<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Baidu implements DNS
{
    // 开发依据: https://cloud.baidu.com/doc/DNS/index.html
    private const API_ENDPOINT = 'https://dns.baidubce.com';

    public const KEY = 'baidu';

    public const LABEL = 'Baidu AI Cloud | 百度智能云';

    private string $secretId;

    private string $secretKey;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->secretId = sysConfig('ddns_key');
        $this->secretKey = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('DescribeDomains')['zones'] ?? [], 'name');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException("[Baidu – DescribeDomains] The subdomain {$this->subdomain} does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = [], ?string $recordId = null): array
    {
        $date = gmdate("Y-m-d\TH:i:s\Z");
        $client = Http::timeout(15)->withHeaders(['Host' => 'dns.baidubce.com', 'x-bce-date' => $date, 'Content-Type' => 'application/json; charset=utf-8'])->baseUrl(self::API_ENDPOINT);

        $path = match ($action) {
            'DescribeDomains' => '/v1/dns/zone',
            'DescribeSubDomainRecords', 'AddDomainRecord' => "/v1/dns/zone/{$this->domainInfo['domain']}/record",
            'UpdateDomainRecord', 'DeleteDomainRecord' => "/v1/dns/zone/{$this->domainInfo['domain']}/record/$recordId",
        };

        $method = match ($action) {
            'DescribeDomains', 'DescribeSubDomainRecords' => 'GET',
            'AddDomainRecord' => 'POST',
            'UpdateDomainRecord' => 'PUT',
            'DeleteDomainRecord' => 'DELETE',
        };

        $client->withHeader('Authorization', $this->generateSignature($parameters, $method, $path, $date));

        $response = match ($method) {
            'GET' => $client->get($path, $parameters),
            'POST' => $client->post($path, $parameters),
            'PUT' => $client->put($path, $parameters),
            'DELETE' => $client->delete($path, $parameters),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data ?? [];
        }

        if ($data) {
            Log::error('[Baidu – '.$action.'] 返回错误信息：'.$data['message'] ?? 'Unknown error');
        } else {
            Log::error('[Baidu – '.$action.'] 请求失败');
        }

        exit(400);
    }

    private function generateSignature(array $parameters, string $httpMethod, string $path, string $date): string
    { // 签名
        $authStringPrefix = "bce-auth-v1/$this->secretId/$date/1800";
        $signingKey = hash_hmac('sha256', $authStringPrefix, $this->secretKey);
        $canonicalRequest = "$httpMethod\n$path\n".http_build_query($parameters)."\nhost:dns.baidubce.com\nx-bce-date:".rawurlencode($date);
        $signature = hash_hmac('sha256', $canonicalRequest, $signingKey);

        return "$authStringPrefix/host;x-bce-date/$signature";
    }

    public function store(string $ip, string $type): bool
    {
        return $this->sendRequest('AddDomainRecord', [
            'rr' => $this->domainInfo['sub'],
            'type' => $type,
            'value' => $ip,
        ]) === [];
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            return $this->sendRequest('UpdateDomainRecord', [
                'rr' => $this->domainInfo['sub'],
                'type' => $type,
                'value' => $latest_ip,
            ], $recordIds[0]) === [];
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $parameters = ['rr' => $this->domainInfo['sub']];
        $response = $this->sendRequest('DescribeSubDomainRecords', $parameters);

        if (isset($response['records'])) {
            $records = $response['records'];

            if ($ip) {
                $records = array_filter($records, static function ($record) use ($ip) {
                    return $record['value'] === $ip;
                });
            } elseif ($type) {
                $records = array_filter($records, static function ($record) use ($type) {
                    return $record['type'] === $type;
                });
            }

            return array_column($records, 'id');
        }

        return [];
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteDomainRecord', [], $recordId) === []) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
