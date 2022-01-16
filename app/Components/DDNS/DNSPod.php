<?php

namespace App\Components\DDNS;

use Arr;
use Http;
use Log;

class DNSPod
{
    private static $apiHost = 'https://dnsapi.cn/';
    private static $subDomain;

    public function __construct($subDomain)
    {
        self::$subDomain = $subDomain;
    }

    public function store($ip, $type)
    {
        $domainInfo = $this->analysisDomain();

        if ($domainInfo) {
            return $this->send('Record.Create', [
                'domain_id' => $domainInfo[2],
                'sub_domain' => $domainInfo[1],
                'record_type' => $type,
                'record_line_id' => 0,
                'value' => $ip,
            ]);
        }

        return false;
    }

    private function analysisDomain()
    {
        $domainList = $this->domainList();
        if ($domainList) {
            foreach ($domainList as $key => $domain) {
                if (str_contains(self::$subDomain, $domain)) {
                    return [$domain, rtrim(substr(self::$subDomain, 0, -(strlen($domain))), '.'), $key];
                }
            }
        }

        return [];
    }

    public function domainList()
    {
        $domainList = $this->send('Domain.List', ['type' => 'mine']);
        if ($domainList) {
            return Arr::pluck($domainList['domains'], 'name', 'id');
        }

        return false;
    }

    private function send($action, $data = null)
    {
        $parameters = [
            'login_token' => sysConfig('ddns_key').','.sysConfig('ddns_secret'),
            'format' => 'json',
        ];

        if ($data) {
            $parameters = array_merge($data, $parameters);
        }

        $response = Http::timeout(15)->asForm()->post(self::$apiHost.$action, $parameters);
        $message = $response->json();

        if ($response->failed() || ($message && Arr::has($message, 'status.code') && $message['status']['code'] !== '1')) {
            if ($message && Arr::has($message, 'status.code') && $message['status']['code'] !== '1') {
                $error = $message['status']['message'];
            } else {
                $error = $response->body();
            }
            Log::error('[DNSPod - '.$action.'] 请求失败：'.$error);

            return false;
        }

        return $message;
    }

    public function update($ip, $type)
    {
        $recordId = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();
        if ($recordId && $domainInfo) {
            return $this->send('Record.Modify', [
                'domain_id' => $domainInfo[2],
                'record_id' => $recordId[0],
                'sub_domain' => $domainInfo[1],
                'record_type' => $type,
                'record_line_id' => 0,
                'value' => $ip,
            ]);
        }

        return false;
    }

    private function getRecordId($type = null)
    {
        $domainInfo = $this->analysisDomain();
        if ($domainInfo) {
            $parameters = ['domain_id' => $domainInfo[2], 'sub_domain' => $domainInfo[1]];
            if ($type) {
                $parameters['record_type'] = $type;
            }
            $records = $this->send('Record.List', $parameters);
            if ($records && Arr::has($records, 'records')) {
                return Arr::pluck($records['records'], 'id');
            }
        }

        return false;
    }

    public function destroy($type)
    {
        $records = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();
        if ($records && $domainInfo) {
            $count = 0;
            foreach ($records as $record) {
                $result = $this->send('Record.Remove', ['domain_id' => $domainInfo[2], 'record_id' => $record]);
                if ($result) {
                    $count++;
                }
            }

            return $count;
        }

        return false;
    }

    public function version()
    {
        $version = $this->send('Info.Version');
        if ($version) {
            return $version['status']['message'];
        }

        return false;
    }
}
