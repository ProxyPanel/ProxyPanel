<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Google implements DNS
{
    // 开发依据: https://developers.google.com/identity/protocols/oauth2/service-account?hl=zh-cn#httprest https://cloud.google.com/dns/docs/apis?hl=zh-cn
    public const KEY = 'google';

    public const LABEL = 'Google Cloud DNS';

    private string $apiEndpoint = 'https://dns.googleapis.com/dns/v1/projects/';

    private array $credentials;

    private string $token;

    private string $zoneID;

    public function __construct(private readonly string $subdomain)
    {
        $this->credentials = json_decode(sysConfig('ddns_secret'), true);
        if (! $this->credentials) {
            exit(400);
        }
        $this->apiEndpoint .= "{$this->credentials['project_id']}/";
        $this->token = $this->getBearerToken();
        $this->zoneID = $this->getZoneIdentifier();
    }

    private function getBearerToken(): string
    {
        return Cache::remember('google_token', 3599, function () {
            $response = Http::timeout(15)->asForm()->post('https://oauth2.googleapis.com/token', ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $this->generateJWT()]);

            if ($response->successful() && $data = $response->json()) {
                return $data['access_token'];
            }

            exit(400);
        });
    }

    private function generateJWT(): string
    {
        $headerEncoded = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $this->credentials['private_key_id']]));

        $now = time();
        $payloadEncoded = base64url_encode(json_encode([
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/ndev.clouddns.readwrite',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $dataToSign = "$headerEncoded.$payloadEncoded";
        openssl_sign($dataToSign, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256);
        $signatureEncoded = base64url_encode($signature);

        return "$dataToSign.$signatureEncoded";
    }

    private function getZoneIdentifier(): string
    {
        $zones = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListZones')['managedZones'] ?? [], 'dnsName', 'id');
        });

        foreach ($zones as $zoneID => $zoneName) {
            if (str_contains("$this->subdomain.", $zoneName)) {
                return $zoneID;
            }
        }

        throw new RuntimeException('['.self::LABEL." — ListPublicZones] The subdomain $this->subdomain does not match any domain in your account.");
    }

    private function sendRequest(string $action, array $parameters = [], string $type = 'A'): array|bool
    {
        $client = Http::timeout(15)->retry(3, 1000)->withToken($this->token)->baseUrl($this->apiEndpoint)->withQueryParameters(['api-version' => '2018-05-01'])->asJson();

        $response = match ($action) {
            'ListZones' => $client->get('managedZones'),
            'ListRecordSets' => $client->get("managedZones/$this->zoneID/rrsets", $parameters),
            'CreateRecordSet' => $client->post("managedZones/$this->zoneID/rrsets", $parameters),
            'PatchRecordSet' => $client->patch("managedZones/$this->zoneID/rrsets/$this->subdomain./$type", $parameters),
            'DeleteRecordSet' => $client->delete("managedZones/$this->zoneID/rrsets/$this->subdomain./$type"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data ?: true;
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
        $ips = $this->getRecordIps($type);

        if (! $ips) {
            return (bool) $this->sendRequest('CreateRecordSet', ['kind' => 'dns#resourceRecordSet', 'name' => "$this->subdomain.", 'type' => $type, 'ttl' => 300, 'rrdatas' => [$ip]]);
        }

        if (! in_array($ip, $ips, true)) {
            $ips[] = $ip;

            return $this->updateRecord($type, $ips);
        }

        return true;
    }

    private function getRecordIps(string $type): array
    {
        $parameters = ['name' => "$this->subdomain."];
        if ($type) {
            $parameters['type'] = $type;
        }
        $records = $this->sendRequest('ListRecordSets', $parameters)['rrsets'] ?? [];

        if ($records) {
            return Arr::first($records)['rrdatas'] ?? [];
        }

        return [];
    }

    private function updateRecord(string $type, array $ips): bool
    {
        return (bool) $this->sendRequest('PatchRecordSet', ['kind' => 'dns#resourceRecordSet', 'name' => "$this->subdomain.", 'type' => $type, 'ttl' => 300, 'rrdatas' => array_values($ips)]);
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
