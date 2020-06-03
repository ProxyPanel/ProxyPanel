@if(Auth::getUser()->qq)
	<img src="http://q1.qlogo.cn/g?b=qq&nk={{Auth::getUser()->qq}}&s=640" alt="{{trans('home.ticket_reply_me')}}">
@elseif(strpos(strtolower(Auth::getUser()->email),"@qq.com") !== FALSE)
	<img src="http://q1.qlogo.cn/g?b=qq&nk={{Auth::getUser()->email}}&s=640" alt="{{trans('home.ticket_reply_me')}}">
@else
	<img src="/assets/images/avatar.svg" alt="{{trans('home.ticket_reply_me')}}">
@endif
