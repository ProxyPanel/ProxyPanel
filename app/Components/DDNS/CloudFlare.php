<?php

namespace App\Components\DDNS;

use Arr;
use Http;
use Log;

class CloudFlare
{
    private static $apiHost = 'https://api.cloudflare.com/client/v4/';

    private static $subDomain;

    private $zoneIdentifier;

    private $client;

    public function __construct($subDomain)
    {
        self::$subDomain = $subDomain;
        $this->zoneIdentifier = $this->getZone();
        $this->client = Http::withHeaders(['X-Auth-Key' => sysConfig('ddns_secret'), 'X-Auth-Email' => sysConfig('ddns_key')]);
    }

    private function getZone()
    {
        $zoneInfo = $this->client->get(self::$apiHost.'zones')->json();
        if ($zoneInfo && Arr::has($zoneInfo, 'result.0.id')) {
            foreach ($zoneInfo['result'] as $zone) {
                if (str_contains(self::$subDomain, $zone['name'])) {
                    return [$zone['name'], rtrim(substr(self::$subDomain, 0, -strlen($zone['name'])), '.'), $zone['id']];
                }
            }
        }

        return [];
    }

    public function store($ip, $type)
    {
        if ($this->zoneIdentifier) {
            return $this->send('create', ['type' => $type, 'name' => self::$subDomain, 'content' => $ip, 'ttl' => 120]);
        }

        return false;
    }

    private function send($action, $data = [], $id = null)
    {
        if ($this->zoneIdentifier) {
            switch ($action) {
                case 'get':
                    $response = $this->client->get(self::$apiHost.'zones/'.$this->zoneIdentifier[2].'/dns_records', $data);
                    break;
                case 'create':
                    $response = $this->client->post(self::$apiHost.'zones/'.$this->zoneIdentifier[2].'/dns_records', $data);
                    break;
                case 'update':
                    $response = $this->client->put(self::$apiHost.'zones/'.$this->zoneIdentifier[2].'/dns_records/'.$id, $data);
                    break;
                case 'delete':
                    $response = $this->client->delete(self::$apiHost.'zones/'.$this->zoneIdentifier[2].'/dns_records/'.$id);
                    break;
                default:
                    return false;
            }

            $message = $response->json();
            if ($message && ! $response->failed()) {
                return $message;
            }

            Log::error('[CloudFlare API] - ['.$action.'] 请求失败：'.var_export($message, true));
        }

        return false;
    }

    public function update($ip, $type)
    {
        $recordId = $this->getRecordId($type);

        if ($this->zoneIdentifier && $recordId) {
            return $this->send('update', ['type' => $type, 'name' => self::$subDomain, 'content' => $ip, 'ttl' => 120], $recordId[0]);
        }

        return false;
    }

    private function getRecordId($type = null)
    {
        $parameters['name'] = self::$subDomain;
        if ($type) {
            $parameters['type'] = $type;
        }
        $dnsList = $this->send('get', $parameters);

        if ($dnsList && Arr::has($dnsList, 'result.0.id')) {
            $dnsRecords = $dnsList['result'];
            $data = null;
            foreach ($dnsRecords as $record) {
                $data[] = $record['id'];
            }

            return $data ?: false;
        }

        return false;
    }

    public function destroy($type)
    {
        $records = $this->getRecordId($type);
        if ($records && $this->zoneIdentifier) {
            $count = 0;
            foreach ($records as $record) {
                $result = $this->send('delete', [], $record);
                if ($result && Arr::has($result, 'result.id')) {
                    $count++;
                }
            }

            return $count;
        }

        return false;
    }
}
