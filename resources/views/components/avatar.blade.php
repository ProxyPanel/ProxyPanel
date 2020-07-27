@if($user->qq)
	<img src="https://q1.qlogo.cn/g?b=qq&nk={{$user->qq}}&s=640" alt="头像">
@elseif(strpos(strtolower($user->email),"@qq.com") !== false)
	<img src="https://q1.qlogo.cn/g?b=qq&nk={{$user->email}}&s=640" alt="头像">
@else
	<img src="/assets/images/avatar.svg" alt="头像">
@endif