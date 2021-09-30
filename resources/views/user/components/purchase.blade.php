@if(sysConfig('is_AliPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{sysConfig('is_AliPay')}}','1')">
        <img src="/assets/images/payment/alipay.svg" height="36px" alt="alipay"/>
    </button>
@endif
@if(sysConfig('is_QQPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{sysConfig('is_QQPay')}}','2')">
        <img src="/assets/images/payment/qqpay.svg" height="36px" alt="qq"/>
    </button>
@endif
@if(sysConfig('is_WeChatPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{sysConfig('is_WeChatPay')}}','3')">
        <img src="/assets/images/payment/wechatpay.svg" height="36px" alt="wechat"/>
    </button>
@endif
@if(strpos(sysConfig('is_otherPay'), 'bitpayx') !== false)
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('bitpayx','4')">
        <img src="/assets/images/payment/btc.svg" height="36px" alt="other"/>
        <span class="font-size-24 black"> {{trans('common.payment.crypto')}} </span>
    </button>
@endif
@if(strpos(sysConfig('is_otherPay'), 'paypal') !== false)
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('paypal','5')">
        <img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-150px.png" height="32px" alt="PayPal"/>
    </button>
@endif
@if(strpos(sysConfig('is_otherPay'), 'stripe') !== false)
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('stripe','6')">
        <img src="/assets/images/payment/stripe.svg" height="40px" alt="stripe"/>
    </button>
@endif
@if(sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('manual','7')">
        <img src="/assets/images/payment/pay.svg" height="40px" alt="pay"/> <span class="font-size-18 font-weight-bold"> 人工支付 </span>
    </button>
@endif
