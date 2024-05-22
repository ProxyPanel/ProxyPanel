<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Vultr implements DNS
{
    // 开发依据: https://www.vultr.com/api/#tag/dns
    private const API_ENDPOINT = 'https://api.vultr.com/v2/';

    public const KEY = 'vultr';

    public const LABEL = 'Vultr';

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
            return array_column($this->sendRequest('ListDNSDomains')['domains'] ?? [], 'domain');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException('['.self::LABEL." — ListDNSDomains] The subdomain $this->subdomain does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = [], string $recordId = ''): array|bool
    {
        $client = Http::timeout(15)->retry(3, 1000)->withToken($this->apiKey)->baseUrl(self::API_ENDPOINT)->asJson();

        $response = match ($action) {
            'ListDNSDomains' => $client->get('/domains'),
            'ListRecords' => $client->get("/domains/{$this->domainInfo['domain']}/records", $parameters),
            'CreateRecord' => $client->post("/domains/{$this->domainInfo['domain']}/records", $parameters),
            'UpdateRecord' => $client->patch("/domains/{$this->domainInfo['domain']}/records/$recordId", $parameters),
            'DeleteRecord' => $client->delete("/domains/{$this->domainInfo['domain']}/records/$recordId"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data ?? true;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['error'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest('CreateRecord', ['name' => $this->domainInfo['sub'], 'type' => $type, 'data' => $ip]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $this->sendRequest('UpdateRecord', ['data' => $latest_ip], $recordIds[0]);

            return true;
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $response = $this->sendRequest('ListRecords');

        if (isset($response['records'])) {
            $records = $response['records'];

            if ($ip) {
                $records = array_filter($records, function ($record) use ($ip) {
                    return $record['data'] === $ip && $record['name'] === $this->domainInfo['sub'];
                });
            } elseif ($type) {
                $records = array_filter($records, function ($record) use ($type) {
                    return $record['type'] === $type && $record['name'] === $this->domainInfo['sub'];
                });
            } else {
                $records = array_filter($records, function ($record) {
                    return $record['name'] === $this->domainInfo['sub'];
                });
            }

            return array_column($records, 'id');
        }

        return [];
    }

    public function destroy(string $type, string $ip): int|bool
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteRecord', $recordId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
