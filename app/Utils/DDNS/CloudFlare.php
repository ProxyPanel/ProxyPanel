<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Http;
use Log;

class CloudFlare implements DNS
{
    // 开发依据: https://developers.cloudflare.com/api/
    private string $apiHost;

    private array $auth;

    public function __construct(private readonly string $subDomain)
    {
        $this->apiHost = 'https://api.cloudflare.com/client/v4/zones/';
        $this->auth = ['X-Auth-Key' => sysConfig('ddns_secret'), 'X-Auth-Email' => sysConfig('ddns_key')];
        $zoneIdentifier = $this->getZone();
        if ($zoneIdentifier) {
            $this->apiHost .= $zoneIdentifier.'/dns_records/';
        }
    }

    private function getZone(): string
    {
        $zones = $this->send('list');
        if ($zones) {
            foreach ($zones as $zone) {
                if (str_contains($this->subDomain, Arr::get($zone, 'name'))) {
                    return $zone['id'];
                }
            }
        }

        exit(400);
    }

    private function send(string $action, array $parameters = [], ?string $identifier = null): array
    {
        $client = Http::timeout(10)->retry(3, 1000)->withHeaders($this->auth)->baseUrl($this->apiHost)->asJson();

        $response = match ($action) {
            'list' => $client->get(''),
            'get' => $client->get('', $parameters),
            'create' => $client->post('', $parameters),
            'update' => $client->put($identifier, $parameters),
            'delete' => $client->delete($identifier),
        };

        $data = $response->json();
        if ($data) {
            if ($response->ok() && Arr::get($data, 'success')) {
                return Arr::get($data, 'result');
            }
            Log::error('[CloudFlare - '.$action.'] 返回错误信息：'.Arr::get($data, 'errors.error_chain.message', Arr::get($data, 'errors.0.message')));
        } else {
            Log::error('[CloudFlare - '.$action.'] 请求失败');
        }

        exit(400);
    }

    public function store(string $ip, string $type): bool
    {
        $ret = $this->send('create', ['content' => $ip, 'name' => $this->subDomain, 'type' => $type]);

        return (bool) $ret;
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $recordId = Arr::first($this->getRecordId($type, $original_ip));

        if ($recordId) {
            $ret = $this->send('update', ['content' => $latest_ip, 'name' => $this->subDomain, 'type' => $type], $recordId);
        }

        return (bool) ($ret ?? false);
    }

    private function getRecordId(string $type, string $ip): array|false
    {
        $records = $this->send('get', ['content' => $ip, 'name' => $this->subDomain, 'type' => $type]);

        if ($records) {
            return data_get($records, '*.id');
        }

        return false;
    }

    public function destroy(string $type, string $ip): int
    {
        $records = $this->getRecordId($type, $ip);
        $count = 0;
        if ($records) {
            foreach ($records as $record) {
                $ret = $this->send('delete', [], $record);
                if ($ret) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
