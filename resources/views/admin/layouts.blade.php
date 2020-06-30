<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->
<head>
	<meta charset="utf-8">
	<title>{{\App\Components\Helpers::systemConfig()['website_name']}}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="兔姬菌">
	<meta name="copyright" content="2017-2020©兔姬菌">
	<link href="{{asset('favicon.ico')}}" rel="shortcut icon apple-touch-icon">
	<!-- 样式表/Stylesheets -->
	<link href="/assets/global/css/bootstrap.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/css/bootstrap-extend.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/css/site.min.css" type="text/css" rel="stylesheet">
	<!-- 插件/Plugins -->
	<link href="/assets/global/vendor/animsition/animsition.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/asscrollable/asScrollable.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/slidepanel/slidePanel.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/flag-icon-css/flag-icon.min.css" type="text/css" rel="stylesheet">
	@yield('css')
	<link href="/assets/custom/Plugin/sweetalert2/sweetalert2.min.css" type="text/css" rel="stylesheet">
	<!-- 字体/Fonts -->
	<link href="/assets/global/fonts/web-icons/web-icons.min.css" type="text/css" rel="stylesheet">
	<link href="//fonts.loli.net/css?family=Roboto:300,400,500,300italic" type="text/css" rel="stylesheet">
	<!--[if lt IE 9]>
	<script src="/assets/global/vendor/html5shiv/html5shiv.min.js" type="text/javascript"></script>
	<![endif]-->
	<!--[if lt IE 10]>
	<script src="/assets/global/vendor/media-match/media.match.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/respond/respond.min.js" type="text/javascript"></script>
	<![endif]-->
	<!-- Scripts -->
	<script src="/assets/global/vendor/breakpoints/breakpoints.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		Breakpoints();
	</script>
</head>

<body class="animsition dashboard">
<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse bg-indigo-600" role="navigation">
	<div class="navbar-header">
		<button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar">
			<span class="sr-only">Toggle navigation</span>
			<span class="hamburger-bar"></span>
		</button>
		<button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse">
			<i class="icon wb-more-horizontal" aria-hidden="true"></i>
		</button>
		<div class="navbar-brand navbar-brand-center">
			<img src="{{\App\Components\Helpers::systemConfig()['website_logo']? :'/assets/images/logo64.png'}}" class="navbar-brand-logo" alt="logo"/>
			<span class="navbar-brand-text hidden-xs-down"> {{\App\Components\Helpers::systemConfig()['website_name']}}</span>
		</div>
	</div>
	<div class="navbar-container container-fluid">
		<div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
			<ul class="nav navbar-toolbar">
				<li class="nav-item hidden-float" id="toggleMenubar">
					<a class="nav-link" data-toggle="menubar" href="#" role="button">
						<i class="icon hamburger hamburger-arrow-left">
							<span class="sr-only">菜单</span>
							<span class="hamburger-bar"></span>
						</i>
					</a>
				</li>
				<li class="nav-item hidden-sm-down">
					<a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
						<span class="sr-only">全屏</span>
					</a>
				</li>
			</ul>
			<ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
				<li class="nav-item dropdown">
					<a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
						<span class="avatar avatar-online">
							<img src="/assets/images/avatar.svg" alt="..."/>
							<i></i>
						</span>
					</a>
					<div class="dropdown-menu" role="menu">
						<a class="dropdown-item" href="/" role="menuitem">
							<i class="icon wb-settings" aria-hidden="true"></i>
							个人中心
						</a>
						<a class="dropdown-item" href="/admin/profile" role="menuitem">
							<i class="icon wb-user" aria-hidden="true"></i>
							{{trans('home.profile')}}
						</a>
						<div class="dropdown-divider" role="presentation"></div>
						<a class="dropdown-item" href="/logout" role="menuitem">
							<i class="icon wb-power" aria-hidden="true"></i>
							{{trans('home.logout')}}
						</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
</nav>
<div class="site-menubar site-menubar-light">
	<div class="site-menubar-body">
		<ul class="site-menu" data-plugin="menu">
			<li class="site-menu-item {{in_array(Request::path(), ['admin']) ? 'active open' : ''}}">
				<a href="/admin">
					<i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
					<span class="site-menu-title">管理中心</span>
				</a>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/export', 'admin/onlineIPMonitor', 'admin/userMonitor', 'admin/userCreditLogList', 'subscribe/subscribeList']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-user" aria-hidden="true"></i>
					<span class="site-menu-title">用户系统</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/export', 'admin/onlineIPMonitor', 'admin/userMonitor']) ? 'active open' : ''}}">
						<a href="/admin/userList" class="animsition-link">
							<span class="site-menu-title">用户管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userCreditLogList']) ? 'active open' : ''}}">
						<a href="/admin/userCreditLogList" class="animsition-link">
							<span class="site-menu-title">余额变动</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['subscribe']) ? 'active open' : ''}}">
						<a href="/subscribe" class="animsition-link">
							<span class="site-menu-title">订阅管理</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['ticket', 'ticket/add','ticket/reply', 'admin/articleList', 'admin/addArticle', 'admin/editArticle', 'marketing/push', 'marketing/email']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
					<span class="site-menu-title">客服系统</span>
					@if(\App\Models\Ticket::query()->whereStatus(0)->count() > 0 )
						<div class="site-menu-badge">
							<span class="badge badge-pill badge-success">{{\App\Models\Ticket::query()->whereStatus(0)->count()}}</span>
						</div>
					@endif
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['ticket', 'ticket/add','ticket/reply']) ? 'active open' : ''}}">
						<a href="/ticket" class="animsition-link">
							<span class="site-menu-title">服务工单</span>
							@if(\App\Models\Ticket::query()->whereStatus(0)->count() > 0 )
								<div class="site-menu-label">
									<span class="badge badge-danger badge-round mr-25">{{\App\Models\Ticket::query()->whereStatus(0)->count()}}</span>
								</div>
							@endif
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/articleList', 'admin/addArticle', 'admin/editArticle']) ? 'active open' : ''}}">
						<a href="/admin/articleList" class="animsition-link">
							<span class="site-menu-title">文章管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['marketing/push']) ? 'active open' : ''}}">
						<a href="/marketing/push" class="animsition-link">
							<span class="site-menu-title">消息推送</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['marketing/email']) ? 'active open' : ''}}">
						<a href="/marketing/email" class="animsition-link">
							<span class="site-menu-title">邮件群发</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['node', 'node/add', 'node/edit', 'node/monitor', 'node/pingLog']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-grid-4" aria-hidden="true"></i>
					<span class="site-menu-title">线路系统</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['node', 'node/add', 'node/edit', 'node/monitor']) ? 'active open' : ''}}">
						<a href="/node" class="animsition-link">
							<span class="site-menu-title">线路管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['node/pingLog']) ? 'active open' : ''}}">
						<a href="/node/pingLog">
							<span class="site-menu-title">测速日志</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['rule', 'rule/add', 'rule/edit', 'rule/group', 'rule/group/add', 'rule/group/edit', 'rule/group/assign', 'rule/log']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-eye" aria-hidden="true"></i>
					<span class="site-menu-title">审计规则</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['rule', 'rule/add', 'rule/edit']) ? 'active open' : ''}}">
						<a href="/rule" class="animsition-link">
							<span class="site-menu-title">规则列表</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['rule/group', 'rule/group/add', 'rule/group/edit', 'rule/group/assign']) ? 'active open' : ''}}">
						<a href="/rule/group">
							<span class="site-menu-title">规则分组</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['rule/log']) ? 'active open' : ''}}">
						<a href="/rule/log">
							<span class="site-menu-title">触发记录</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['shop', 'shop/add', 'shop/edit', 'coupon', 'coupon/add','admin/orderList']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
					<span class="site-menu-title">商品系统</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['shop', 'shop/add', 'shop/edit']) ? 'active open' : ''}}">
						<a href="/shop" class="animsition-link">
							<span class="site-menu-title">商品管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['coupon', 'coupon/add']) ? 'active open' : ''}}">
						<a href="/coupon" class="animsition-link">
							<span class="site-menu-title">卡券管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/orderList']) ? 'active open' : ''}}">
						<a href="/admin/orderList" class="animsition-link">
							<span class="site-menu-title">商品订单</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/inviteList', 'admin/affList', 'admin/affDetail', 'admin/userRebateList']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-thumb-up" aria-hidden="true"></i>
					<span class="site-menu-title">推广系统</span>
					@if(\App\Models\ReferralApply::query()->whereStatus(0)->count() > 0 )
						<div class="site-menu-badge">
							<span class="badge badge-pill badge-success">{{\App\Models\Ticket::query()->whereStatus(0)->count()}}</span>
						</div>
					@endif
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['admin/inviteList']) ? 'active open' : ''}}">
						<a href="/admin/inviteList" class="animsition-link">
							<span class="site-menu-title">邀请管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/affList', 'admin/affDetail']) ? 'active open' : ''}}">
						<a href="/admin/affList" class="animsition-link">
							<span class="site-menu-title">提现管理</span>
							@if(\App\Models\ReferralApply::query()->whereStatus(0)->count() > 0 )
								<div class="site-menu-label">
									<span class="badge badge-danger badge-round mr-25">{{\App\Models\ReferralApply::query()->whereStatus(0)->count()}}</span>
								</div>
							@endif
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userRebateList']) ? 'active open' : ''}}">
						<a href="/admin/userRebateList" class="animsition-link">
							<span class="site-menu-title">返利流水</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['admin/trafficLog', 'admin/userTrafficLogList', 'admin/userBanLogList', 'admin/userOnlineIPList', 'admin/onlineIPMonitor', 'admin/notificationLog', 'payment/callbackList', 'logs']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-calendar" aria-hidden="true"></i>
					<span class="site-menu-title">日志系统</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['admin/trafficLog']) ? 'active open' : ''}}">
						<a href="/admin/trafficLog" class="animsition-link">
							<span class="site-menu-title">流量使用</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userTrafficLogList']) ? 'active open' : ''}}">
						<a href="/admin/userTrafficLogList" class="animsition-link">
							<span class="site-menu-title">流量变动</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userBanLogList']) ? 'active open' : ''}}">
						<a href="/admin/userBanLogList" class="animsition-link">
							<span class="site-menu-title">封禁记录</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/userOnlineIPList']) ? 'active open' : ''}}">
						<a href="/admin/userOnlineIPList" class="animsition-link">
							<span class="site-menu-title">在线记录</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/onlineIPMonitor']) ? 'active open' : ''}}">
						<a href="/admin/onlineIPMonitor" class="animsition-link">
							<span class="site-menu-title">在线监控</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/notificationLog']) ? 'active open' : ''}}">
						<a href="/admin/notificationLog" class="animsition-link">
							<span class="site-menu-title">通知记录</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['payment/callbackList']) ? 'active open' : ''}}">
						<a href="/payment/callbackList" class="animsition-link">
							<span class="site-menu-title">支付回调</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['logs']) ? 'active open' : ''}}">
						<a href="/logs" class="animsition-link" target="_blank">
							<span class="site-menu-title">系统运行</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['tools/decompile', 'tools/convert', 'tools/import', 'tools/analysis']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-briefcase" aria-hidden="true"></i>
					<span class="site-menu-title">工具箱</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['tools/decompile']) ? 'active open' : ''}}">
						<a href="/tools/decompile" class="animsition-link">
							<span class="site-menu-title">反解析</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['tools/convert']) ? 'active open' : ''}}">
						<a href="/tools/convert" class="animsition-link">
							<span class="site-menu-title">格式转换</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['tools/import']) ? 'active open' : ''}}">
						<a href="/tools/import" class="animsition-link">
							<span class="site-menu-title">数据导入</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['tools/analysis']) ? 'active open' : ''}}">
						<a href="/tools/analysis" class="animsition-link">
							<span class="site-menu-title">日志分析</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="site-menu-item has-sub {{in_array(Request::path(), ['sensitiveWords', 'admin/config', 'admin/system']) ? 'active open' : ''}}">
				<a href="javascript:void(0)">
					<i class="site-menu-icon wb-settings" aria-hidden="true"></i>
					<span class="site-menu-title">设置</span>
				</a>
				<ul class="site-menu-sub">
					<li class="site-menu-item {{in_array(Request::path(), ['sensitiveWords']) ? 'active open' : ''}}">
						<a href="/sensitiveWords" class="animsition-link">
							<span class="site-menu-title">敏感词管理</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/config']) ? 'active open' : ''}}">
						<a href="/admin/config" class="animsition-link">
							<span class="site-menu-title">通用配置</span>
						</a>
					</li>
					<li class="site-menu-item {{in_array(Request::path(), ['admin/system']) ? 'active open' : ''}}">
						<a href="/admin/system" class="animsition-link">
							<span class="site-menu-title">系统设置</span>
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</div>
<div class="page">
	<!--[if lt IE 8]>
	<p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
		<a href="http://browsehappy.com/">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong> browser. Please
		<a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.
	</p>
	<![endif]-->
	@yield('content')
</div>
<!-- 核心/Core -->
<script src="/assets/global/vendor/babel-external-helpers/babel-external-helpers.js" type="text/javascript"></script>
<script src="/assets/global/vendor/jquery/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/popper-js/umd/popper.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/bootstrap/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/animsition/animsition.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/mousewheel/jquery.mousewheel.js" type="text/javascript"></script>
<script src="/assets/global/vendor/asscrollbar/jquery-asScrollbar.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/asscrollable/jquery-asScrollable.min.js" type="text/javascript"></script>
<script src="/assets/global/vendor/ashoverscroll/jquery-asHoverScroll.min.js" type="text/javascript"></script>
<!-- 插件/Plugins -->
<script src="/assets/global/vendor/screenfull/screenfull.js" type="text/javascript"></script>
<script src="/assets/global/vendor/slidepanel/jquery-slidePanel.min.js" type="text/javascript"></script>
<!--[if lt IE 11]>
<script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js" type="text/javascript"></script>
<![endif]-->
<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.min.js" type="text/javascript"></script>
<!-- 脚本/Scripts -->
<script src="/assets/global/js/Component.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin.js" type="text/javascript"></script>
<script src="/assets/global/js/Base.js" type="text/javascript"></script>
<script src="/assets/global/js/Config.js" type="text/javascript"></script>
<script src="/assets/js/Section/Menubar.js" type="text/javascript"></script>
<script src="/assets/js/Section/Sidebar.js" type="text/javascript"></script>
<script src="/assets/js/Section/PageAside.js" type="text/javascript"></script>
<script src="/assets/js/Plugin/menu.js" type="text/javascript"></script>
<!-- 设置/Config -->
<script src="/assets/global/js/config/colors.js" type="text/javascript"></script>
<script type="text/javascript">
	Config.set('assets', '/assets');
</script>
<!-- 页面/Page -->
<script src="/assets/js/Site.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/asscrollable.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/slidepanel.js" type="text/javascript"></script>
<script src="/assets/custom/Plugin/js-cookie/js.cookie.min.js" type="text/javascript"></script>
<script type="text/javascript">
	(function (document, window, $) {
		'use strict';
		const Site = window.Site;
		$(document).ready(function () {
			Site.run();
		});
	})(document, window, jQuery);
</script>
@yield('script')
</body>
</html>
