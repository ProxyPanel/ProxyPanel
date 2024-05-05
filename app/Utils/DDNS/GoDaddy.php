<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class GoDaddy implements DNS
{
    //  开发依据: https://developer.godaddy.com/doc/endpoint/domains
    private const API_ENDPOINT = 'https://api.godaddy.com/v1/domains/';

    public const KEY = 'godaddy';

    public const LABEL = 'GoDaddy';

    private string $accessKeyID;

    private string $accessKeySecret;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->accessKeySecret = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('DescribeDomains') ?? [], 'domain');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException("[GoDaddy – DescribeDomains] The subdomain {$this->subdomain} does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = []): array|bool
    {
        $client = Http::timeout(15)->retry(3, 1000)->withHeader('Authorization', "sso-key $this->accessKeyID:$this->accessKeySecret")->baseUrl(self::API_ENDPOINT)->asJson();

        $response = match ($action) {
            'DescribeDomains' => $client->get('', ['statuses' => 'ACTIVE']),
            'DescribeSubDomainRecords' => $client->get("{$parameters['Domain']}/records/{$parameters['Type']}/{$parameters['Sub']}"),
            'AddDomainRecord' => $client->patch("{$parameters['Domain']}/records", $parameters['Data']),
            'UpdateDomainRecord' => $client->put("{$parameters['Domain']}/records/{$parameters['Type']}/{$parameters['Sub']}", $parameters['Data']),
            'DeleteDomainRecord' => $client->delete("{$parameters['Domain']}/records/{$parameters['Type']}/{$parameters['Sub']}"),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data ?: true;
        }

        if ($data) {
            Log::error('[GoDaddy - '.$action.'] 返回错误信息：'.$data['message'] ?? 'Unknown error');
        } else {
            Log::error('[GoDaddy - '.$action.'] 请求失败');
        }

        return false;
    }

    public function store(string $ip, string $type): bool
    {
        $ret = $this->sendRequest('AddDomainRecord', ['Domain' => $this->domainInfo['domain'], 'Data' => [['name' => $this->domainInfo['sub'], 'type' => $type, 'data' => $ip]]]);

        return (bool) ($ret ?: false);
    }

    public function destroy(string $type, string $ip): bool
    {
        if ($ip) {
            $ret = $this->update('', $ip, $type);
        } else {
            $ret = $this->sendRequest('DeleteDomainRecord', ['Sub' => $this->domainInfo['sub'], 'Domain' => $this->domainInfo['domain'], 'Type' => $type]);
        }

        return (bool) ($ret ?: false);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIPs = $this->getRecordIPs($type);
        if ($recordIPs) {
            $recordIPs = array_values(array_filter($recordIPs, static fn ($ip) => $ip !== $original_ip));

            if ($latest_ip) {
                $recordIPs[] = $latest_ip;
            }

            $ret = $this->sendRequest('UpdateDomainRecord', [
                'Sub' => $this->domainInfo['sub'],
                'Domain' => $this->domainInfo['domain'],
                'Type' => $type,
                'Data' => array_map(static function ($ip) {
                    return ['data' => $ip];
                }, $recordIPs),
            ]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordIPs(string $type): array
    {
        $records = $this->sendRequest('DescribeSubDomainRecords', ['Sub' => $this->domainInfo['sub'], 'Domain' => $this->domainInfo['domain'], 'Type' => $type]);

        return array_column($records, 'data');
    }
}
