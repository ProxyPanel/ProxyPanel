<?php

namespace App\Components;

use \DOMDocument;

/**
 * Class AlipaySubmit
 *
 * @author  wz812180
 *
 * @package App\Components
 */
class AlipaySubmit
{
    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?'; // 支付宝网关地址（新）
    var $sign_type = "MD5"; // 加密方式：MD5/RSA
    var $partner = "";
    var $md5_key = "";
    var $private_key = "";

    function __construct($sign_type, $partner, $md5_key, $private_key)
    {
        $this->sign_type = $sign_type;
        $this->partner = $partner;
        $this->md5_key = $md5_key;
        $this->private_key = $private_key;
    }

    /**
     * 生成签名结果
     *
     * @param array $para_sort 已排序要签名的数组
     *
     * @return string
     */
    function buildRequestMysign($para_sort)
    {
        // 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkString($para_sort);

        switch (strtoupper(trim($this->sign_type))) {
            case "MD5" :
                $mysign = $this->md5Sign($prestr, $this->md5_key);
                break;
            case "RSA" :
                $mysign = $this->rsaSign($prestr, $this->private_key);
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * 生成要请求给支付宝的参数数组
     *
     * @param array $para_temp 请求前的参数数组
     *
     * @return array
     */
    function buildRequestPara($para_temp)
    {
        // 除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        // 对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        // 生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        // 签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->sign_type));

        return $para_sort;
    }

    /**
     * 生成要请求给支付宝的参数数组
     *
     * @param array $para_temp 请求前的参数数组
     *
     * @return string
     */
    function buildRequestParaToString($para_temp)
    {
        // 待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        // 把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = $this->createLinkStringUrlEncode($para);

        return $request_data;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     *
     * @param array  $para_temp   请求参数数组
     * @param string $method      提交方式。两个值可选：post、get
     * @param string $button_name 确认按钮显示文字
     *
     * @return string
     */
    public function buildRequestForm($para_temp, $method, $button_name)
    {
        // 待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=utf-8' method='" . $method . "'>";
        while (list ($key, $val) = each($para)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        // submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit'  value='" . $button_name . "' style='display:none;'></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     *
     * @return string
     */
    function query_timestamp()
    {
        $url = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($this->partner)) . "&_input_charset=utf-8";

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param array $para
     *
     * @return bool|string
     */
    function createLinkString($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }

        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     *
     * @param array $para 需要拼接的数组
     *
     * @return bool|string
     */
    function createLinkStringUrlEncode($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }

        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * RSA签名
     *
     * @param string $data        待签名数据
     * @param string $private_key 商户私钥字符串
     *
     * @return string
     */
    function rsaSign($data, $private_key)
    {
        //以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $private_key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("-----END RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("\n", "", $private_key);
        $private_key = "-----BEGIN RSA PRIVATE KEY-----" . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . "-----END RSA PRIVATE KEY-----";

        $res = openssl_get_privatekey($private_key);
        if (!$res) {
            \Log::error("私钥格式不正确");
            exit();
        }

        openssl_sign($data, $sign, $res);
        openssl_free_key($res);

        $sign = base64_encode($sign); // base64编码

        return $sign;
    }

    /**
     * 签名字符串
     *
     * @param string $prestr 需要签名的字符串
     * @param string $key    私钥
     *
     * @return string
     */
    function md5Sign($prestr, $key)
    {
        return md5($prestr . $key);
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param array $para 签名参数组
     *
     * @return array
     */
    function paraFilter($para)
    {
        $para_filter = [];
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }

        return $para_filter;
    }

    /**
     * 对数组排序
     *
     * @param array $para 排序前的数组
     *
     * @return mixed
     */
    function argSort($para)
    {
        ksort($para);
        reset($para);

        return $para;
    }
}