@if(sysConfig('is_AliPay'))
    <button class="btn btn-flat" onclick="pay('{{sysConfig('is_AliPay')}}','1')">
        <img src="/assets/images/payment/alipay.svg" height="36px" alt="alipay"/>
    </button>
@endif
@if(sysConfig('is_QQPay'))
    <button class="btn btn-flat" onclick="pay('{{sysConfig('is_QQPay')}}','2')">
        <img src="/assets/images/payment/qqpay.svg" height="36px" alt="qq"/>
    </button>
@endif
@if(sysConfig('is_WeChatPay'))
    <button class="btn btn-flat" onclick="pay('{{sysConfig('is_WeChatPay')}}','3')">
        <img src="/assets/images/payment/wechatpay.svg" height="36px" alt="wechat"/>
    </button>'
@endif
@if(sysConfig('is_otherPay') == 'bitpayx')
    <button class="btn btn-flat" onclick="pay('{{sysConfig('is_otherPay')}}','4')">
        <img src="/assets/images/payment/btc.svg" height="36px" alt="other"/>
        <span class="font-size-24 black"> 虚拟货币</span>
    </button>'
@elseif(sysConfig('is_otherPay') == 'paypal')
    <button class="btn btn-flat" onclick="pay('{{sysConfig('is_otherPay')}}','5')">
        <img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-150px.png" height="32px" alt="PayPal"/>
    </button>'
@endif
