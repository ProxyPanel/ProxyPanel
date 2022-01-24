<?php

namespace App\Components\DDNS;

use Arr;
use Log;

class Namesilo
{
    private static $apiHost = 'https://www.namesilo.com/api/';
    private static $subDomain;

    public function __construct($subDomain)
    {
        self::$subDomain = $subDomain;
    }

    public function store($ip, $type)
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
        $domainList = $this->domainList();
        if ($domainList) {
            if (is_array($domainList)) {
                foreach ($domainList as $domain) {
                    if (str_contains(self::$subDomain, $domain)) {
                        return [$domain, rtrim(substr(self::$subDomain, 0, -(strlen($domain))), '.')];
                    }
                }
            } elseif (str_contains(self::$subDomain, $domainList)) {
                return [$domainList, rtrim(substr(self::$subDomain, 0, -(strlen($domainList))), '.')];
            }
        }

        return [];
    }

    public function domainList()
    {
        $data = $this->send('listDomains');
        if ($data) {
            return $data['domains']['domain'];
        }

        return false;
    }

    private function send($action, $data = [])
    {
        $params = [
            'version' => 1,
            'type'    => 'xml',
            'key'     => sysConfig('ddns_key'),
        ];
        $query = array_merge($params, $data);

        $result = file_get_contents(self::$apiHost.$action.'?'.http_build_query($query));
        $result = json_decode(json_encode(simplexml_load_string(trim($result))), true);

        if ($result && $result['reply']['code'] === '300' && $result['reply']['detail'] === 'success') {
            return $result['reply'];
        }
        Log::error('[Namesilo API] - ['.$action.'] 请求失败：'.var_export($result, true));

        return false;
    }

    public function update($ip, $type)
    {
        $recordId = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();

        if ($domainInfo && $recordId) {
            return $this->send('dnsUpdateRecord', [
                'domain'  => $domainInfo[0],
                'rrid'    => $recordId[0],
                'rrhost'  => $domainInfo[1],
                'rrvalue' => $ip,
                'rrttl'   => 3600,
            ]);
        }
        Log::error('[Namesilo API] - [更新] 处理失败：'.var_export($recordId, true).var_export($domainInfo, true));

        return false;
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

    public function destroy($type)
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
