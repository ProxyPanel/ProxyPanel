<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class DNSPod implements DNS
{
    // 开发依据: https://docs.dnspod.cn/api/
    private const API_ENDPOINT = 'https://dnspod.tencentcloudapi.com';

    public const KEY = 'dnspod';

    public const LABEL = 'Tencent Cloud | DNSPod | 腾讯云';

    private string $secretId;

    private string $secretKey;

    private array $domainInfo;

    public function __construct(private readonly string $subdomain)
    {
        $this->secretId = sysConfig('ddns_key');
        $this->secretKey = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('DescribeDomainList', ['Type' => 'mine'])['DomainList'], 'Name');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException("[DNSPod – DescribeDomainList] The subdomain {$this->subdomain} does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = []): array
    {
        $timestamp = time();
        $response = Http::timeout(15)->withHeaders([
            'X-TC-Action' => $action,
            'X-TC-Timestamp' => $timestamp,
            'X-TC-Version' => '2021-03-23',
            'Authorization' => $this->generateSignature($parameters, $timestamp),
            'Host' => 'dnspod.tencentcloudapi.com',
        ])->withBody(json_encode($parameters, JSON_FORCE_OBJECT))->post(self::API_ENDPOINT);

        $data = $response->json();
        if ($data) {
            $data = $data['Response'];
            if ($response->ok()) {
                return $data;
            }

            Log::error('[DNSPod – '.$action.'] 返回错误信息：'.$data['Error']['Message'] ?? 'Unknown error');
        } else {
            Log::error('[DNSPod – '.$action.'] 请求失败');
        }

        exit(400);
    }

    private function generateSignature(array $parameters, int $timestamp): string
    { // 签名
        $date = gmdate('Y-m-d', $timestamp);
        $canonicalRequest = "POST\n/\n\ncontent-type:application/json\nhost:dnspod.tencentcloudapi.com\n\ncontent-type;host\n".hash('sha256', json_encode($parameters, JSON_FORCE_OBJECT));

        $credentialScope = "$date/dnspod/tc3_request";
        $stringToSign = "TC3-HMAC-SHA256\n$timestamp\n$credentialScope\n".hash('sha256', $canonicalRequest);

        $secretDate = hash_hmac('SHA256', $date, 'TC3'.$this->secretKey, true);
        $secretService = hash_hmac('SHA256', 'dnspod', $secretDate, true);
        $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);
        $signature = hash_hmac('SHA256', $stringToSign, $secretSigning);

        return 'TC3-HMAC-SHA256 Credential='.$this->secretId.'/'.$credentialScope.', SignedHeaders=content-type;host, Signature='.$signature;
    }

    public function store(string $ip, string $type): bool
    {
        return (bool) $this->sendRequest('CreateRecord', [
            'Domain' => $this->domainInfo['domain'],
            'SubDomain' => $this->domainInfo['sub'],
            'RecordType' => $type,
            'RecordLine' => '默认',
            'Value' => $ip,
        ]);
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        if ($recordIds) {
            $result = $this->sendRequest('ModifyRecord', [
                'Domain' => $this->domainInfo['domain'],
                'RecordType' => $type,
                'RecordLine' => '默认',
                'Value' => $latest_ip,
                'RecordId' => $recordIds[0],
                'SubDomain' => $this->domainInfo['sub'],
            ]);
        }

        return (bool) ($result ?? false);
    }

    private function getRecordIds(string $type, string $ip): array
    {
        $parameters = ['Domain' => $this->domainInfo['domain'], 'Subdomain' => $this->domainInfo['sub']];
        if ($type) {
            $parameters['RecordType'] = $type;
        }
        $response = $this->sendRequest('DescribeRecordList', $parameters);

        if (isset($response['RecordList'])) {
            $records = $response['RecordList'];

            if ($ip) {
                $records = array_filter($records, static function ($record) use ($ip) {
                    return $record['Value'] === $ip;
                });
            }

            return array_column($records, 'RecordId');
        }

        return [];
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteRecord', ['Domain' => $this->domainInfo['domain'], 'RecordId' => $recordId])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
