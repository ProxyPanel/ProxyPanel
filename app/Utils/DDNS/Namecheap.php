<?php

namespace App\Utils\DDNS;

use App\Utils\IP;
use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;

class Namecheap implements DNS
{
    //  开发依据: https://www.namecheap.com/support/api/methods/
    private const API_ENDPOINT = 'https://api.namecheap.com/xml.response';

    public const KEY = 'namecheap';

    public const LABEL = 'Namecheap';

    private string $accessKeyID;

    private string $accessKeySecret;

    private array $domainInfo;

    private array $domainRecords;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->accessKeySecret = sysConfig('ddns_secret');
        $this->domainInfo = $this->parseDomainInfo();
        $this->domainRecords = $this->fetchDomainRecords();
    }

    private function parseDomainInfo(): array
    {
        $domains = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_map(static function ($domain) {
                return $domain['@attributes']['Name'];
            }, $this->sendRequest('namecheap.domains.getList')['DomainGetListResult']['Domain']);
        });

        if ($domains) {
            $matched = Arr::first($domains, fn ($domain) => str_contains($this->subdomain, $domain));
        }

        if (empty($matched)) {
            throw new RuntimeException("[Namecheap – domains.getList] The subdomain {$this->subdomain} does not match any domain in your account.");
        }

        $domainParts = explode('.', $matched);

        return [
            'sub' => rtrim(substr($this->subdomain, 0, -strlen($matched)), '.'),
            'domain' => $domainParts[0],
            'tld' => end($domainParts),
        ];
    }

    private function sendRequest(string $command, array $parameters = []): array
    {
        $parameters = array_merge([
            'ApiUser' => $this->accessKeyID,
            'ApiKey' => $this->accessKeySecret,
            'UserName' => $this->accessKeyID,
            'ClientIp' => IP::getClientIP(),
            'Command' => $command,
        ], $parameters);

        $response = Http::timeout(15)->retry(3, 1000)->get(self::API_ENDPOINT, $parameters);
        $data = $response->body();
        if ($data) {
            $data = json_decode(json_encode(simplexml_load_string($data)), true);
            if ($response->successful() && $data['@attributes']['Status'] === 'OK') {
                return $data['CommandResponse'];
            }
            Log::error('[Namecheap - '.$command.'] 返回错误信息：'.$data['Errors']['Error'] ?? 'Unknown error');
        } else {
            Log::error('[Namecheap - '.$command.'] 请求失败');
        }

        exit(400);
    }

    private function fetchDomainRecords(): array
    {
        $records = $this->sendRequest('namecheap.domains.dns.getHosts', ['SLD' => $this->domainInfo['domain'], 'TLD' => $this->domainInfo['tld']]);

        if (isset($records['DomainDNSGetHostsResult']['host'])) {
            $hosts = $records['DomainDNSGetHostsResult']['host'];

            if (isset($hosts[0])) {
                foreach ($hosts as $record) {
                    $ret[] = $this->parseRecordData($record);
                }
            } else {
                $ret[] = $this->parseRecordData($hosts);
            }
        }

        return $ret ?? [];
    }

    private function parseRecordData(array $record): array
    {
        return [
            'name' => $record['@attributes']['Name'],
            'type' => $record['@attributes']['Type'],
            'address' => $record['@attributes']['Address'],
            'ttl' => $record['@attributes']['TTL'],
        ];
    }

    public function store(string $ip, string $type): bool
    {
        $this->domainRecords[] = [
            'name' => $this->domainInfo['sub'],
            'type' => $type,
            'address' => $ip,
            'ttl' => '60',
        ];

        return $this->updateDomainRecords();
    }

    private function updateDomainRecords(): bool
    {
        $para = ['SLD' => $this->domainInfo['domain'], 'TLD' => $this->domainInfo['tld']];
        foreach ($this->domainRecords as $index => $record) {
            $para['HostName'.($index + 1)] = $record['name'];
            $para['RecordType'.($index + 1)] = $record['type'];
            $para['Address'.($index + 1)] = $record['address'];
            $para['TTL'.($index + 1)] = $record['ttl'];
        }

        return $this->sendRequest('namecheap.domains.dns.setHosts', $para)['DomainDNSSetHostsResult']['@attributes']['IsSuccess'] === 'true';
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        foreach ($this->domainRecords as &$record) {
            if ($record['address'] === $original_ip && $record['name'] === $this->domainInfo['sub'] && $record['type'] === $type) {
                $record['address'] = $latest_ip;

                return $this->updateDomainRecords();
            }
        }

        return false;
    }

    public function destroy(string $type, string $ip): int|bool
    {
        if ($ip) {
            $this->domainRecords = array_filter($this->domainRecords, function ($record) use ($ip) {
                return $record['address'] !== $ip || $record['name'] !== $this->domainInfo['sub'];
            });
        } elseif ($type) {
            $this->domainRecords = array_filter($this->domainRecords, function ($record) use ($type) {
                return $record['type'] !== $type || $record['name'] !== $this->domainInfo['sub'];
            });
        } else {
            $this->domainRecords = array_filter($this->domainRecords, function ($record) {
                return $record['name'] !== $this->domainInfo['sub'];
            });
        }

        return $this->updateDomainRecords();
    }
}
