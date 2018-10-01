<?php

namespace App\Components;

use App\Http\Models\Config;
use App\Http\Models\EmailLog;
use LSS\XML2Array;
use Log;

class Namesilo
{
    protected static $host;
    protected static $systemConfig;

    function __construct()
    {
        self::$host = 'https://www.namesilo.com/api/';
        self::$systemConfig = Helpers::systemConfig();
    }

    // 列出账号下所有域名
    public function listDomains()
    {
        return $this->send('listDomains');
    }

    // 列出指定域名的所有DNS记录
    public function dnsListRecords($domain)
    {
        $query = [
            'domain' => $domain
        ];

        return $this->send('dnsListRecords', $query);
    }

    // 为指定域名添加DNS记录
    public function dnsAddRecord($domain, $host, $value, $type = 'A', $ttl = 7207)
    {
        $query = [
            'domain'  => $domain,
            'rrtype'  => $type,
            'rrhost'  => $host,
            'rrvalue' => $value,
            'rrttl'   => $ttl
        ];

        return $this->send('dnsAddRecord', $query);
    }

    // 更新DNS记录
    public function dnsUpdateRecord($domain, $id, $host, $value, $ttl = 7207)
    {
        $query = [
            'domain'  => $domain,
            'rrid'    => $id,
            'rrhost'  => $host,
            'rrvalue' => $value,
            'rrttl'   => $ttl
        ];

        return $this->send('dnsUpdateRecord', $query);
    }

    // 删除DNS记录
    public function dnsDeleteRecord($domain, $id)
    {
        $data = [
            'domain' => $domain,
            'rrid'   => $id
        ];

        return $this->send('dnsDeleteRecord', $data);
    }

    // 发送请求
    private function send($operation, $data = [])
    {
        $params = [
            'version' => 1,
            'type'    => 'xml',
            'key'     => self::$systemConfig['namesilo_key']
        ];
        $query = array_merge($params, $data);

        $content = '请求操作：[' . $operation . '] --- 请求数据：[' . http_build_query($query) . ']';

        try {
            $result = $this->curlRequest(self::$host . $operation . '?' . http_build_query($query));
            $result = XML2Array::createArray($result);

            // 出错
            if (empty($result['namesilo']) || $result['namesilo']['reply']['code'] != 300 || $result['namesilo']['reply']['detail'] != 'success') {
                $this->addEmailLog(1, '[Namesilo API] - [' . $operation . ']', $content, 0, $result['namesilo']['reply']['detail']);
            }

            return $result['namesilo']['reply'];
        } catch (\Exception $e) {
            Log::error('CURL请求失败：' . $e->getMessage() . ' --- ' . $e->getLine());
            $this->addEmailLog(1, '[Namesilo API] - [' . $operation . ']', $content, 0, $e->getMessage());

            return false;
        }
    }

    /**
     * 写入邮件发送日志
     *
     * @param int    $user_id 用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     */
    private function addEmailLog($user_id, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $user_id;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }

    // 发起一个CURL请求
    private function curlRequest($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        // 如果data有数据，则用POST请求
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}