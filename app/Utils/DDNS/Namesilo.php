<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Log;

class Namesilo implements DNS
{
    // 开发依据: https://www.namesilo.com/api-reference
    private const API_HOST = 'https://www.namesilo.com/api/';

    private string $apiKey;

    private array $domainData;

    public function __construct(private readonly string $subDomain)
    {
        $this->apiKey = sysConfig('ddns_key');
        $data = $this->analysisDomain();
        if ($data) {
            $this->domainData = $data;
        } else {
            abort(400, '域名存在异常');
        }
    }

    private function analysisDomain(): array
    {
        $domains = Arr::get($this->send('listDomains'), 'domains.domain');
        if ($domains) {
            foreach ($domains as $domain) {
                if (str_contains($this->subDomain, $domain)) {
                    return ['rr' => rtrim(substr($this->subDomain, 0, -strlen($domain)), '.'), 'host' => $domain];
                }
            }
            Log::error('[DNS] Namesilo - 错误域名 '.$this->subDomain.' 不在账号拥有域名里');
        }

        exit(400);
    }

    private function send(string $operation, array $parameters = []): array
    {
        $request = simplexml_load_string(file_get_contents(self::API_HOST.$operation.'?'.Arr::query(array_merge(['version' => 1, 'type' => 'xml', 'key' => $this->apiKey], $parameters))));

        if ($request) {
            $result = json_decode(json_encode($request), true);
            if ($result && $result['reply']['code'] === '300') {
                return Arr::except($result['reply'], ['code', 'detail']);
            }

            Log::error('[Namesilo - '.$operation.'] 返回错误信息：'.$result['reply']['detail']);
        } else {
            Log::error('[Namesilo - '.$operation.'] 请求失败');
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        $ret = $this->send('dnsAddRecord', [
            'domain' => $this->domainData['host'],
            'rrtype' => $type,
            'rrhost' => $this->domainData['rr'],
            'rrvalue' => $ip,
            'rrttl' => 3600,
        ]);

        return (bool) $ret;
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $record = Arr::first($this->getRecordId($type, $original_ip));
        if ($record) {
            $ret = $this->send('dnsUpdateRecord', [
                'domain' => $this->domainData['host'],
                'rrid' => $record,
                'rrhost' => $this->domainData['rr'],
                'rrvalue' => $latest_ip,
                'rrttl' => 3600,
            ]);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordId(string $type, string $ip): array|false
    {
        $records = $this->send('dnsListRecords', ['domain' => $this->domainData['host']]);

        if (Arr::has($records, 'resource_record')) {
            $records = $records['resource_record'];

            if ($ip) {
                $filtered = Arr::where($records, function (array $value) use ($ip) {
                    return $value['host'] === $this->subDomain && $value['value'] === $ip;
                });
            } elseif ($type) {
                $filtered = Arr::where($records, function (array $value) use ($type) {
                    return $value['host'] === $this->subDomain && $value['type'] === $type;
                });
            } else {
                $filtered = Arr::where($records, function (array $value) {
                    return $value['host'] === $this->subDomain;
                });
            }

            if ($filtered) {
                return data_get($filtered, '*.record_id');
            }
        }

        return false;
    }

    public function destroy(string $type = '', string $ip = ''): int
    {
        $records = $this->getRecordId($type, $ip);
        $count = 0;
        if ($records) {
            foreach ($records as $record) {
                $result = $this->send('dnsDeleteRecord', ['domain' => $this->domainData['host'], 'rrid' => $record]);

                if ($result === []) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
