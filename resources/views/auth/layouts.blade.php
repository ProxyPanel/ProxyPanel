<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="兔姬菌">
	<meta name="copyright" content="2017-2020©兔姬菌">
	<title>@yield('title')</title>
	<link href="{{asset('favicon.ico')}}" rel="shortcut icon apple-touch-icon">
	<!-- Stylesheets -->
	<link href="/assets/global/css/bootstrap.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/css/bootstrap-extend.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/css/site.min.css" type="text/css" rel="stylesheet">
	<!-- Plugins -->
	<link href="/assets/global/vendor/animsition/animsition.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/asscrollable/asScrollable.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/slidepanel/slidePanel.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/flag-icon-css/flag-icon.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/examples/css/pages/login-v3.css" type="text/css" rel="stylesheet">
@yield('css')
<!-- Fonts -->
	<link href="/assets/global/fonts/web-icons/web-icons.min.css" type="text/css" rel="stylesheet">
	<link href="//fonts.loli.net/css?family=Roboto:300,400,500,300italic" type="text/css" rel="stylesheet">
	<!--[if lt IE 9]>
	<script src="/assets/global/vendor/html5shiv/html5shiv.min.js" type="text/javascript"></script> <![endif]-->
	<!--[if lt IE 10]>
	<script src="/assets/global/vendor/media-match/media.match.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/respond/respond.min.js" type="text/javascript"></script> <![endif]-->
	<!-- Scripts -->
	<script src="/assets/global/vendor/breakpoints/breakpoints.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		Breakpoints();
	</script>
</head>
<body class="animsition page-login-v3 layout-full" style="position: relative;">
<!--[if lt IE 8]> <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
<a href="http://browsehappy.com/" target="_blank">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong>
                                            browser. Please
<a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
@if(Agent::isMobile() && Agent::is('iOS') && strpos(Agent::getUserAgent(), 'MicroMessenger') !== false)
	<style type="text/css">
        .cover-up {
            opacity: 0.1;
            filter: alpha(opacity=10);
        }
	</style>
	<div class="m-0 p-0 w-full h-full text-white" style="z-index: 10; position: absolute;">
		<div class="font-size-16 h-p33 pl-20 pt-20"
				style="line-height: 1.8; background: url(//gw.alicdn.com/tfs/TB1eSZaNFXXXXb.XXXXXXXXXXXX-750-234.png) center top/contain no-repeat">
			<p>点击右上角 <i class="icon wb-more-horizontal"></i>，选择在<img
						src="//gw.alicdn.com/tfs/TB1xwiUNpXXXXaIXXXXXXXXXXXX-55-55.png"
						class="w-30 h-30 vertical-align-middle m-3" alt="Safari"/> Safari 中打开
			</p>
			<p>您就可以正常访问本站了呦~</p>
		</div>
	</div>
@endif
<div class="page vertical-align text-center cover-up" data-animsition-in="fade-in" data-animsition-out="fade-out">
	<div class="page-content vertical-align-middle">
		<div class="animation-slide-top animation-duration-1">
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">
						<div class="brand">
							<img
									src="{{sysConfig('website_home_logo')? :'/assets/images/logo64.png'}}"
									class="brand-img" alt="logo"/>
							<h3 class="brand-text">{{sysConfig('website_name')}}</h3>
						</div>
					</div>
					<div class="ribbon ribbon-reverse ribbon-info ribbon-clip">
						<button class="ribbon-inner btn dropdown-toggle pt-0" id="language" data-toggle="dropdown"
								aria-expanded="false">
							<i class="font-size-20 wb-globe"></i>
						</button>
						<div class="dropdown-menu dropdown-menu-bullet" aria-labelledby="language" role="menu">
							<a class="dropdown-item" href="{{url('lang', ['locale' => 'zh-CN'])}}" role="menuitem">
								<span class="flag-icon flag-icon-cn"></span>
								简体中文</a>
							<a class="dropdown-item" href="{{url('lang', ['locale' => 'zh-tw'])}}" role="menuitem">
								<span class="flag-icon flag-icon-tw"></span>
								繁體中文</a>
							<a class="dropdown-item" href="{{url('lang', ['locale' => 'en'])}}" role="menuitem">
								<span class="flag-icon flag-icon-gb"></span>
								English</a>
							<a class="dropdown-item" href="{{url('lang', ['locale' => 'ja'])}}" role="menuitem">
								<span class="flag-icon flag-icon-jp"></span>
								日本語</a>
							<a class="dropdown-item" href="{{url('lang', ['locale' => 'ko'])}}" role="menuitem">
								<span class="flag-icon flag-icon-kr"></span>
								한국어</a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					@yield('content')
				</div>
			</div>
		</div>
		@yield('modal')
	</div>
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
<script src="/assets/global/vendor/jquery-placeholder/jquery.placeholder.js" type="text/javascript"></script>
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
<!-- Page -->
@yield('script')
<script src="/assets/js/Site.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/asscrollable.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/slidepanel.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/jquery-placeholder.js" type="text/javascript"></script>
<script src="/assets/global/js/Plugin/material.js" type="text/javascript"></script>
<!-- 统计 -->
{!! sysConfig('website_analytics') !!}
<!-- 客服 -->
{!! sysConfig('website_customer_service') !!}
<script type="text/javascript">
	(function (document, window, $) {
		'use strict';
		const Site = window.Site;
		$(document).ready(function () {
			Site.run();
		});
	})(document, window, jQuery);
</script>
</body>
</html>