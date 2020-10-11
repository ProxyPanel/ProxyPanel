<?php

namespace App\Components\DDNS;

use Arr;
use Log;

class Namesilo
{
    private static $subDomain;

    public function __construct($subDomain)
    {
        self::$subDomain = $subDomain;
    }

    public function store($ip, $type = 'A')
    {
        $domainInfo = $this->analysisDomain();
        if ($domainInfo) {
            return $this->send('dnsAddRecord', [
                'domain'  => $domainInfo[0],
                'rrtype'  => $type,
                'rrhost'  => $domainInfo[1],
                'rrvalue' => $ip,
                'rrttl'   => 3600,
            ]);
        }

        return false;
    }

    private function analysisDomain()
    {
        // TODO: 尚未进行，多域名环境下，获取信息测试
        $domainList = $this->domainList();
        if ($domainList) {
            foreach ($domainList as $domain) {
                if (strpos(self::$subDomain, $domain) !== false) {
                    return [$domain, rtrim(substr(self::$subDomain, 0, -(strlen($domain))), '.')];
                }
            }
        }

        return false;
    }

    public function domainList()
    {
        $data = $this->send('listDomains');
        if ($data) {
            return $data['domains'];
        }

        return false;
    }

    private function send($operation, $data = [])
    {
        $params = [
            'version' => 1,
            'type'    => 'xml',
            'key'     => sysConfig('ddns_key'),
        ];
        $query = array_merge($params, $data);

        $result = file_get_contents('https://www.namesilo.com/api/'.$operation.'?'.http_build_query($query));
        $result = json_decode(json_encode(simplexml_load_string(trim($result))), true);

        if ($result && $result['reply']['code'] === '300' && $result['reply']['detail'] === 'success') {
            return $result['reply'];
        }

        Log::error('[Namesilo API] - ['.$operation.'] 请求失败：'.var_export($result, true));

        return false;
    }

    public function update($ip, $type)
    {
        $recordId = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();

        return $this->send('dnsUpdateRecord', [
            'domain'  => $domainInfo[0],
            'rrid'    => $recordId[0],
            'rrhost'  => $domainInfo[1],
            'rrvalue' => $ip,
            'rrttl'   => 3600,
        ]);
    }

    private function getRecordId($type = null)
    {
        $domainInfo = $this->analysisDomain();
        if ($domainInfo) {
            $records = $this->send('dnsListRecords', ['domain' => $domainInfo[0]]);

            if ($records && Arr::has($records, 'resource_record')) {
                $records = $records['resource_record'];
                $data = null;
                foreach ($records as $record) {
                    if (Arr::has($record, ['host', 'type', 'record_id']) && $record['host'] === self::$subDomain) {
                        if ($type) {
                            if ($type === $record['type']) {
                                $data[] = $record['record_id'];
                            }
                        } else {
                            $data[] = $record['record_id'];
                        }
                    }
                }

                return $data ?: false;
            }
        }

        return false;
    }

    public function destory($type)
    {
        $records = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();
        if ($records && $domainInfo) {
            $count = 0;
            foreach ($records as $record) {
                $result = $this->send('dnsDeleteRecord', ['domain' => $domainInfo[0], 'rrid' => $record]);
                if ($result) {
                    $count++;
                }
            }

            return $count;
        }

        return false;
    }
}
