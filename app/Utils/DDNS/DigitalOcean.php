<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class DigitalOcean implements DNS
{
    // 开发依据: https://docs.digitalocean.com/products/networking/dns/how-to/manage-records/
    private const API_ENDPOINT = 'https://api.digitalocean.com/v2/domains';

    public const KEY = 'digitalocean';

    public const LABEL = 'DigitalOcean';

    private string $accessToken;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessToken = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('DescribeDomains')['domains'] ?? [], 'name');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException('['.self::LABEL." — DescribeDomains] The subdomain $this->subdomain does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = [], string $recordId = ''): array|bool
    {
        $client = Http::timeout(15)->retry(3, 1000)->withToken($this->accessToken)->baseUrl(self::API_ENDPOINT)->asJson();

        $response = match ($action) {
            'DescribeDomains' => $client->get(''),
            'DescribeSubDomainRecords' => $client->get("/{$this->domainInfo['domain']}/records"),
            'CreateDomainRecord' => $client->post("/{$this->domainInfo['domain']}/records", $parameters),
            'UpdateDomainRecord' => $client->patch("/{$this->domainInfo['domain']}/records/$recordId", $parameters),
            'DeleteDomainRecord' => $client->delete("/{$this->domainInfo['domain']}/records/$recordId"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data ?: true;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest('CreateDomainRecord', ['name' => $this->domainInfo['sub'], 'type' => $type, 'data' => $ip]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            foreach ($recordIds as $recordId) {
                $this->sendRequest('UpdateDomainRecord', ['type' => $type, 'data' => $latest_ip], $recordId);
            }

            return true;
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $response = $this->sendRequest('DescribeSubDomainRecords');

        if (isset($response['domain_records'])) {
            $records = $response['domain_records'];

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

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteDomainRecord', [], $recordId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
