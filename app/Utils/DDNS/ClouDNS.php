<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class ClouDNS implements DNS
{
    // 开发依据: https://www.cloudns.net/wiki/article/41/
    private const API_ENDPOINT = 'https://api.cloudns.net/dns/';

    public const KEY = 'cloudns';

    public const LABEL = 'ClouDNS';

    private string $authID;

    private string $authPassword;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->authID = sysConfig('ddns_key');
        $this->authPassword = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('list-zones', ['page' => 1, 'rows-per-page' => 100]) ?? [], 'name');
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

    private function sendRequest(string $action, array $parameters = []): array
    {
        $response = Http::timeout(15)->get(self::API_ENDPOINT."$action.json", array_merge(['auth-id' => $this->authID, 'auth-password' => $this->authPassword], $parameters));

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['status']) && $data['status'] === 'Failed') {
                Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['statusDescription'] ?? 'Unknown error');
            } else {
                return $data;
            }
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        $result = $this->sendRequest('add-record', ['domain-name' => $this->domainInfo['domain'], 'record-type' => $type, 'host' => $this->domainInfo['sub'], 'record' => $ip, 'ttl' => 300]);

        return $result['status'] === 'Success';
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $ret = $this->sendRequest('mod-record', ['domain-name' => $this->domainInfo['domain'], 'record-id' => $recordIds[0], 'host' => $this->domainInfo['sub'], 'record' => $latest_ip, 'ttl' => 300]);
            if (count($recordIds) > 1) {
                $this->destroy($type, $original_ip);
            }
        }

        return ($ret['status'] ?? false) === 'Success';
    }

    private function getRecordIds(string $type, string $ip): array
    { // 域名信息
        $records = $this->sendRequest('records', ['domain-name' => $this->domainInfo['domain'], 'host' => $this->domainInfo['sub'], 'type' => $type]) ?? [];

        if ($ip) {
            $records = array_filter($records, static function ($record) use ($ip) {
                return $record['record'] === $ip;
            });
        }

        return array_column($records, 'id');
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            $result = $this->sendRequest('delete-record', ['domain-name' => $this->domainInfo['domain'], 'record-id' => $recordId]);
            if (isset($result['status']) && $result['status'] === 'Success') {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
