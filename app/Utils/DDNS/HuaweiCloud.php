<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Cache;
use Http;
use Log;
use RuntimeException;

class HuaweiCloud implements DNS
{
    // 开发依据: https://support.huaweicloud.com/api-dns/dns_api_64001.html
    private const API_ENDPOINT = 'https://dns.myhuaweicloud.com';

    public const KEY = 'huaweicloud';

    public const LABEL = 'HuaweiCloud 华为云';

    private string $accessKeyID;

    private string $secretAccessKey;

    private string $zoneID;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->secretAccessKey = sysConfig('ddns_secret');
        $this->zoneID = $this->getZoneIdentifier();
    }

    private function getZoneIdentifier(): string
    {
        $zones = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListPublicZones')['zones'] ?? [], 'name', 'id');
        });

        foreach ($zones as $zoneID => $zoneName) {
            if (str_contains("$this->subdomain.", $zoneName)) {
                return $zoneID;
            }
        }

        throw new RuntimeException('['.self::LABEL." — ListPublicZones] The subdomain $this->subdomain does not match any domain in your account.");
    }

    private function sendRequest(string $action, array $parameters = [], array $payload = [], string $recordsetId = ''): array
    {
        $date = gmdate("Ymd\THis\Z");
        $client = Http::timeout(15)->retry(3, 1000)->withHeaders(['Host' => 'dns.myhuaweicloud.com', 'X-Sdk-Date' => $date])->baseUrl(self::API_ENDPOINT)->asJson();

        $uri = match ($action) {
            'ListPublicZones' => '/v2/zones',
            'ListRecordSets' => '/v2/recordsets',
            'CreateRecordSet' => "/v2/zones/$this->zoneID/recordsets",
            'UpdateRecordSet' => "/v2/zones/$this->zoneID/recordsets/$recordsetId",
            'DeleteRecordSets' => "/v2.1/zones/$this->zoneID/recordsets",
        };

        $response = match ($action) {
            'ListPublicZones', 'ListRecordSets' => $client->withHeader('Authorization', $this->generateSignature('GET', $uri, $parameters, $payload, $date))->get($uri, $parameters),
            'CreateRecordSet' => $client->withHeader('Authorization', $this->generateSignature('POST', $uri, $parameters, $payload, $date))->post($uri, $payload),
            'UpdateRecordSet' => $client->withHeader('Authorization', $this->generateSignature('PUT', $uri, $parameters, $payload, $date))->put($uri, $payload),
            'DeleteRecordSets' => $client->withHeader('Authorization', $this->generateSignature('DELETE', $uri, $parameters, $payload, $date))->delete($uri, $payload),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['error_msg'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    private function generateSignature(string $method, string $uri, array $parameters, array $payload, string $date): string
    { // 签名
        $canonicalRequest = "$method\n$uri/\n".http_build_query($parameters)."\nhost:dns.myhuaweicloud.com\nx-sdk-date:$date\n\nhost;x-sdk-date\n".hash('sha256', $payload ? json_encode($payload) : '');
        $stringToSign = "SDK-HMAC-SHA256\n$date\n".hash('sha256', $canonicalRequest);
        $signature = hash_hmac('SHA256', $stringToSign, $this->secretAccessKey);

        return "SDK-HMAC-SHA256 Access=$this->accessKeyID, SignedHeaders=host;x-sdk-date, Signature=$signature";
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);

        if ($recordIds) {
            $response = $this->sendRequest('UpdateRecordSet', [], ['name' => "$this->subdomain.", 'type' => $type, 'records' => [$latest_ip]], $recordIds[0]);

            return isset($response['status']) && $response['status'] === 'PENDING_UPDATE';
        }

        return $this->store($latest_ip, $type);
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $response = $this->sendRequest('ListRecordSets', ['name' => "$this->subdomain.", 'records' => $ip, 'type' => $type]);

        if (isset($response['recordsets'])) {
            $records = $response['recordsets'];

            return array_column($records, 'id');
        }

        return [];
    }

    public function store(string $ip, string $type): bool
    {
        $response = $this->sendRequest('CreateRecordSet', [], ['name' => "$this->subdomain.", 'type' => $type, 'records' => [$ip]]);

        return isset($response['status']) && $response['status'] === 'PENDING_CREATE';
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);

        if ($recordIds) {
            $response = $this->sendRequest('DeleteRecordSets', [], ['recordset_ids' => $recordIds]);

            return $response['metadata']['total_count'];
        }

        return true;
    }
}
