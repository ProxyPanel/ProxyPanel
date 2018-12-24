<?php

namespace App\Components;

/**
 * Class AlipayNotify
 *
 * @author  wz812180
 *
 * @package App\Components
 */
class AlipayNotify
{
    private $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&'; // HTTPS形式消息验证地址
    private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?'; // HTTP形式消息验证地址
    private $sign_type = "MD5"; // 加密方式：MD5/RSA
    private $partner = "";
    private $md5_key = "";
    private $private_key = "";
    private $alipay_public_key = "";
    private $transport = "http";

    function __construct($sign_type, $partner, $md5_key, $private_key, $alipay_public_key, $transport)
    {
        $this->sign_type = $sign_type;
        $this->partner = $partner;
        $this->md5_key = $md5_key;
        $this->private_key = $private_key;
        $this->alipay_public_key = $alipay_public_key;
        $this->transport = $transport;
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     *
     * @return bool 验证结果
     */
    public function verifyNotify()
    {
        if (empty($_POST)) {
            return false;
        } else {
            // 生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);

            $converted_res = ($isSign) ? 'true' : 'false';

            // 获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($_POST["notify_id"])) {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }

            // 验证
            // $responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            // isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     *
     * @param array  $para_temp 通知返回来的参数数组
     * @param string $sign      返回的签名结果
     *
     * @return bool 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign)
    {
        // 除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        // 对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        // 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkString($para_sort);

        switch (strtoupper(trim($this->sign_type))) {
            case "RSA" :
                $isSgin = $this->rsaVerify($prestr, trim($this->alipay_public_key), $sign);
                break;
            case "MD5" :
                $isSgin = $this->md5Verify($prestr, $sign, trim($this->md5_key));
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     *
     * @param integer $notify_id 通知校验ID
     *
     * @return string 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->transport));
        $partner = trim($this->partner);

        $verify_url = $transport == 'https' ? $this->https_verify_url : $this->http_verify_url;
        $verify_url = $verify_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($verify_url, base_path('ca/cacert_alipay.pem'));

        return $responseTxt;
    }

    /**
     * RSA验签
     *
     * @param string $data              待签名数据
     * @param string $alipay_public_key 支付宝的公钥字符串
     * @param string $sign              要校对的的签名结果
     *
     * @return bool
     */
    function rsaVerify($data, $alipay_public_key, $sign)
    {
        // 以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $alipay_public_key = str_replace("-----BEGIN PUBLIC KEY-----", "", $alipay_public_key);
        $alipay_public_key = str_replace("-----END PUBLIC KEY-----", "", $alipay_public_key);
        $alipay_public_key = str_replace("\n", "", $alipay_public_key);

        $alipay_public_key = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . wordwrap($alipay_public_key, 64, "\n", true) . PHP_EOL . '-----END PUBLIC KEY-----';
        $res = openssl_get_publickey($alipay_public_key);
        if (!$res) {
            \Log::error("支付宝公钥格式不正确");
            exit();
        }

        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);

        return $result;
    }

    /**
     * 验证签名
     *
     * @param string $prestr 需要签名的字符串pre-sign
     * @param string $sign   签名结果
     * @param string $key    私钥
     *
     * @return bool
     */
    function md5Verify($prestr, $sign, $key)
    {
        $mysgin = md5($prestr . $key);

        return $mysgin == $sign ? true : false;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param array $para 需要拼接的数组
     *
     * @return string
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
     * 远程获取数据，GET模式
     * 注意：文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     *
     * @param string $url        指定URL完整路径地址
     * @param string $cacert_url 指定当前工作目录绝对路径
     *
     * @return mixed
     */
    function getHttpResponseGET($url, $cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); // 证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param array $para 签名参数组
     *
     * @return array 去掉空值与签名参数后的新签名参数组
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
     * @return array 排序后的数组
     */
    function argSort($para)
    {
        ksort($para);
        reset($para);

        return $para;
    }
}

?>
