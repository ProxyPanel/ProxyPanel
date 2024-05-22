<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Azure implements DNS
{
    // 开发依据: https://learn.microsoft.com/en-us/rest/api/eventhub/get-azure-active-directory-token?view=rest-dns-2018-05-01 https://learn.microsoft.com/en-us/rest/api/dns/?view=rest-dns-2018-05-01
    private const API_ENDPOINT = 'https://management.azure.com/';

    public const KEY = 'azure';

    public const LABEL = 'Microsoft Azure';

    private string $tenantId;

    private string $clientId;

    private string $clientSecret;

    private string $token;

    private string $subscriptionId;

    private string $zoneID;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $ids = explode(',', sysConfig('ddns_key'));
        $this->tenantId = $ids[1];
        $this->clientId = $ids[2];
        $this->clientSecret = sysConfig('ddns_secret');
        $this->subscriptionId = $ids[0];
        $this->token = $this->getBearerToken();
        $this->zoneID = $this->getZoneIdentifier();
    }

    private function getBearerToken(): string
    {
        return Cache::remember('azure_token', 3599, function () {
            $response = Http::timeout(15)->retry(3, 1000)->asForm()->post("https://login.microsoftonline.com/$this->tenantId/oauth2/token",
                ['grant_type' => 'client_credentials', 'client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'resource' => self::API_ENDPOINT]);

            if ($response->successful() && $data = $response->json()) {
                return $data['access_token'];
            }

            exit(400);
        });
    }

    private function getZoneIdentifier(): string
    {
        $zones = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListZones')['value'] ?? [], 'name', 'id');
        });

        foreach ($zones as $zoneID => $zoneName) {
            if (str_contains($this->subdomain, $zoneName)) {
                $this->domainInfo = [
                    'sub' => rtrim(substr($this->subdomain, 0, -strlen($zoneName)), '.'),
                    'domain' => $zoneName,
                ];

                return $zoneID;
            }
        }

        throw new RuntimeException('['.self::LABEL." — ListPublicZones] The subdomain $this->subdomain does not match any domain in your account.");
    }

    private function sendRequest(string $action, array $payload = [], string $type = 'A'): array|bool
    {
        $client = Http::timeout(15)->retry(3, 1000)->withToken($this->token)->baseUrl(self::API_ENDPOINT)->withQueryParameters(['api-version' => '2018-05-01'])->asJson();

        $response = match ($action) {
            'ListZones' => $client->get("/subscriptions/$this->subscriptionId/providers/Microsoft.Network/dnszones"),
            'ListRecordSets' => $client->get("$this->zoneID/".($type ?: 'all')),
            'CreateRecordSet' => $client->put("$this->zoneID/$type/{$this->domainInfo['sub']}", $payload),
            'DeleteRecordSet' => $client->delete("$this->zoneID/$type/{$this->domainInfo['sub']}"),
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
        $ips = $this->getRecordIps($type);
        if (! in_array($ip, $ips, true)) {
            $ips[] = $ip;

            return $this->updateRecord($type, $ips);
        }

        return true;
    }

    private function getRecordIps(string $type): array
    { // 域名信息
        $records = $this->sendRequest('ListRecordSets', [], $type)['value'] ?? [];

        $records = array_filter($records, function ($record) {
            return $record['name'] === $this->domainInfo['sub'];
        });

        return Arr::flatten(Arr::first($records)['properties']["{$type}Records"] ?? []);
    }

    private function updateRecord(string $type, array $ips): bool
    {
        $ipKey = $type === 'A' ? 'ipv4Address' : 'ipv6Address';

        $ipInfo = array_map(static function ($ip) use ($ipKey) {
            return [$ipKey => $ip];
        }, $ips);

        return (bool) $this->sendRequest('CreateRecordSet', ['properties' => ['TTL' => 300, "{$type}Records" => $ipInfo]]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $ips = $this->getRecordIps($type);

        if ($ips) {
            $ips = array_filter($ips, static fn ($ip) => $ip !== $original_ip);
        }

        $ips[] = $latest_ip;

        return $this->updateRecord($type, $ips);
    }

    public function destroy(string $type, string $ip): bool
    {
        if (! $type) {
            return $this->sendRequest('DeleteRecordSet') && $this->sendRequest('DeleteRecordSet', [], 'AAAA');
        }

        if ($ip) {
            $ips = array_filter($this->getRecordIps($type), static fn ($hasIp) => $hasIp !== $ip);

            if ($ips) {
                return $this->updateRecord($type, $ips);
            }
        }

        return (bool) $this->sendRequest('DeleteRecordSet', [], $type);
    }
}
