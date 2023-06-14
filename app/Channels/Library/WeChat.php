<?php

namespace App\Channels\Library;

use DOMDocument;
use Exception;
use Log;
use Str;

class WeChat
{
    public function EncryptMsg(string $sReplyMsg, int|null $sTimeStamp, string $sNonce, string &$sEncryptMsg)
    { //将公众平台回复用户的消息加密打包.
        //加密
        $array = (new Prpcrypt())->encrypt($sReplyMsg);
        $ret = $array[0];
        if ($ret !== 0) {
            return $ret;
        }

        if ($sTimeStamp === null) {
            $sTimeStamp = time();
        }
        $encrypt = $array[1];

        //生成安全签名
        $array = $this->getSHA1($sTimeStamp, $sNonce, $encrypt);
        $ret = $array[0];
        if ($ret !== 0) {
            return $ret;
        }
        $signature = $array[1];

        //生成发送的xml
        $sEncryptMsg = $this->generate($encrypt, $signature, $sTimeStamp, $sNonce);

        return 0;
    }

    public function getSHA1(string $timestamp, string $nonce, string $encrypt_msg): array
    {
        //排序
        try {
            $array = [$encrypt_msg, sysConfig('wechat_token'), $timestamp, $nonce];
            sort($array, SORT_STRING);

            return [0, sha1(implode($array))];
        } catch (Exception $e) {
            Log::critical('企业微信消息推送异常：'.var_export($e->getMessage(), true));

            return [-40003, null]; // ComputeSignatureError
        }
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
        $format = '<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>';

        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    public function DecryptMsg(string $sMsgSignature, int|null $sTimeStamp, string $sNonce, string $sPostData, string &$sMsg)
    { // 检验消息的真实性，并且获取解密后的明文.
        //提取密文
        $array = $this->extract($sPostData);
        $ret = $array[0];

        if ($ret !== 0) {
            return $ret;
        }

        if ($sTimeStamp === null) {
            $sTimeStamp = time();
        }

        $encrypt = $array[1];

        //验证安全签名
        $this->verifyURL($sMsgSignature, $sTimeStamp, $sNonce, $encrypt, $sMsg);
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
            $xml = new DOMDocument();
            $xml->loadXML($xmlText);
            $array_e = $xml->getElementsByTagName('Encrypt');
            $encrypt = $array_e->item(0)->nodeValue;

            return [0, $encrypt];
        } catch (Exception $e) {
            Log::critical('企业微信消息推送异常：'.var_export($e->getMessage(), true));

            return [-40002, null]; // ParseXmlError
        }
    }

    public function verifyURL(string $sMsgSignature, string $sTimeStamp, string $sNonce, string $sEchoStr, string &$sReplyEchoStr)
    { // 验证URL
        //verify msg_signature
        $array = $this->getSHA1($sTimeStamp, $sNonce, $sEchoStr);
        $ret = $array[0];

        if ($ret !== 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature !== $sMsgSignature) {
            return -40001; // ValidateSignatureError
        }

        $result = (new Prpcrypt())->decrypt($sEchoStr);
        if ($result[0] !== 0) {
            return $result[0];
        }
        $sReplyEchoStr = $result[1];

        return 0;
    }
}

/**
 * PKCS7Encoder class.
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static int $block_size = 32;

    public function encode(string $text): string
    { // 对需要加密的明文进行填充补位
        //计算需要填充的位数
        $amount_to_pad = self::$block_size - (strlen($text) % self::$block_size);
        if ($amount_to_pad === 0) {
            $amount_to_pad = self::$block_size;
        }

        return $text.str_repeat(chr($amount_to_pad), $amount_to_pad); // 获得补位所用的字符
    }

    public function decode(string $text): string
    { // 对解密后的明文进行补位删除
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > self::$block_size) {
            $pad = 0;
        }

        return substr($text, 0, strlen($text) - $pad);
    }
}

/**
 * Prpcrypt class.
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public string $key;

    public string $iv;

    public function __construct()
    {
        $this->key = base64_decode(sysConfig('wechat_encodingAESKey').'=');
        $this->iv = substr($this->key, 0, 16);
    }

    /**
     * 加密.
     */
    public function encrypt(string $text): array
    {
        try {
            //拼接
            $text = Str::random().pack('N', strlen($text)).$text.sysConfig('wechat_cid');
            //添加PKCS#7填充
            $text = (new PKCS7Encoder)->encode($text);
            //加密
            $encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv);

            return [0, $encrypted];
        } catch (Exception $e) {
            Log::critical('企业微信消息推送异常：'.var_export($e->getMessage(), true));

            return [-40006, null]; // EncryptAESError
        }
    }

    public function decrypt(string $encrypted): array
    { // 解密
        try {
            //解密
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv);
        } catch (Exception $e) {
            Log::critical('企业微信消息推送异常：'.var_export($e->getMessage(), true));

            return [-40007, null]; // DecryptAESError
        }
        try {
            //删除PKCS#7填充
            $result = (new PKCS7Encoder)->decode($decrypted);
            if (strlen($result) < 16) {
                return [];
            }
            //拆分
            $content = substr($result, 16, strlen($result));
            $len_list = unpack('N', substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_receiveId = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            // 发送错误
            Log::critical('企业微信消息推送异常：'.var_export($e->getMessage(), true));

            return [-40008, null]; // IllegalBuffer
        }
        if ($from_receiveId !== sysConfig('wechat_cid')) {
            return [-40005, null]; // ValidateCorpidError
        }

        return [0, $xml_content];
    }
}
