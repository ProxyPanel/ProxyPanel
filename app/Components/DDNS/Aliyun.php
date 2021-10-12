<?php

namespace App\Components\DDNS;

use Arr;
use Http;
use Log;

class Aliyun
{
    private static $apiHost = 'https://alidns.aliyuncs.com/';
    private static $subDomain;

    public function __construct($subDomain)
    {
        self::$subDomain = $subDomain;
    }

    public function store($ip, $type)
    {
        $domainInfo = $this->analysisDomain();

        if ($domainInfo) {
            return $this->send('AddDomainRecord', ['DomainName' => $domainInfo[0], 'RR' => $domainInfo[1], 'Type' => $type, 'Value' => $ip]);
        }

        return false;
    }

    private function analysisDomain()
    {
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
        $result = $this->send('DescribeDomains');
        if ($result) {
            $result = $result['Domains']['Domain'];
            if ($result) {
                return Arr::pluck($result, 'DomainName');
            }
        }

        return false;
    }

    private function send($action, $data = [])
    {
        $public = [
            'Format' => 'JSON',
            'Version' => '2015-01-09',
            'AccessKeyId' => sysConfig('ddns_key'),
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"), //公共参数Timestamp GMT时间
            'SignatureVersion' => '1.0',
            'SignatureNonce' => str_replace('.', '', microtime(true)), //唯一数，用于防止网络重放攻击
        ];
        $parameters = array_merge(['Action' => $action], $data, $public);
        $parameters['Signature'] = $this->computeSignature($parameters);

        $response = Http::asForm()->timeout(15)->post(self::$apiHost, $parameters);
        $message = $response->json();

        if ($response->failed()) {
            if ($message && $message['Code']) {
                $error = $message['Message'];
            } else {
                $error = $response->body();
            }
            Log::error('[Aliyun - '.$action.'] 请求失败：'.$error);

            return false;
        }

        return $message;
    }

    // 签名
    private function computeSignature($parameters): string
    {
        ksort($parameters, SORT_STRING);

        $stringToBeSigned = 'POST&%2F&'.urlencode(http_build_query($parameters));

        return base64_encode(hash_hmac('sha1', $stringToBeSigned, sysConfig('ddns_secret').'&', true));
    }

    public function update($ip, $type)
    {
        $recordId = $this->getRecordId($type);
        $domainInfo = $this->analysisDomain();
        if ($recordId && $domainInfo) {
            return $this->send('UpdateDomainRecord', ['RR' => $domainInfo[1], 'RecordId' => $recordId[0], 'Type' => $type, 'Value' => $ip]);
        }

        return false;
    }

    /**
     * 域名信息.
     *
     * @param  string|null  $type  记录类型,默认为 null
     * @return array|false
     */
    private function getRecordId($type = null)
    {
        $parameters = ['SubDomain' => self::$subDomain];
        if ($type) {
            $parameters['Type'] = $type;
        }
        $records = $this->send('DescribeSubDomainRecords', $parameters);

        if ($records && Arr::has($records, 'DomainRecords.Record')) {
            $records = $records['DomainRecords']['Record'];
            $data = null;
            foreach ($records as $record) {
                $data[] = $record['RecordId'];
            }

            return $data ?: false;
        }

        return false;
    }

    public function destroy($type)
    {
        $records = $this->getRecordId($type);
        if ($records) {
            $count = 0;
            foreach ($records as $record) {
                $result = $this->send('DeleteDomainRecord', ['RecordId' => $record]);
                if ($result) {
                    $count++;
                }
            }

            return $count;
        }

        return false;
    }
}
