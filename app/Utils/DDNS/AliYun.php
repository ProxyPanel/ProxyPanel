<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class AliYun implements DNS
{
    //  开发依据: https://api.aliyun.com/document/Alidns/2015-01-09/overview
    private const API_ENDPOINT = 'https://alidns.aliyuncs.com/';

    public const KEY = 'aliyun';

    public const LABEL = 'Alibaba Cloud | 阿里云';

    private string $accessKeyID;

    private string $accessKeySecret;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->accessKeySecret = sysConfig('ddns_secret');
    }

    public function store(string $ip, string $type): bool
    {
        $domainInfo = $this->parseDomainInfo();

        if (! $domainInfo) {
            return false;
        }

        return (bool) $this->sendRequest('AddDomainRecord', [
            'DomainName' => $domainInfo['domain'],
            'RR' => $domainInfo['sub'],
            'Type' => $type,
            'Value' => $ip,
        ]);
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('DescribeDomains')['Domains']['Domain'] ?? [], 'DomainName');
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException("[AliYun – DescribeDomains] The subdomain {$this->subdomain} does not match any domain in your account.");
        }

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $matched,
        ];
    }

    private function sendRequest(string $action, array $parameters = []): array
    {
        $parameters = array_merge([
            'Action' => $action,
            'Format' => 'JSON',
            'Version' => '2015-01-09',
            'AccessKeyId' => $this->accessKeyID,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"), //公共参数Timestamp GMT时间
            'SignatureVersion' => '1.0',
            'SignatureNonce' => str_replace('.', '', microtime(true)), //唯一数，用于防止网络重放攻击
        ], $parameters);
        $parameters['Signature'] = $this->generateSignature($parameters);

        $response = Http::asForm()->timeout(15)->post(self::API_ENDPOINT, $parameters);
        $data = $response->json();

        if ($data) {
            if ($response->successful()) {
                return $data;
            }

            Log::error('[AliYun - '.$action.'] 返回错误信息：'.$data['Message'] ?? 'Unknown error');
        } else {
            Log::error('[AliYun - '.$action.'] 请求失败');
        }

        exit(400);
    }

    private function generateSignature(array $parameters): string
    { // 签名
        ksort($parameters, SORT_STRING);

        $stringToBeSigned = 'POST&%2F&'.urlencode(http_build_query($parameters));

        return base64_encode(hash_hmac('sha1', $stringToBeSigned, $this->accessKeySecret.'&', true));
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordIds = $this->getRecordIds($type, $original_ip);
        $domainInfo = $this->parseDomainInfo();
        if ($recordIds && $domainInfo) {
            $ret = $this->sendRequest('UpdateDomainRecord', ['RR' => $domainInfo['sub'], 'RecordId' => Arr::first($recordIds), 'Type' => $type, 'Value' => $latest_ip]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordIds(string $type, string $ip): array
    { // 域名信息
        $parameters = ['SubDomain' => $this->subdomain];
        if ($type) {
            $parameters['Type'] = $type;
        }

        $records = $this->sendRequest('DescribeSubDomainRecords', $parameters)['DomainRecords']['Record'] ?? [];

        if ($ip) {
            $records = array_filter($records, static function ($record) use ($ip) {
                return $record['Value'] === $ip;
            });
        }

        return array_column($records, 'RecordId');
    }

    public function destroy(string $type, string $ip): int
    {
        $recordIds = $this->getRecordIds($type, $ip);
        $deletedCount = 0;

        foreach ($recordIds as $recordId) {
            if ($this->sendRequest('DeleteDomainRecord', ['RecordId' => $recordId])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
