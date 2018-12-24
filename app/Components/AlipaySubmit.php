<?php

namespace App\Components;

use \DOMDocument;

class AlipaySubmit {

	/**
	 *支付宝网关地址（新）
	 */
	var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	
	var $sign_type = "MD5";
	var $partner = "";
	var $md5_key = "";
	var $private_key = "";
	
	/**
	 * 构造函数
	 * @param $sign_type 加密方式 MD5/RSA
	 * return 
	 */
	function __construct($sign_type, $partner, $md5_key, $private_key){
		$this->sign_type = $sign_type;	
		$this->partner = $partner;
		$this->md5_key = $md5_key;
		$this->private_key = $private_key;
	}
	
	
	/**
	 * 生成签名结果
	 * Generate the sign
	 * @param $para_sort 已排序要签名的数组Parameters to sign
	 * return 签名结果字符串sign generated
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    	//Rearrange parameters in the data set alphabetically and connect rearranged parameters with & like "parametername=value"
		$prestr = $this->createLinkstring($para_sort);
		
		$mysign = "";
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
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->sign_type));
		
		return $para_sort;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = $this->createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	public function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=utf-8' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		//Tools::Log($sHtml);
		return $sHtml;
	}
	
	
	/**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->partner))."&_input_charset=utf-8";
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
	
		/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	 function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	function createLinkstringUrlencode($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	
	/**
	 * RSA签名
	 * @param $data 待签名数据
	 * @param $private_key 商户私钥字符串
	 * return 签名结果
	 */
	function rsaSign($data, $private_key) {
		//以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
		$private_key=str_replace("-----BEGIN RSA PRIVATE KEY-----","",$private_key);
		$private_key=str_replace("-----END RSA PRIVATE KEY-----","",$private_key);
		$private_key=str_replace("\n","",$private_key);

		$private_key="-----BEGIN RSA PRIVATE KEY-----".PHP_EOL .wordwrap($private_key, 64, "\n", true). PHP_EOL."-----END RSA PRIVATE KEY-----";

		$res=openssl_get_privatekey($private_key);

		if($res)
		{
			openssl_sign($data, $sign,$res);
		}
		else {
			echo "您的私钥格式不正确!"."<br/>"."The format of your private_key is incorrect!";
			exit();
		}
		openssl_free_key($res);
		//base64编码
		$sign = base64_encode($sign);
		return $sign;
	}
	
	/**
	 * sign  签名字符串
	 * @param $prestr 需要签名的字符串
	 * @param $key 私钥
	 * return 签名结果 sign generated
	 */
	function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
	
	/**
	 * 除去数组中的空值和签名参数
	 * @param $para 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	
	/**
	 * 对数组排序
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
}