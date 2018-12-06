<?php
namespace App\Components;

class Trimepay {
    private $appId;
    private $appSecret;
    /**
	 * 签名初始化
	 * @param merKey	签名密钥
	 */
	public function __construct($appId, $appSecret) {
	    $this->appId = $appId;
        $this->appSecret = $appSecret;
        //国内
		// $this->gatewayUri = 'https://api.ecy.es/gateway/pay/go';
        // $this->refundUri = 'https://api.ecy.es/gateway/refund/go';
        //国外
		$this->gatewayUri = 'https://api.trimepay.com/gateway/pay/go';
        $this->refundUri = 'https://api.trimepay.com/gateway/refund/go';
	}
	
	/**
	 * @name	准备签名/验签字符串
	 */
	public function prepareSign($data) {
		ksort($data);
		return http_build_query($data);
	}
	/**
	 * @name	生成签名
	 * @param	sourceData
	 * @return	签名数据
	 */
	public function sign($data) {
		$signature = strtolower(md5(md5($data).$this->appSecret));
		return $signature;
	}
	/*
	 * @name	验证签名
	 * @param	signData 签名数据
	 * @param	sourceData 原数据
	 * @return
	 */
	public function verify($data, $signature) {
		$mySign = $this->sign($data);
		if ($mySign === $signature) {
			return true;
		} else {
			return false;
		}
	}
    
    public function post($data, $url = ''){
        if($url == '') {
            $url = $this->gatewayUri;
        }
        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($curl);
		curl_close($curl);
		return json_decode($data, true);
    }

    public function pay($type, $tradeNo, $totalFee, $notifyUrl = '', $returnUrl = ''){
        $payData = [
            'appId' => $this->appId,
            'merchantTradeNo' => $tradeNo,
            'totalFee' => $totalFee * 100,
            'notifyUrl' => $notifyUrl,
            'returnUrl' => $returnUrl,
            'payType' => $type
        ];
        $signData = $this->prepareSign($payData);
        $payData['sign'] = $this->sign($signData);
        $response = $this->post($payData);
        return $response;
    }
    
    public function refund($merchantTradeNo) {
        $params['merchantTradeNo'] = $merchantTradeNo;
        $params['appId'] = $this->appId;
        $prepareSign = $this->prepareSign($params);
        $params['sign'] = $this->sign($prepareSign);
        return $this->post($params, $this->refundUri);
    }
    public function buildHtml($params, $method = 'post', $target = '_self'){
        // var_dump($params);exit;
		$html = "<form id='submit' name='submit' action='".$this->gatewayUri."' method='$method' target='$target'>";
		foreach ($params as $key => $value) {
			$html .= "<input type='hidden' name='$key' value='$value'/>";
		}
		$html .= "</form><script>document.forms['submit'].submit();</script>";
		return $html;
    }
}