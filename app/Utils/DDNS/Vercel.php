<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Vercel implements DNS
{
    // 开发依据: https://vercel.com/docs/rest-api
    private const API_ENDPOINT = 'https://api.vercel.com/';

    public const KEY = 'vercel';

    public const LABEL = 'Vercel';

    private string $teamID;

    private string $token;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->teamID = sysConfig('ddns_key');
        $this->token = sysConfig('ddns_secret');
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

    private function sendRequest(string $action, array $parameters = [], string $recordId = ''): array
    {
        $client = Http::timeout(15)->retry(3, 1000)->withToken($this->token)->baseUrl(self::API_ENDPOINT)->withQueryParameters(['teamId' => $this->teamID])->asJson();

        $response = match ($action) {
            'DescribeDomains' => $client->get('v5/domains'),
            'DescribeSubDomainRecords' => $client->get("v4/domains/{$this->domainInfo['domain']}/records"),
            'AddDomainRecord' => $client->post("v2/domains/{$this->domainInfo['domain']}/records", $parameters),
            'UpdateDomainRecord' => $client->patch("v1/domains/records/$recordId", $parameters),
            'DeleteDomainRecord' => $client->delete("v2/domains/{$this->domainInfo['domain']}/records/$recordId"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['error']['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest('AddDomainRecord', ['name' => $this->domainInfo['sub'], 'type' => $type, 'value' => $ip]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $this->sendRequest('UpdateDomainRecord', ['value' => $latest_ip], $recordIds[0]);

            return true;
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $response = $this->sendRequest('DescribeSubDomainRecords');

        if (isset($response['records'])) {
            $records = $response['records'];

            if ($ip) {
                $records = array_filter($records, function ($record) use ($ip) {
                    return $record['value'] === $ip && $record['name'] === $this->domainInfo['sub'];
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

    public function destroy(string $type, string $ip): bool|int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteDomainRecord', $recordId) === []) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
