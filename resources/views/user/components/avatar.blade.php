@if(Auth::user()->qq)
	<img src="http://q1.qlogo.cn/g?b=qq&nk={{Auth::user()->qq}}&s=640" alt="{{trans('home.ticket_reply_me')}}">
@elseif(strpos(strtolower(Auth::user()->email),"@qq.com") !== FALSE)
	<img src="http://q1.qlogo.cn/g?b=qq&nk={{Auth::user()->email}}&s=640" alt="{{trans('home.ticket_reply_me')}}">
@else
	<img src="/assets/images/avatar.svg" alt="{{trans('home.ticket_reply_me')}}">
@endif