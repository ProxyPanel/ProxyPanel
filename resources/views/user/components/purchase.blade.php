@if (sysConfig('is_AliPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{ sysConfig('is_AliPay') }}','1')">
        <img src="/assets/images/payment/alipay.svg" alt="{{ trans('common.payment.alipay') }}" height="36px" />
    </button>
@endif
@if (sysConfig('is_QQPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{ sysConfig('is_QQPay') }}','2')">
        <img src="/assets/images/payment/qqpay.svg" alt="{{ trans('common.payment.alipay') }}" height="36px" />
    </button>
@endif
@if (sysConfig('is_WeChatPay'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('{{ sysConfig('is_WeChatPay') }}','3')">
        <img src="/assets/images/payment/wechatpay.svg" alt="{{ trans('common.payment.wechat') }}" height="36px" />
    </button>
@endif
@if (sysConfig('is_otherPay'))
    @if (str_contains(sysConfig('is_otherPay'), 'bitpayx'))
        <button class="btn btn-round btn-outline-default mt-2" onclick="pay('bitpayx','4')">
            <img src="/assets/images/payment/btc.svg" alt="{{ trans('common.payment.crypto') }}" height="36px" />
            <span class="font-size-24 black"> {{ trans('common.payment.crypto') }} </span>
        </button>
    @elseif(str_contains(sysConfig('is_otherPay'), 'cryptomus'))
        <button class="btn btn-round btn-outline-default mt-2" onclick="pay('cryptomus','4')">
            <img src="/assets/images/payment/btc.svg" alt="{{ trans('common.payment.crypto') }}" height="36px" />
            <span class="font-size-24 black"> {{ trans('common.payment.crypto') }} </span>
        </button>
    @endif
    @if (str_contains(sysConfig('is_otherPay'), 'paypal'))
        <button class="btn btn-round btn-outline-default mt-2" onclick="pay('paypal','5')">
            <img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-150px.png" alt="PayPal" loading="lazy" height="32px" />
        </button>
    @endif
    @if (str_contains(sysConfig('is_otherPay'), 'stripe'))
        <button class="btn btn-round btn-outline-default mt-2" onclick="pay('stripe','6')">
            <img src="/assets/images/payment/stripe.svg" alt="Stripe" height="40px" />
        </button>
    @endif
@endif
@if (sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
    <button class="btn btn-round btn-outline-default mt-2" onclick="pay('manual','7')">
        <img src="/assets/images/payment/pay.svg" alt="{{ trans('common.payment.manual') }}" height="40px" />
        <span class="font-size-18 font-weight-bold"> {{ trans('common.payment.manual') }} </span>
    </button>
@endif
