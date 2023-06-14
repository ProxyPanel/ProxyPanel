<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Http;
use Log;

class AliYun implements DNS
{
    //  开发依据: https://api.aliyun.com/document/Alidns/2015-01-09/overview
    private const API_HOST = 'https://alidns.aliyuncs.com/';

    private string $accessKeyID;

    private string $accessKeySecret;

    public function __construct(private readonly string $subDomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->accessKeySecret = sysConfig('ddns_secret');
    }

    public function store(string $ip, string $type): bool
    {
        $domainInfo = $this->analysisDomain();
        if ($domainInfo) {
            $ret = $this->send('AddDomainRecord', ['DomainName' => $domainInfo['host'], 'RR' => $domainInfo['rr'], 'Type' => $type, 'Value' => $ip]);
        }

        return (bool) ($ret ?? false);
    }

    private function analysisDomain(): array|false
    {
        $domains = data_get($this->send('DescribeDomains'), 'Domains.Domain.*.DomainName');

        if ($domains) {
            foreach ($domains as $domain) {
                if (str_contains($this->subDomain, $domain)) {
                    return ['rr' => rtrim(substr($this->subDomain, 0, -strlen($domain)), '.'), 'host' => $domain];
                }
            }
            Log::error('[AliYun - DescribeDomains] 错误域名 '.$this->subDomain.' 不在账号拥有域名里');
        }

        return false;
    }

    private function send(string $action, array $info = []): array
    {
        $public = [
            'Format' => 'JSON',
            'Version' => '2015-01-09',
            'AccessKeyId' => $this->accessKeyID,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"), //公共参数Timestamp GMT时间
            'SignatureVersion' => '1.0',
            'SignatureNonce' => str_replace('.', '', microtime(true)), //唯一数，用于防止网络重放攻击
        ];
        $parameters = array_merge(['Action' => $action], $public, $info);
        $parameters['Signature'] = $this->computeSignature($parameters);

        $response = Http::asForm()->timeout(15)->post(self::API_HOST, $parameters);
        $data = $response->json();

        if ($data) {
            if ($response->ok()) {
                return Arr::except($data, ['TotalCount', 'PageSize', 'RequestId', 'PageNumber']);
            }

            Log::error('[AliYun - '.$action.'] 返回错误信息：'.$data['Message']);
        } else {
            Log::error('[AliYun - '.$action.'] 请求失败');
        }

        exit(400);
    }

    private function computeSignature(array $parameters): string
    { // 签名
        ksort($parameters, SORT_STRING);

        $stringToBeSigned = 'POST&%2F&'.urlencode(http_build_query($parameters));

        return base64_encode(hash_hmac('sha1', $stringToBeSigned, $this->accessKeySecret.'&', true));
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $records = $this->getRecordId($type, $original_ip);
        $domainInfo = $this->analysisDomain();
        if ($records && $domainInfo) {
            $ret = $this->send('UpdateDomainRecord', ['RR' => $domainInfo['rr'], 'RecordId' => Arr::first($records), 'Type' => $type, 'Value' => $latest_ip]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordId(string $type, string $ip): array|false
    { // 域名信息
        $parameters = ['SubDomain' => $this->subDomain];
        if ($type) {
            $parameters['Type'] = $type;
        }
        $records = $this->send('DescribeSubDomainRecords', $parameters);

        if ($records) {
            $filtered = data_get($records, 'DomainRecords.Record');
            if ($ip) {
                $filtered = Arr::where($filtered, static function (array $value) use ($ip) {
                    return $value['Value'] === $ip;
                });
            }

            return data_get($filtered, '*.RecordId');
        }

        return false;
    }

    public function destroy(string $type = '', string $ip = ''): int
    {
        $records = $this->getRecordId($type, $ip);
        $count = 0;
        if ($records) {
            foreach ($records as $record) {
                if ($this->send('DeleteDomainRecord', ['RecordId' => $record])) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
