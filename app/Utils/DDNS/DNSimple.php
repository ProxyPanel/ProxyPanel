<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class DNSimple implements DNS
{
    // 开发依据: https://developer.dnsimple.com/v2/
    private const API_ENDPOINT = 'https://api.dnsimple.com/v2/';

    public const KEY = 'dnsimple';

    public const LABEL = 'DNSimple';

    private string $accountID;

    private string $accessToken;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->accountID = sysConfig('ddns_key');
        $this->accessToken = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListDomains') ?? [], 'name');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException('['.self::LABEL." — ListDomains] The subdomain $this->subdomain does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = [], string $recordId = ''): bool|array
    {
        $client = Http::timeout(15)->retry(3, 1000)->withHeader('Authorization', "Bearer $this->accessToken")->baseUrl(self::API_ENDPOINT.$this->accountID)->asJson();

        $response = match ($action) {
            'ListDomains' => $client->get('/domains'),
            'ListZoneRecords' => $client->get("/zones/{$this->domainInfo['domain']}/records", $parameters),
            'CreateZoneRecord' => $client->post("/zones/{$this->domainInfo['domain']}/records", $parameters),
            'UpdateZoneRecord' => $client->patch("/zones/{$this->domainInfo['domain']}/records/$recordId", $parameters),
            'DeleteZoneRecord' => $client->delete("/zones/{$this->domainInfo['domain']}/records/$recordId"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data['data'] ?? true;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['errors']['base'] ?? $data['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest('CreateZoneRecord', ['name' => $this->domainInfo['sub'], 'type' => $type, 'content' => $ip]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $this->sendRequest('UpdateZoneRecord', ['content' => $latest_ip], $recordIds[0]);

            return true;
        }

        return false;
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $parameter = ['name' => $this->domainInfo['sub']];
        if ($type) {
            $parameter['type'] = $type;
        }
        $records = $this->sendRequest('ListZoneRecords', $parameter);

        if ($records) {
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
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteZoneRecord', $recordId)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
