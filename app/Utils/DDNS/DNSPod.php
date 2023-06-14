<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Http;
use Log;

class DNSPod implements DNS
{
    // 开发依据: https://docs.dnspod.cn/api/
    private const API_HOST = 'https://dnsapi.cn/';

    private string $loginToken;

    private array $domainData;

    public function __construct(private readonly string $subDomain)
    {
        $this->loginToken = sysConfig('ddns_key').','.sysConfig('ddns_secret');

        $data = $this->analysisDomain();
        if ($data) {
            $this->domainData = $data;
        } else {
            abort(400, '域名存在异常');
        }
    }

    private function analysisDomain(): array
    {
        $domains = data_get($this->send('Domain.List', ['type' => 'mine']), 'domains.*.name');
        if ($domains) {
            foreach ($domains as $domain) {
                if (str_contains($this->subDomain, $domain)) {
                    return ['rr' => rtrim(substr($this->subDomain, 0, -strlen($domain)), '.'), 'host' => $domain];
                }
            }
            Log::error('[DNS] DNSPod - 错误域名 '.$this->subDomain.' 不在账号拥有域名里');
        }

        exit(400);
    }

    private function send(string $action, array $parameters = []): array
    {
        $response = Http::timeout(15)->asForm()->post(self::API_HOST.$action, array_merge(['login_token' => $this->loginToken, 'format' => 'json'], $parameters));

        if ($response->ok()) {
            $data = $response->json();
            if (Arr::get($data, 'status.code') === 1) {
                return Arr::except($data, ['status']);
            }

            Log::error('[DNSPod - '.$action.'] 返回错误信息：'.Arr::get($data, 'status.message'));
        } else {
            Log::error('[DNSPod - '.$action.'] 请求失败');
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        $ret = $this->send('Record.Create', [
            'domain' => $this->domainData['host'],
            'sub_domain' => $this->domainData['rr'],
            'record_type' => $type,
            'record_line_id' => 0,
            'value' => $ip,
        ]);

        return (bool) $ret;
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $record = Arr::first($this->getRecordId($type, $original_ip));
        if ($record) {
            $ret = $this->send('Record.Modify', [
                'domain' => $this->domainData['host'],
                'record_id' => $record,
                'sub_domain' => $this->domainData['rr'],
                'record_type' => $type,
                'record_line_id' => 0,
                'value' => $latest_ip,
            ]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordId(string $type, string $ip): array|false
    {
        $parameters = ['domain' => $this->domainData['host'], 'sub_domain' => $this->domainData['rr']];
        if ($type) {
            $parameters['record_type'] = $type;
        }
        $records = $this->send('Record.List', $parameters);

        if ($records) {
            $filtered = Arr::get($records, 'records');
            if ($ip) {
                $filtered = Arr::where($filtered, static function (array $value) use ($ip) {
                    return $value['value'] === $ip;
                });
            }

            return data_get($filtered, '*.id');
        }

        return false;
    }

    public function destroy(string $type, string $ip): int
    {
        $records = $this->getRecordId($type, $ip);
        $count = 0;
        if ($records) {
            foreach ($records as $record) {
                $result = $this->send('Record.Remove', ['domain' => $this->domainData['host'], 'record_id' => $record]);
                if ($result === []) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
