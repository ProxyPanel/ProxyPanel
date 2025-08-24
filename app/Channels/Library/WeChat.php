<?php

namespace App\Channels\Library;

use DOMDocument;
use Exception;
use Log;
use Str;

class WeChat
{
    public string $key;

    public string $iv;

    public function __construct()
    {
        $this->key = base64_decode(sysConfig('wechat_encodingAESKey').'=');
        $this->iv = substr($this->key, 0, 16);
    }

    public function encryptMsg(string $sReplyMsg, ?int $sTimeStamp, string $sNonce, string &$sEncryptMsg): int
    { // 将公众平台回复用户的消息加密打包.
        $array = $this->prpcrypt_encrypt($sReplyMsg); // 加密

        if ($array[0] !== 0) {
            return $array[0];
        }

        $encrypt = $array[1];
        $sTimeStamp = $sTimeStamp ?? time();
        $array = $this->getSHA1($sTimeStamp, $sNonce, $encrypt);

        if ($array[0] !== 0) {
            return $array[0];
        }

        $signature = $array[1];
        $sEncryptMsg = $this->generate($encrypt, $signature, $sTimeStamp, $sNonce);

        return 0;
    }

    public function prpcrypt_encrypt(string $data): array
    {
        try {
            // 拼接
            $data = Str::random().pack('N', strlen($data)).$data.sysConfig('wechat_cid');
            // 添加PKCS#7填充
            $data = $this->pkcs7_encode($data);
            // 加密
            $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv);

            return [0, $encrypted];
        } catch (Exception $e) {
            Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.wechat'), 'reason' => var_export($e->getMessage(), true)]));

            return [-40006, null]; // EncryptAESError
        }
    }

    public function pkcs7_encode(string $data): string
    {// 对需要加密的明文进行填充补位
        // 计算需要填充的位数
        $padding = 32 - (strlen($data) % 32);
        $padding = ($padding === 0) ? 32 : $padding;
        $pattern = chr($padding);

        return $data.str_repeat($pattern, $padding); // 获得补位所用的字符
    }

    public function getSHA1(string $timestamp, string $nonce, string $encryptMsg): array
    {
        $data = [$encryptMsg, sysConfig('wechat_token'), $timestamp, $nonce];
        sort($data, SORT_STRING);
        $signature = sha1(implode($data));

        return [0, $signature];
    }

    /**
     * 生成xml消息.
     *
     * @param  string  $encrypt  加密后的消息密文
     * @param  string  $signature  安全签名
     * @param  string  $timestamp  时间戳
     * @param  string  $nonce  随机字符串
     */
    public function generate(string $encrypt, string $signature, string $timestamp, string $nonce): string
    {
        $format = <<<'XML'
<xml>
    <Encrypt><![CDATA[%s]]></Encrypt>
    <MsgSignature><![CDATA[%s]]></MsgSignature>
    <TimeStamp>%s</TimeStamp>
    <Nonce><![CDATA[%s]]></Nonce>
</xml>
XML;

        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    public function decryptMsg(string $sMsgSignature, ?int $sTimeStamp, string $sNonce, string $sPostData, string &$sMsg)
    { // 检验消息的真实性，并且获取解密后的明文.
        // 提取密文
        [$code, $encrypt] = $this->extract($sPostData);
        if ($code !== 0) {
            return $code;
        }

        $sTimeStamp = $sTimeStamp ?? time();

        $this->verifySignature($sMsgSignature, $sTimeStamp, $sNonce, $encrypt, $sMsg); // 验证安全签名
    }

    /**
     * 提取出xml数据包中的加密消息.
     *
     * @param  string  $xmlText  待提取的xml字符串
     * @return array 提取出的加密消息字符串
     */
    public function extract(string $xmlText): array
    {
        try {
            $xml = new DOMDocument;
            $xml->loadXML($xmlText);
            $encrypt = $xml->getElementsByTagName('Encrypt')->item(0)->nodeValue;

            return [0, $encrypt];
        } catch (Exception $e) {
            Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.wechat'), 'reason' => var_export($e->getMessage(), true)]));

            return [-40002, null]; // ParseXmlError
        }
    }

    public function verifySignature(string $sMsgSignature, string $sTimeStamp, string $sNonce, string $sEcho, string &$sMsg): int
    { // 验证URL
        // verify msg_signature
        [$code, $encrypt] = $this->extract($sEcho);

        if ($code !== 0) {
            return $code;
        }

        [$code, $signature] = $this->getSHA1($sTimeStamp, $sNonce, $encrypt);

        if ($code !== 0) {
            return $code;
        }

        if ($sMsgSignature !== $signature) {
            Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.wechat'), 'reason' => trans('notification.sign_failed')]));

            return -40004; // ValidateSignatureError
        }

        $sMsg = $encrypt;

        return 0;
    }

    public function prpcrypt_decrypt(string $encrypted): array
    {
        try {
            // 解密
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv);
        } catch (Exception $e) {
            Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.wechat'), 'reason' => var_export($e->getMessage(), true)]));

            return [-40007, null]; // DecryptAESError
        }
        try {
            // 删除PKCS#7填充
            $result = $this->pkcs7_decode($decrypted);
            if (strlen($result) < 16) {
                return [];
            }
            // 拆分
            $content = substr($result, 16, strlen($result));
            $len_list = unpack('N', substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_receiveId = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            // 发送错误
            Log::critical(trans('notification.error', ['channel' => trans('admin.system.notification.channel.wechat'), 'reason' => var_export($e->getMessage(), true)]));

            return [-40008, null]; // IllegalBuffer
        }
        if ($from_receiveId !== sysConfig('wechat_cid')) {
            return [-40005, null]; // ValidateCorpidError
        }

        return [0, $xml_content];
    }

    public function pkcs7_decode(string $encrypted): string
    {// 对解密后的明文进行补位删除
        $length = strlen($encrypted);
        $padding = ord($encrypted[$length - 1]);

        if ($padding < 1 || $padding > 32) {
            return $encrypted;
        }

        return substr($encrypted, 0, $length - $padding);
    }
}
