@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
	<link href="//cdn.bootcss.com/bootstrap-switch/4.0.0-alpha.1/css/bootstrap-switch.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/dropify/dropify.min.css" type="text/css" rel="stylesheet">
	<style>
		.text-help {
			padding-left: 15px;
		}
	</style>
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title"><i class="icon wb-settings"></i>通用配置</h1>
			</div>
			<div class="panel-body">
				<div class="nav-tabs-horizontal" data-plugin="tabs">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item" role="presentation">
							<a class="nav-link active" data-toggle="tab" href="#webSetting" aria-controls="webSetting" role="tab">网站常规</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#account" aria-controls="account" role="tab">账号设置</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#node" aria-controls="node" role="tab">节点设置</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">拓展功能</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">签到系统</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">推广系统</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">通知系统</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">自动任务</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#other" aria-controls="other" role="tab">LOGO|客服|统计</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">支付系统</a>
						</li>
						<li class="dropdown nav-item" role="presentation">
							<a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false">菜单</a>
							<div class="dropdown-menu" role="menu">
								<a class="dropdown-item active" data-toggle="tab" href="#webSetting" aria-controls="webSetting" role="tab">网站常规</a>
								<a class="dropdown-item" data-toggle="tab" href="#account" aria-controls="account" role="tab">账号设置</a>
								<a class="dropdown-item" data-toggle="tab" href="#node" aria-controls="node" role="tab">节点设置</a>
								<a class="dropdown-item" data-toggle="tab" href="#extend" aria-controls="extend" role="tab">拓展功能</a>
								<a class="dropdown-item" data-toggle="tab" href="#checkIn" aria-controls="checkIn" role="tab">签到系统</a>
								<a class="dropdown-item" data-toggle="tab" href="#promo" aria-controls="promo" role="tab">推广系统</a>
								<a class="dropdown-item" data-toggle="tab" href="#notify" aria-controls="notify" role="tab">通知系统</a>
								<a class="dropdown-item" data-toggle="tab" href="#auto" aria-controls="auto" role="tab">自动任务</a>
								<a class="dropdown-item" data-toggle="tab" href="#other" aria-controls="other" role="tab">LOGO|客服|统计</a>
								<a class="dropdown-item" data-toggle="tab" href="#payment" aria-controls="payment" role="tab">支付系统</a>
							</div>
						</li>
					</ul>
					<div class="tab-content py-35 px-35">
						<div class="tab-pane active" id="webSetting" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal" autocomplete="off">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3" for="website_name">网站名称</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="text" class="form-control" name="website_name" id="website_name" value="{{$website_name}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setWebsiteName()">修改</button></span>
												</div>
											</div>
											<span class="offset-md-3 text-help"> 发邮件时展示 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="website_url">网站地址</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="url" class="form-control" name="website_url" id="website_url" value="{{$website_url}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setWebsiteUrl()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 生成重置密码、在线支付必备 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="AppStore_id">苹果账号</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="email" class="form-control" name="AppStore_id" id="AppStore_id" value="{{$AppStore_id}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAppStoreId()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> iOS软件设置教程中使用的苹果账号 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="AppStore_password">苹果密码</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="password" class="form-control" name="AppStore_password" id="AppStore_password" value="{{$AppStore_password}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAppStorePassword()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> iOS软件设置教程中使用的苹果密码 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="admin_email">管理员邮箱</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="email" class="form-control" name="admin_email" id="admin_email" value="{{$admin_email}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAdminEmail()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 错误提示时会提供管理员邮箱作为联系方式 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="website_security_code">网站安全码</label>
											<div class="col-md-6">
												<div class="input-group">
													<input type="text" class="form-control" name="website_security_code" id="website_security_code" value="{{$website_security_code}}"/>
													<span class="input-group-append">
														<button class="btn btn-info" type="button" onclick="makeWebsiteSecurityCode()">生成</button>
														<button class="btn btn-primary" type="button" onclick="setWebsiteSecurityCode()">修改</button>
													</span>
												</div>
											</div>
											<span class="text-help offset-md-3">非空时必须通过 <a href="/login?securityCode=" target="_blank">安全入口</a> 加上安全码才可访问 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_forbid_china">阻止大陆访问</label>
											<span class="col-md-9"><input type="checkbox" id="is_forbid_china" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_forbid_china) checked @endif></span>
											<span class="text-help offset-md-3"> 开启后大陆IP禁止访问 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_forbid_oversea">阻止海外访问</label>
											<span class="col-md-9"><input type="checkbox" id="is_forbid_oversea" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_forbid_oversea) checked @endif></span>
											<span class="text-help offset-md-3"> 开启后海外IP(含港澳台)禁止访问 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_forbid_robot">阻止机器人访问</label>
											<span class="col-md-9"><input type="checkbox" id="is_forbid_robot" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_forbid_robot) checked @endif></span>
											<span class="text-help offset-md-3"> 如果是机器人、爬虫、代理访问网站则会抛出404错误 </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="account" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_register">用户注册</label>
											<span class="col-md-9"><input type="checkbox" id="is_register" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_register) checked @endif></span>
											<span class="text-help offset-md-3"> 关闭后无法注册 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_invite_register">邀请注册</label>
											<select class="col-md-3" name="is_invite_register" id="is_invite_register" data-plugin="selectpicker" data-style="btn-outline btn-primary">
												<option value="0" @if($is_invite_register == '0') selected @endif>关闭</option>
												<option value="1" @if($is_invite_register == '1') selected @endif>可选</option>
												<option value="2" @if($is_invite_register == '2') selected @endif>必须</option>
											</select>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_active_register">激活账号</label>
											<span class="col-md-9"><input type="checkbox" id="is_active_register" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_active_register) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后用户需要通过邮件来激活账号 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_reset_password">重置密码</label>
											<span class="col-md-9"><input type="checkbox" id="is_reset_password" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_reset_password) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后用户可以通过邮件重置密码 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_captcha">验证码</label>
											<select class="col-md-5" name="is_captcha" id="is_captcha" data-plugin="selectpicker" data-style="btn-outline btn-primary">
												<option value="0" @if($is_captcha == '0') selected @endif>关闭</option>
												<option value="1" @if($is_captcha == '1') selected @endif>普通验证码</option>
												<option value="2" @if($is_captcha == '2') selected @endif>极验Geetest</option>
												<option value="3" @if($is_captcha == '3') selected @endif>Google reCAPTCHA</option>
											</select>
											<span class="text-help offset-md-3"> 启用后登录、注册需要输入验证码 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_free_code">免费邀请码</label>
											<span class="col-md-9"><input type="checkbox" id="is_free_code" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_free_code) checked @endif></span>
											<span class="text-help offset-md-3"> 关闭后免费邀请码不可见 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_verify_register">注册校验验证码</label>
											<span class="col-md-9"><input type="checkbox" id="is_verify_register" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_verify_register) checked @endif></span>
											<span class="text-help offset-md-3"> 注册时需要先通过邮件获取验证码方可注册，‘激活账号’失效 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label for="is_rand_port" class="col-md-3 col-form-label">随机端口</label>
											<span class="col-md-9"><input type="checkbox" id="is_rand_port" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_rand_port) checked @endif></span>
											<span class="text-help offset-md-3"> 注册、添加用户时随机生成端口 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label">端口范围</label>
											<div class="col-md-7">
												<div class="input-group">
													<label for="min_port"></label>
													<input type="number" class="form-control" name="min_port" id="min_port" value="{{$min_port}}"/>
													<div class="input-group-prepend">
														<span class="input-group-text"> ~ </span>
													</div>
													<label for="max_port"></label>
													<input type="number" class="form-control" name="max_port" id="max_port" value="{{$max_port}}"/>
												</div>
											</div>
											<span class="text-help offset-md-3"> 端口范围：1000 - 65535 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_user_rand_port">自定义端口</label>
											<span class="col-md-9"><input type="checkbox" id="is_user_rand_port" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_user_rand_port) checked @endif></span>
											<span class="text-help offset-md-3"> 用户可以自定义端口 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="default_days">初始有效期</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="default_days" id="default_days" value="{{$default_days}}"/>
													<div class="input-group-append">
													</div>
													<span class="input-group-text">天</span>
													<button class="btn btn-primary" type="button" onclick="setDefaultDays()">修改</button>
												</div>
											</div>
											<span class="text-help offset-md-3"> 用户注册时默认账户有效期，为0即当天到期 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="default_traffic">初始流量</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="default_traffic" id="default_traffic" value="{{$default_traffic}}"/>
													<div class="input-group-append">
													</div>
													<span class="input-group-text">MB</span>
													<button class="btn btn-primary" type="button" onclick="setDefaultTraffic()">修改</button>
												</div>
											</div>
											<span class="text-help offset-md-3"> 用户注册时默认可用流量 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="invite_num">可生成邀请码数</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="invite_num" id="invite_num" value="{{$invite_num}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setInviteNum()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 用户可以生成的邀请码数 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="reset_password_times">重置密码次数</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="reset_password_times" id="reset_password_times" value="{{$reset_password_times}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setResetPasswordTimes()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 24小时内可以通过邮件重置密码次数 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="sensitiveType">邮箱过滤机制</label>
											<span class="col-md-9"><input type="checkbox" id="sensitiveType" data-on-color="primary" data-off-color="danger" data-on-text="黑名单" data-off-text="白名单" data-base-class="bootstrap-switch" @if($sensitiveType) checked @endif></span>
											<span class="text-help offset-md-3"> 黑名单时，用户可使用任意黑名单外的邮箱注册；白名单时用户只能选择使用白名单中的邮箱后缀注册 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="active_times">激活账号次数</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="active_times" id="active_times" value="{{$active_times}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setActiveTimes()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 24小时内可以通过邮件激活账号次数 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="register_ip_limit">同IP注册限制</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="register_ip_limit" id="register_ip_limit" value="{{$register_ip_limit}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setRegisterIpLimit()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 同IP在24小时内允许注册数量，为0时不限制 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="initial_labels_for_user">用户初始标签</label>
											<select class="col-md-7 show-tick" name="initial_labels_for_user" id="initial_labels_for_user" data-plugin="selectpicker" data-style="btn-outline btn-primary" multiple>
												@foreach($label_list as $label)
													<option value="{{$label->id}}" @if(in_array($label->id, explode(',', $initial_labels_for_user))) selected @endif>{{$label->name}}</option>
												@endforeach
											</select>
											<span class="text-help offset-md-3"> 注册用户时的初始标签，标签用于关联节点 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="user_invite_days">用户-邀请码有效期</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="user_invite_days" id="user_invite_days" value="{{$user_invite_days}}"/>
													<div class="input-group-append">
													</div>
													<span class="input-group-text">天</span>
													<button class="btn btn-primary" type="button" onclick="setUserInviteDays()">修改</button>
												</div>
											</div>
											<span class="text-help offset-md-3"> 用户自行生成邀请的有效期 </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="node" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="subscribe_domain">节点订阅地址</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="url" class="form-control" name="subscribe_domain" id="subscribe_domain" value="{{$subscribe_domain}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setSubscribeDomain()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> （推荐）防止面板域名被DNS投毒后无法正常订阅，需带http://或https:// </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="subscribe_max">订阅节点数</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="subscribe_max" id="subscribe_max" value="{{$subscribe_max}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setSubscribeMax()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 客户端订阅时取得几个节点，为0时返回全部节点 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="mix_subscribe">混合订阅</label>
											<span class="col-md-9"><input type="checkbox" id="mix_subscribe" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($mix_subscribe) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后，订阅信息中将包含V2Ray节点信息（仅支持Shadowrocket、Quantumult、v2rayN） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="rand_subscribe">随机订阅</label>
											<span class="col-md-9"><input type="checkbox" id="rand_subscribe" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($rand_subscribe) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后，订阅时将随机返回节点信息，否则按节点排序返回 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_custom_subscribe">高级订阅</label>
											<span class="col-md-9"><input type="checkbox" id="is_custom_subscribe" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_custom_subscribe) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后，订阅信息顶部将显示过期时间、剩余流量（Quantumult有特殊效果） </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="extend" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_namesilo">Namesilo</label>
											<span class="col-md-9"><input type="checkbox" id="is_namesilo" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_namesilo) checked @endif></span>
											<span class="text-help offset-md-3"> 添加、编辑节点的绑定域名时自动更新域名DNS记录值为节点IP（<a href="https://www.namesilo.com/account_api.php?rid=326ec20pa" target="_blank">创建API KEY</a>） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="namesilo_key">Namesilo API KEY</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="namesilo_key" id="namesilo_key" value="{{$namesilo_key}}" placeholder="填入Namesilo上申请的API KEY"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setNamesiloKey()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 域名必须是<a href="https://www.namesilo.com/?rid=326ec20pa" target="_blank">www.namesilo.com</a>上购买的 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="admin_invite_days">管理员-邀请码有效期</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="admin_invite_days" id="admin_invite_days" value="{{$admin_invite_days}}"/>
													<div class="input-group-append">
													</div>
													<span class="input-group-text">天</span>
													<button class="btn btn-primary" type="button" onclick="setAdminInviteDays()">修改</button>
												</div>
											</div>
											<span class="text-help offset-md-3"> 管理员生成邀请码的有效期 </span>
										</div>
									</div>
									@if($is_captcha == 2)
										<div class="form-group col-lg-6">
											<div class="row">
												<label class="col-md-3 col-form-label" for="geetest_id">极验ID</label>
												<div class="col-md-7">
													<div class="input-group">
														<input type="text" class="form-control" name="geetest_id" id="geetest_id" value="{{$geetest_id}}"/>
														<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setGeetestId()">修改</button></span>
													</div>
												</div>
												<span class="text-help offset-md-3"> 本功能需要 <a href="https://auth.geetest.com/login/" target="_blank">极验后台</a> 申请权限及应用 </span>
											</div>
										</div>
										<div class="form-group col-lg-6">
											<div class="row">
												<label class="col-md-3 col-form-label" for="geetest_key">极验KEY</label>
												<div class="col-md-7">
													<div class="input-group">
														<input type="text" class="form-control" name="geetest_key" id="geetest_key" value="{{$geetest_key}}"/>
														<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setGeetestKey()">修改</button></span>
													</div>
												</div>
											</div>
										</div>
									@elseif($is_captcha == 3)
										<div class="form-group col-lg-6">
											<div class="row">
												<label class="col-md-3 col-form-label" for="google_captcha_sitekey">网站密钥</label>
												<div class="col-md-7">
													<div class="input-group">
														<input type="text" class="form-control" name="google_captcha_sitekey" id="google_captcha_sitekey" value="{{$google_captcha_sitekey}}"/>
														<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setGoogleCaptchaId()">修改</button></span>
													</div>
												</div>
												<span class="text-help offset-md-3"> 本功能需要 <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA后台</a> 申请权限及应用 （申请需科学上网，日常验证不用）</span>
											</div>
										</div>
										<div class="form-group col-lg-6">
											<div class="row">
												<label class="col-md-3 control-label" for="google_captcha_secret">密钥</label>
												<div class="col-md-7">
													<div class="input-group">
														<input type="text" class="form-control" name="google_captcha_secret" id="google_captcha_secret" value="{{$google_captcha_secret}}"/>
														<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setGoogleCaptchaKey()">修改</button></span>
													</div>
												</div>
											</div>
										</div>
									@endif
								</div>
							</form>
						</div>
						<div class="tab-pane" id="checkIn" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_checkin">签到加流量</label>
											<span class="col-md-9"><input type="checkbox" id="is_checkin" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_checkin) checked @endif></span>
											<span class="text-help offset-md-3"> 登录时将根据流量范围随机得到流量 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="traffic_limit_time">时间间隔</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="traffic_limit_time" id="traffic_limit_time" value="{{$traffic_limit_time}}"/>
													<div class="input-group-append">
													</div>
													<span class="input-group-text">分钟</span>
													<button class="btn btn-primary" type="button" onclick="setTrafficLimitTime()">修改</button>
												</div>
											</div>
											<span class="text-help offset-md-3"> 间隔多久才可以再次签到</span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label">流量范围</label>
											<div class="col-md-7">
												<div class="input-group">
													<label for="min_rand_traffic"></label>
													<input type="number" class="form-control" name="min_rand_traffic" id="min_rand_traffic" value="{{$min_rand_traffic}}"/>
													<div class="input-group-prepend">
														<span class="input-group-text"> ~ </span>
													</div>
													<label for="max_rand_traffic"></label>
													<input type="number" class="form-control" name="max_rand_traffic" id="max_rand_traffic" value="{{$max_rand_traffic}}"/>
													<div class="input-group-prepend">
														<span class="input-group-text"> MB </span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="promo" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="referral_status">推广功能</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="checkbox" id="referral_status" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($referral_status) checked @endif>
												</div>
											</div>
											<span class="text-help offset-md-3"> 关闭后用户不可见，但是不影响其正常邀请返利 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="referral_traffic">注册送流量</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="referral_gift_traffic" id="referral_traffic" value="{{$referral_traffic}}"/>
													<div class="input-group-append">
														<span class="input-group-text">MB</span>
														<button class="btn btn-primary" type="button" onclick="setReferralTraffic()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 根据推广链接、邀请码注册则赠送相应的流量 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="referral_percent">返利比例</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="referral_percent" id="referral_percent" value="{{$referral_percent * 100}}"/>
													<div class="input-group-append">
														<span class="input-group-text">%</span>
														<button class="btn btn-primary" type="button" onclick="setReferralPercent()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 根据推广链接注册的账号每笔消费推广人可以分成的比例 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="referral_money">提现限制</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="referral_money" id="referral_money" value="{{$referral_money}}"/>
													<div class="input-group-append">
														<span class="input-group-text">元</span>
														<button class="btn btn-primary" type="button" onclick="setReferralMoney()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 满多少元才可以申请提现 </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="notify" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="expire_warning">用户过期警告</label>
											<span class="col-md-9"><input type="checkbox" id="expire_warning" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($expire_warning) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后账号距到期还剩阈值设置的值时自动发邮件提醒用户 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="expire_days">过期警告阈值</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="expire_days" id="expire_days" value="{{$expire_days}}"/>
													<div class="input-group-append">
														<span class="input-group-text">天</span>
														<button class="btn btn-primary" type="button" onclick="setExpireDays()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 账号距离过期还差多少天时发警告邮件 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="traffic_warning">用户流量警告</label>
											<span class="col-md-9"><input type="checkbox" id="traffic_warning" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($traffic_warning) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后账号已使用流量超过警告阈值时自动发邮件提醒用户 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label for="traffic_warning_percent" class="col-md-3 col-form-label">流量警告阈值</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="traffic_warning_percent" id="traffic_warning_percent" value="{{$traffic_warning_percent}}"/>
													<div class="input-group-append">
														<span class="input-group-text">%</span>
														<button class="btn btn-primary" type="button" onclick="setTrafficWarningPercent()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 建议设置在70%~90% </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_node_crash_warning">节点离线提醒</label>
											<span class="col-md-9"><input type="checkbox" id="is_node_crash_warning" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_node_crash_warning) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后如果节点离线则通过ServerChan推送提醒 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="webmaster_email">管理员收信地址</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="email" class="form-control" name="webmaster_email" id="webmaster_email" value="{{$webmaster_email}}" placeholder="master@ssrpanel.com"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setCrashWarningEmail()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 填写此值则节点离线、用户回复工单都会自动提醒 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="nodes_detection">节点阻断检测</label>
											<span class="col-md-9"><input type="checkbox" id="nodes_detection" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($nodes_detection) checked @endif></span>
											<span class="text-help offset-md-3"> 每小时检测节点是否被阻断并提醒 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="numberOfWarningTimes">阻断检测提醒</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="numberOfWarningTimes" id="numberOfWarningTimes" value="{{$numberOfWarningTimes}}"/>
													<div class="input-group-append">
														<span class="input-group-text">次</span>
														<button class="btn btn-primary" type="button" onclick="setNumberOfWarningTimes()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 提醒N次后自动下线节点，为0时不限制，不超过12 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_server_chan">ServerChan</label>
											<span class="col-md-9"><input type="checkbox" id="is_server_chan" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_server_chan) checked @endif></span>
											<span class="text-help offset-md-3"> 推送节点离线提醒、用户流量异常警告、节点使用报告（<a href="http://sc.ftqq.com" target="_blank">绑定微信</a>） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="server_chan_key">SCKEY</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="server_chan_key" id="server_chan_key" value="{{$server_chan_key}}" placeholder="请到ServerChan申请"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setServerChanKey()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 启用ServerChan，请务必填入本值（<a href="http://sc.ftqq.com" target="_blank">申请SCKEY</a>） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_push_bear">PushBear</label>
											<span class="col-md-9"><input type="checkbox" id="is_push_bear" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_push_bear) checked @endif></span>
											<span class="text-help offset-md-3"> 使用PushBear推送微信消息给用户（<a href="https://pushbear.ftqq.com/admin/#/signin" target="_blank">创建消息通道</a>） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="push_bear_send_key">PushBear SendKey</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="push_bear_send_key" id="push_bear_send_key" value="{{$push_bear_send_key}}" placeholder="创建消息通道后即可获取"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setPushBearSendKey()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 启用PushBear，请务必填入本值 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="push_bear_qrcode">PushBear订阅二维码</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="url" class="form-control" name="push_bear_qrcode" id="push_bear_qrcode" value="{{$push_bear_qrcode}}" placeholder="填入消息通道的二维码URL"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setPushBearQrCode()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 创建消息通道后，在二维码上点击右键“复制图片地址”并粘贴至此处 </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="auto" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_clear_log">自动清除日志</label>
											<span class="col-md-9"><input type="checkbox" id="is_clear_log" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_clear_log) checked @endif></span>
											<span class="text-help offset-md-3"> （推荐）启用后自动清除无用日志 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="reset_traffic">流量自动重置</label>
											<span class="col-md-9"><input type="checkbox" id="reset_traffic" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($reset_traffic) checked @endif></span>
											<span class="text-help offset-md-3"> 用户会按其购买套餐的日期自动重置可用流量 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_subscribe_ban">订阅异常自动封禁</label>
											<span class="col-md-9"><input type="checkbox" id="is_subscribe_ban" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_subscribe_ban) checked @endif></span>
											<span class="text-help offset-md-3"> 启用后用户订阅链接请求超过设定阈值则自动封禁 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="subscribe_ban_times">订阅请求阈值</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="subscribe_ban_times" id="subscribe_ban_times" value="{{$subscribe_ban_times}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setSubscribeBanTimes()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 24小时内订阅链接请求次数限制 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_traffic_ban">异常自动封号</label>
											<span class="col-md-9"><input type="checkbox" id="is_traffic_ban" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_traffic_ban) checked @endif/></span>
											<span class="text-help offset-md-3"> 1小时内流量超过异常阈值则自动封号（仅禁用代理） </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="traffic_ban_value">流量异常阈值</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="traffic_ban_value" id="traffic_ban_value" value="{{$traffic_ban_value}}"/>
													<div class="input-group-append">
														<span class="input-group-text">GB</span>
														<button class="btn btn-primary" type="button" onclick="setTrafficBanValue()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 1小时内超过该值，则触发自动封号 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="traffic_ban_time">封号时长</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="number" class="form-control" name="traffic_ban_time" id="traffic_ban_time" value="{{$traffic_ban_time}}"/>
													<div class="input-group-append">
														<span class="input-group-text">分钟</span>
														<button class="btn btn-primary" type="button" onclick="setTrafficBanTime()">修改</button>
													</div>
												</div>
											</div>
											<span class="text-help offset-md-3"> 触发流量异常导致用户被封禁的时长，到期后自动解封 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="auto_release_port">端口自动释放</label>
											<span class="col-md-9"><input type="checkbox" data-on-color="primary" id="auto_release_port" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($auto_release_port) checked @endif></span>
											<span class="text-help offset-md-3"> 被封禁和过期一个月的用户端口自动释放 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_ban_status">过期自动封禁</label>
											<span class="col-md-9"><input type="checkbox" id="is_ban_status" data-on-color="primary" data-off-color="danger" data-on-text="封禁整个账号" data-off-text="仅封禁代理" data-base-class="bootstrap-switch" @if($is_ban_status) checked @endif></span>
											<span class="text-help offset-md-3"> (慎重)封禁整个账号会重置账号的所有数据且会导致用户无法登录 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="node_daily_report">节点使用报告</label>
											<span class="col-md-9"><input type="checkbox" id="node_daily_report" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($node_daily_report) checked @endif></span>
											<span class="text-help offset-md-3"> 每天早上9点推送昨天节点的使用情况 </span>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="other" role="tabpanel">
							<form action="/admin/setExtend" method="post" enctype="multipart/form-data" class="upload-form" role="form" id="setExtend">
								{{csrf_field()}}
								<div class="form-row">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-form-label col-md-3" for="website_home_logo">首页LOGO</label>
											<div class="col-md-9">
												<input type="file" name="website_home_logo" id="website_home_logo" data-plugin="dropify" data-default-file={{$website_home_logo?:'/assets/images/noimage.png'}} />
												<button type="submit" class="btn btn-success float-right mt-10"> 提 交</button>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-form-label col-md-3" for="website_logo">站内LOGO</label>
											<div class="col-md-9">
												<input type="file" name="website_logo" id="website_logo" data-plugin="dropify" data-default-file={{$website_logo?:'/assets/images/noimage.png'}} />
												<button type="submit" class="btn btn-success float-right mt-10"> 提 交</button>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-form-label col-md-3" for="website_analytics">统计代码</label>
											<div class="col-md-9">
												<textarea class="form-control" rows="10" name="website_analytics" id="website_analytics">{{$website_analytics}}</textarea>
												<button type="submit" class="btn btn-success float-right mt-10"> 提 交</button>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-form-label col-md-3" for="website_customer_service">客服代码</label>
											<div class="col-md-9">
												<textarea class="form-control" rows="10" name="website_customer_service" id="website_customer_service">{{$website_customer_service}}</textarea>
												<button type="submit" class="btn btn-success float-right mt-10"> 提 交</button>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-pane" id="payment" role="tabpanel">
							<form action="#" method="post" role="form" class="form-horizontal">
								<div class="row pb-70">
									<div class="form-group col-lg-6">
										<div class="row">
											<label for="is_alipay" class="col-md-3 col-form-label">AliPay国际</label>
											<span class="col-md-9"><input type="checkbox" id="is_alipay" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_alipay) checked @endif></span>
											<span class="text-help offset-md-3"> 请先到 <a href="https://global.alipay.com/" target="_blank">AliPay国际</a> 申请partner和key </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_currency">结算币种</label>
											<select class="col-md-5" name="alipay_currency" id="alipay_currency" data-plugin="selectpicker" data-style="btn-outline btn-primary">
												<option value="USD" @if($alipay_currency == 'USD') selected @endif>美元</option>
												<option value="HKD" @if($alipay_currency == 'HKD') selected @endif>港币</option>
												<option value="JPY" @if($alipay_currency == 'JPY') selected @endif>日元</option>
												<option value="EUR" @if($alipay_currency == 'EUR') selected @endif>欧元</option>
											</select>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_sign_type">加密方式</label>
											<select class="col-md-5" name="alipay_sign_type" id="alipay_sign_type" data-plugin="selectpicker" data-style="btn-outline btn-primary">
												<option value="MD5" @if($alipay_sign_type == 'MD5') selected @endif>MD5</option>
												<option value="RSA" @if($alipay_sign_type == 'RSA') selected @endif>RSA</option>
											</select>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_transport">启用SSL验证</label>
											<select class="col-md-5" name="alipay_transport" id="alipay_transport" data-plugin="selectpicker" data-style="btn-outline btn-primary">
												<option value="http" @if($alipay_transport == 'http') selected @endif>否</option>
												<option value="https" @if($alipay_transport == 'https') selected @endif>是</option>
											</select>
											<span class="text-help offset-md-3"> HTTPS站点需启用 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_partner">Partner</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="alipay_partner" id="alipay_partner" value="{{$alipay_partner}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAlipayPartner()">修改</button></span>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label for="alipay_key" class="col-md-3 col-form-label">Key</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="password" class="form-control" name="alipay_key" id="alipay_key" value="{{$alipay_key}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAlipayKey()">修改</button></span>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_private_key">RSA私钥</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="password" class="form-control" name="alipay_private_key" id="alipay_private_key" value="{{$alipay_private_key}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAlipayPrivateKey()">修改</button></span>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="alipay_public_key">RSA公钥</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="password" class="form-control" name="alipay_public_key" id="alipay_public_key" value="{{$alipay_public_key}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setAlipayPublicKey()">修改</button></span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row pb-70">
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="is_f2fpay">支付宝F2F</label>
											<span class="col-md-9"><input type="checkbox" id="is_f2fpay" data-on-color="primary" data-off-color="danger" data-on-text="启用" data-off-text="关闭" data-base-class="bootstrap-switch" @if($is_f2fpay) checked @endif></span>
											<span class="text-help offset-md-3"> 本功能需要 <a href="https://open.alipay.com/platform/home.htm" target="_blank">蚂蚁金服开放平台</a> 申请权限及应用 </span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="f2fpay_app_id">应用ID</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="f2fpay_app_id" id="f2fpay_app_id" value="{{$f2fpay_app_id}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setF2fpayAppId()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3">即：APPID</span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="f2fpay_private_key">RSA私钥</label>
											<div class="col-md-7">
												<div class="input-group">
													<input class="form-control" type="text" name="f2fpay_private_key" id="f2fpay_private_key" value="{{$f2fpay_private_key}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setF2fpayPrivateKey()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3">即：rsa_private_key，不包括首尾格式</span>
										</div>
									</div>
									<div class="form-group col-lg-6">
										<div class="row">
											<label class="col-md-3 col-form-label" for="f2fpay_public_key">支付宝公钥</label>
											<div class="col-md-7">
												<div class="input-group">
													<input type="text" class="form-control" name="f2fpay_public_key" id="f2fpay_public_key" value="{{$f2fpay_public_key}}"/>
													<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setF2fpayPublicKey()">修改</button></span>
												</div>
											</div>
											<span class="text-help offset-md-3"> 注意不是RSA公钥 </span>
										</div>
									</div>
								</div>
								<div class="form-group col-lg-6">
									<div class="row">
										<label class="col-md-3 col-form-label" for="f2fpay_subject_name">自定义商品名称</label>
										<div class="col-md-7">
											<div class="input-group">
												<input type="text" class="form-control" name="f2fpay_subject_name" id="f2fpay_subject_name" value="{{$f2fpay_subject_name}}"/>
												<span class="input-group-append"><button class="btn btn-primary" type="button" onclick="setF2fpaySubjectName()">修改</button></span>
											</div>
										</div>
										<span class="text-help offset-md-3"> 用于在用户支付宝客户端显示 </span>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="//cdn.bootcss.com/bootstrap-switch/4.0.0-alpha.1/js/bootstrap-switch.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/dropify/dropify.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/responsive-tabs.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/tabs.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/dropify.js" type="text/javascript"></script>

	<script type="text/javascript">
        $('input[type="checkbox"]').bootstrapSwitch();
        // 注册的默认标签
        $('#initial_labels_for_user').on("changed.bs.select", function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'initial_labels_for_user',
                value: $(this).val() ? $(this).val().join(',') : ''
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 启用、禁用随机端口
        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_rand_port',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用屏蔽大陆访问
        $('#is_forbid_china').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_forbid_china',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用屏蔽海外访问
        $('#is_forbid_oversea').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_forbid_oversea',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用机器人访问
        $('#is_forbid_robot').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_forbid_robot',
                    value: is_forbid_robot = state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用注册校验验证码
        $('#is_verify_register').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_verify_register',
                    value: is_verify_register = state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用自定义端口
        $('#is_user_rand_port').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_user_rand_port',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用登录加流量
        $('#is_checkin').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_checkin',
                    value: is_checkin = state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用注册

        $('#is_register').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_register',
                    value: is_register = state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、可选、禁用邀请注册
        $("#is_invite_register").on("changed.bs.select", function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'is_invite_register',
                value: $(this).val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 启用、禁用用户重置密码
        $('#is_reset_password').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_reset_password',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用验证码
        $('#is_captcha').on("changed.bs.select", function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'is_captcha',
                value: $(this).val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });


        // 启用、禁用免费邀请码
        $('#is_free_code').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_free_code',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用用户激活用户
        $('#is_active_register').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_active_register',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用用户到期自动邮件提醒
        $('#expire_warning').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'expire_warning',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用节点离线发件提醒管理员
        $('#is_node_crash_warning').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_node_crash_warning',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用节点离线发ServerChan微信消息提醒
        $('#is_server_chan').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_server_chan',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用Namesilo
        $('#is_namesilo').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_namesilo',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用混合订阅
        $('#mix_subscribe').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'mix_subscribe',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用随机订阅
        $('#rand_subscribe').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'rand_subscribe',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用自定义订阅
        $('#is_custom_subscribe').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_custom_subscribe',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用PushBear
        $('#is_push_bear').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_push_bear',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用节点阻断探测
        $('#nodes_detection').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'nodes_detection',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用订阅异常自动封禁
        $('#is_subscribe_ban').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_subscribe_ban',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用退关返利用户可见与否
        $('#referral_status').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'referral_status',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用随机端口
        $('#traffic_warning').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'traffic_warning',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用随机端口
        $('#is_clear_log').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_clear_log',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用流量自动重置
        $('#reset_traffic').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'reset_traffic',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用流量异常自动封号
        $('#is_traffic_ban').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_traffic_ban',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用端口自动释放
        $('#auto_release_port').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'auto_release_port',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用节点使用报告
        $('#node_daily_report').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'node_daily_report',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 过期封禁是否禁止账号
        $('#is_ban_status').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_ban_status',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用alipay国际
        $('#is_alipay').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_alipay',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 启用、禁用支付宝当面付
        $('#is_f2fpay').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'is_f2fpay',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 流量异常阈值
        function setTrafficBanValue() {
            const traffic_ban_value = $("#traffic_ban_value").val();

            if (traffic_ban_value < 1) {
                swal.fire({title: '不能小于1', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'traffic_ban_value',
                value: traffic_ban_value
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置用户封号时长
        function setTrafficBanTime() {
            const traffic_ban_time = $("#traffic_ban_time").val();

            if (traffic_ban_time < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'traffic_ban_time',
                value: traffic_ban_time
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置节点离线警告收件地址
        function setCrashWarningEmail() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'webmaster_email',
                value: $("#webmaster_email").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置ServerChan的SCKEY
        function setServerChanKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'server_chan_key',
                value: $("#server_chan_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置Namesilo API KEY
        function setNamesiloKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'namesilo_key',
                value: $("#namesilo_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置PushBear的SendKey
        function setPushBearSendKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'push_bear_send_key',
                value: $("#push_bear_send_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置PushBear的消息通道二维码URL
        function setPushBearQrCode() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'push_bear_qrcode',
                value: $("#push_bear_qrcode").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置TCP阻断检测提醒次数
        function setNumberOfWarningTimes() {
            const numberOfWarningTimes = $("#numberOfWarningTimes").val();

            if (numberOfWarningTimes < 0 || numberOfWarningTimes > 12) {
                swal.fire({title: '只能在0-12之间', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'numberOfWarningTimes',
                value: numberOfWarningTimes
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置订阅封禁阈值
        function setSubscribeBanTimes() {
            const subscribe_ban_times = $("#subscribe_ban_times").val();

            if (subscribe_ban_times < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'subscribe_ban_times',
                value: subscribe_ban_times
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置alipay加密方式
        $('#alipay_sign_type').change(function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_sign_type',
                value: $(this).val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 设置alipay是否启用SSL验证
        $('#alipay_transport').change(function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_transport',
                value: $(this).val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        //设置alipay的partner
        function setAlipayPartner() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_partner',
                value: $("#alipay_partner").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        //设置alipay的key
        function setAlipayKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_key',
                value: $("#alipay_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        //设置alipay的私钥
        function setAlipayPrivateKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_private_key',
                value: $("#alipay_private_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        //设置alipay的公钥
        function setAlipayPublicKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_public_key',
                value: $("#alipay_public_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置alipay结算币种
        $('#alipay_currency').on("changed.bs.select", function () {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'alipay_currency',
                value: $(this).val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 设置最小流量
        $("#min_rand_traffic").change(function () {
            const min_rand_traffic = $(this).val();
            const max_rand_traffic = $("#max_rand_traffic").val();

            if (parseInt(min_rand_traffic) < 0) {
                swal.fire({title: '最小流量值不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            if (parseInt(min_rand_traffic) >= parseInt(max_rand_traffic)) {
                swal.fire({title: '最小流量值必须小于最大流量值', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'min_rand_traffic',
                value: min_rand_traffic
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 设置最大流量
        $("#max_rand_traffic").change(function () {
            const min_rand_traffic = $("#min_rand_traffic").val();
            const max_rand_traffic = $(this).val();

            if (parseInt(max_rand_traffic) > 99999) {
                swal.fire({title: '最大流量值不能大于99999', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            if (parseInt(min_rand_traffic) >= parseInt(max_rand_traffic)) {
                swal.fire({title: '最大流量值必须大于最小流量值', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'max_rand_traffic',
                value: max_rand_traffic
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 设置f2fpay的应用id
        function setF2fpayAppId() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'f2fpay_app_id',
                value: $("#f2fpay_app_id").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置f2fpay的私钥
        function setF2fpayPrivateKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'f2fpay_private_key',
                value: $("#f2fpay_private_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置f2fpay的公钥
        function setF2fpayPublicKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'f2fpay_public_key',
                value: $("#f2fpay_public_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 自动去除公钥和私钥中的空格和换行
        $("#alipay_public_key,#alipay_private_key,#f2fpay_public_key,#f2fpay_private_key").on('input', function () {
            $(this).val($(this).val().replace(/(\s+)/g, ''));
        });

        // 设置f2fpay的商品名称
        function setF2fpaySubjectName() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'f2fpay_subject_name',
                value: $("#f2fpay_subject_name").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置极验的Id
        function setGeetestId() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'geetest_id',
                value: $("#geetest_id").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置极验的Key
        function setGeetestKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'geetest_key',
                value: $("#geetest_key").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置Google reCAPTCHA的Id
        function setGoogleCaptchaId() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'google_captcha_sitekey',
                value: $("#google_captcha_sitekey").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置Google reCAPTCHA的Key
        function setGoogleCaptchaKey() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'google_captcha_secret',
                value: $("#google_captcha_secret").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置最小端口
        $("#min_port").change(function () {
            const min_port = $(this).val();
            const max_port = $("#max_port").val();

            // 最大端口必须大于最小端口
            if (parseInt(max_port) <= parseInt(min_port)) {
                swal.fire({title: '必须小于最大端口', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            if (parseInt(min_port) < 1000) {
                swal.fire({title: '最小端口不能小于1000', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {_token: '{{csrf_token()}}', name: 'min_port', value: min_port}, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 设置最大端口
        $("#max_port").change(function () {
            const min_port = $("#min_port").val();
            const max_port = $(this).val();

            // 最大端口必须大于最小端口
            if (parseInt(max_port) <= parseInt(min_port)) {
                swal.fire({title: '必须大于最小端口', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            if (parseInt(max_port) > 65535) {
                swal.fire({title: '最大端口不能大于65535', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {_token: '{{csrf_token()}}', name: 'max_port', value: max_port}, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        });

        // 邮箱黑白名单切换
        $('#sensitiveType').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/setConfig", {
                    _token: '{{csrf_token()}}',
                    name: 'sensitiveType',
                    value: state ? 1 : 0
                }, function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.reload())
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                });
            }
        });

        // 设置注册时默认有效期
        function setDefaultDays() {
            const default_days = parseInt($("#default_days").val());

            if (default_days < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'default_days',
                value: default_days
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置注册时默认流量
        function setDefaultTraffic() {
            const default_traffic = parseInt($("#default_traffic").val());

            if (default_traffic < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'default_traffic',
                value: default_traffic
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置可生成邀请码数量
        function setInviteNum() {
            const invite_num = parseInt($("#invite_num").val());

            if (invite_num < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'invite_num',
                value: invite_num
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置重置密码次数
        function setResetPasswordTimes() {
            const reset_password_times = $("#reset_password_times").val();

            if (reset_password_times < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'reset_password_times',
                value: reset_password_times
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置激活用户次数
        function setActiveTimes() {
            const active_times = parseInt($("#active_times").val());

            if (active_times < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'active_times',
                value: active_times
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置节点订阅地址
        function setSubscribeDomain() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'subscribe_domain',
                value: $("#subscribe_domain").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 24小时内同IP注册限制
        function setRegisterIpLimit() {
            const register_ip_limit = parseInt($("#register_ip_limit").val());

            if (register_ip_limit < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'register_ip_limit',
                value: register_ip_limit
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置节点订阅随机展示节点数
        function setSubscribeMax() {
            const subscribe_max = parseInt($("#subscribe_max").val());

            if (subscribe_max < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'subscribe_max',
                value: subscribe_max
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置用户生成邀请码有效期
        function setUserInviteDays() {
            const user_invite_days = parseInt($("#user_invite_days").val());

            if (user_invite_days <= 0) {
                swal.fire({title: '必须大于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'user_invite_days',
                value: user_invite_days
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置管理员生成邀请码有效期
        function setAdminInviteDays() {
            const admin_invite_days = parseInt($("#admin_invite_days").val());

            if (admin_invite_days <= 0) {
                swal.fire({title: '必须大于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'admin_invite_days',
                value: admin_invite_days
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置流量警告阈值
        function setTrafficWarningPercent() {
            const traffic_warning_percent = $("#traffic_warning_percent").val();

            if (traffic_warning_percent < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'traffic_warning_percent',
                value: traffic_warning_percent
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置用户过期提醒阈值
        function setExpireDays() {
            const expire_days = parseInt($("#expire_days").val());

            if (expire_days < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'expire_days',
                value: expire_days
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置网站名称
        function setWebsiteName() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'website_name',
                value: $("#website_name").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置网站地址
        function setWebsiteUrl() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'website_url',
                value: $("#website_url").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置苹果账号
        function setAppStoreId() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'AppStore_id',
                value: $("#AppStore_id").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置苹果密码
        function setAppStorePassword() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'AppStore_password',
                value: $("#AppStore_password").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        //设置管理员邮箱
        function setAdminEmail() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'admin_email',
                value: $("#admin_email").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 生成网站安全码
        function makeWebsiteSecurityCode() {
            $.get("/makeSecurityCode", function (ret) {
                $("#website_security_code").val(ret);
            });
        }

        // 设置网站安全码
        function setWebsiteSecurityCode() {
            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'website_security_code',
                value: $("#website_security_code").val()
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 登录加流量的时间间隔
        function setTrafficLimitTime() {
            const traffic_limit_time = parseInt($("#traffic_limit_time").val());

            if (traffic_limit_time < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'traffic_limit_time',
                value: traffic_limit_time
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置根据推广链接注册送流量
        function setReferralTraffic() {
            const referral_traffic = parseInt($("#referral_traffic").val());

            if (referral_traffic < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'referral_traffic',
                value: referral_traffic
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置根据推广链接注册人每产生一笔消费，则推广人可以获得的返利比例
        function setReferralPercent() {
            const referral_percent = $("#referral_percent").val();

            if (referral_percent < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            if (referral_percent > 100) {
                swal.fire({title: '不能大于100', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'referral_percent',
                value: referral_percent
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }

        // 设置返利满多少元才可以提现
        function setReferralMoney() {
            const referral_money = $("#referral_money").val();

            if (referral_money < 0) {
                swal.fire({title: '不能小于0', type: 'warning', timer: 1000, showConfirmButton: false});
                return;
            }

            $.post("/admin/setConfig", {
                _token: '{{csrf_token()}}',
                name: 'referral_money',
                value: referral_money
            }, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }
	</script>
@endsection
