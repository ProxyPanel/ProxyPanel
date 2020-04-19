@if(\App\Components\Helpers::systemConfig()['is_AliPay'])
	<button class="btn btn-flat waves-attach" onclick="pay('{{\App\Components\Helpers::systemConfig()['is_AliPay']}}','1')"><img src="/assets/images/alipay.svg" width="50px" height="50px" alt="alipay"/></button>
@endif
@if(\App\Components\Helpers::systemConfig()['is_QQPay'])
	<button class="btn btn-flat waves-attach" onclick="pay('{{\App\Components\Helpers::systemConfig()['is_QQPay']}}','2')"><img src="/assets/images/qq.svg" width="50px" height="50px" alt="qq"/></button>
@endif
@if(\App\Components\Helpers::systemConfig()['is_WeChatPay'])
	<button class="btn btn-flat waves-attach" onclick="pay('{{\App\Components\Helpers::systemConfig()['is_WeChatPay']}}','3')"><img src="/assets/images/wechat.svg" width="50px" height="50px" alt="wechat"/></button>'
@endif