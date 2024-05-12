<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Porkbun implements DNS
{
    // 开发依据: https://porkbun.com/api/json/v3/documentation
    private const API_ENDPOINT = 'https://porkbun.com/api/json/v3/';

    public const KEY = 'porkbun';

    public const LABEL = 'Porkbun';

    private string $apiKey;

    private string $secretKey;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->apiKey = sysConfig('ddns_key');
        $this->secretKey = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('domain/listAll')['domains'] ?? [], 'domain');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException('['.self::LABEL." — domain/listAll] The subdomain $this->subdomain does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $uri, array $parameters = []): bool|array
    {
        $parameters = array_merge($parameters, ['apikey' => $this->apiKey, 'secretapikey' => $this->secretKey]);
        $response = Http::timeout(15)->retry(3, 1000)->baseUrl(self::API_ENDPOINT)->asJson()->post($uri, $parameters);

        $data = $response->json();
        if ($response->successful()) {
            return $data ?? true;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $uri] 返回错误信息: ".$data['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $uri] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest("dns/create/{$this->domainInfo['domain']}", ['name' => $this->domainInfo['sub'], 'type' => $type, 'content' => $ip]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $this->sendRequest("dns/edit/{$this->domainInfo['domain']}/$recordIds[0]", ['type' => $type, 'content' => $latest_ip]);

            return true;
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        if ($type) {
            $response = $this->sendRequest("dns/retrieveByNameType/{$this->domainInfo['domain']}/$type/{$this->domainInfo['sub']}");
        } else {
            $response = $this->sendRequest("dns/retrieve/{$this->domainInfo['domain']}");
        }

        if (isset($response['records'])) {
            $records = $response['records'];

            if (! $type) {
                $records = array_filter($records, function ($record) {
                    return $record['name'] === $this->subdomain;
                });
            }

            if ($ip) {
                $records = array_filter($records, static function ($record) use ($ip) {
                    return $record['content'] === $ip;
                });
            }

            return array_column($records, 'id');
        }

        return [];
    }

    public function destroy(string $type, string $ip): int|bool
    {
        if (! $ip) {
            return $this->sendRequest("dns/deleteByNameType/{$this->domainInfo['domain']}/$type/{$this->domainInfo['sub']}");
        }

        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest("dns/delete/{$this->domainInfo['domain']}/$recordId")) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
