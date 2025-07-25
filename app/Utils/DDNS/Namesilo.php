<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Namesilo implements DNS
{
    // 开发依据: https://www.namesilo.com/api-reference
    private const API_ENDPOINT = 'https://www.namesilo.com/api/';

    public const KEY = 'namesilo';

    public const LABEL = 'Namesilo';

    private string $apiKey;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->apiKey = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('listDomains')['domains'] ?? [], 'domain');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException('['.self::LABEL." — listDomains] The subdomain $this->subdomain does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = []): array
    {
        $response = Http::timeout(15)->retry(3, 1000)->get(self::API_ENDPOINT.$action, ['version' => 1, 'type' => 'json', 'key' => $this->apiKey, ...$parameters]);

        if ($response->ok()) {
            $data = $response->json();
            if ($data && isset($data['reply']['code']) && $data['reply']['code'] === 300) {
                return $data['reply'];
            }

            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['reply']['detail'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return $this->sendRequest('dnsAddRecord', [
            'domain' => $this->domainInfo['domain'],
            'rrtype' => $type,
            'rrhost' => $this->domainInfo['sub'],
            'rrvalue' => $ip,
            'rrttl' => 3600,
        ])['detail'] === 'success';
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $record = Arr::first($this->getRecordIds($type, $original_ip));
        if ($record) {
            return $this->sendRequest('dnsUpdateRecord', [
                'domain' => $this->domainInfo['domain'],
                'rrid' => $record,
                'rrhost' => $this->domainInfo['sub'],
                'rrvalue' => $latest_ip,
                'rrttl' => 3600,
            ])['detail'] === 'success';
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array|false
    {
        $response = $this->sendRequest('dnsListRecords', ['domain' => $this->domainInfo['domain']]);

        if (isset($response['resource_record'])) {
            $records = $response['resource_record'];

            if ($ip) {
                $records = array_filter($records, function ($record) use ($ip) {
                    return $record['host'] === $this->subdomain && $record['value'] === $ip;
                });
            } elseif ($type) {
                $records = array_filter($records, function ($record) use ($type) {
                    return $record['host'] === $this->subdomain && $record['type'] === $type;
                });
            } else {
                $records = array_filter($records, function ($record) {
                    return $record['host'] === $this->subdomain;
                });
            }

            return array_column($records, 'record_id');
        }

        return [];
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('dnsDeleteRecord', ['domain' => $this->domainInfo['domain'], 'rrid' => $recordId])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
