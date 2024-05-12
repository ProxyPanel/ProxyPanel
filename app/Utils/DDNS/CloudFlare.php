<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Cache;
use Http;
use Log;
use RuntimeException;

class CloudFlare implements DNS
{
    // 开发依据: https://developers.cloudflare.com/api/
    public const KEY = 'cloudflare';

    public const LABEL = 'CloudFlare';

    private string $apiEndpoint;

    private array $auth;

    public function __construct(private readonly string $subdomain)
    {
        $this->apiEndpoint = 'https://api.cloudflare.com/client/v4/zones/';
        $this->auth = ['X-Auth-Key' => sysConfig('ddns_secret'), 'X-Auth-Email' => sysConfig('ddns_key')];
        $zoneIdentifier = $this->getZoneIdentifier();
        if ($zoneIdentifier) {
            $this->apiEndpoint .= "$zoneIdentifier/dns_records/";
        }
    }

    private function getZoneIdentifier(): string
    {
        $zones = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListZones') ?? [], 'name', 'id');
        });

        foreach ($zones as $zoneID => $zoneName) {
            if (str_contains($this->subdomain, $zoneName)) {
                return $zoneID;
            }
        }

        throw new RuntimeException('['.self::LABEL." — ListZones] The subdomain $this->subdomain does not match any domain in your account.");
    }

    private function sendRequest(string $action, array $parameters = [], ?string $identifier = null): array
    {
        $client = Http::timeout(10)->retry(3, 1000)->withHeaders($this->auth)->baseUrl($this->apiEndpoint)->asJson();

        $response = match ($action) {
            'ListZones' => $client->get(''),
            'ListDNSRecords' => $client->get('', $parameters),
            'CreateDNSRecord' => $client->post('', $parameters),
            'UpdateDNSRecord' => $client->put($identifier, $parameters),
            'DeleteDNSRecord' => $client->delete($identifier),
        };

        $data = $response->json();
        if ($data) {
            if ($data['success'] && $response->ok()) {
                return $data['result'] ?? [];
            }

            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['errors'][0]['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        $result = $this->sendRequest('CreateDNSRecord', ['content' => $ip, 'name' => $this->subdomain, 'type' => $type]);

        return ! empty($result);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);

        if ($recordIds) {
            $ret = $this->sendRequest('UpdateDNSRecord', ['content' => $latest_ip, 'name' => $this->subdomain, 'type' => $type], $recordIds[0]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordIds(string $type, string $ip): array|false
    {
        $records = $this->sendRequest('ListDNSRecords', ['content' => $ip, 'name' => $this->subdomain, 'type' => $type]);

        if ($records) {
            return array_column($records, 'id');
        }

        return false;
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteDNSRecord', [], $recordId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
