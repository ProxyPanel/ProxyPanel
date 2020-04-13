<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale()}}" class="ie8 no-js css-menubar"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale()}}" class="ie9 no-js css-menubar"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}" class="no-js css-menubar">
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
			<span class="sr-only">Toggle navigation切换导航</span>
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
							<span class="sr-only">Toggle menubar切换菜单栏</span>
							<span class="hamburger-bar"></span>
						</i>
					</a>
				</li>
				<li class="nav-item hidden-sm-down" id="toggleFullscreen">
					<a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
						<span class="sr-only">Toggle fullscreen切换全屏</span>
					</a>
				</li>
			</ul>
			<ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
				<li class="nav-item dropdown">
					<a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" data-animation="scale-up" aria-expanded="false" role="button">
						<span class="flag-icon wb-flag"></span>
						<span class="flag-icon icon wb-chevron-down-mini"></span>
					</a>
					<div class="dropdown-menu" role="menu">
						<a href="{{url('lang', ['locale' => 'zh-CN'])}}" class="dropdown-item" role="menuitem">
							<span class="flag-icon flag-icon-cn"></span>
							简体中文</a>
						<a href="{{url('lang', ['locale' => 'zh-tw'])}}" class="dropdown-item" role="menuitem">
							<span class="flag-icon flag-icon-tw"></span>
							繁體中文</a>
						<a href="{{url('lang', ['locale' => 'en'])}}" class="dropdown-item" role="menuitem">
							<span class="flag-icon flag-icon-gb"></span>
							English</a>
						<a href="{{url('lang', ['locale' => 'ja'])}}" class="dropdown-item" role="menuitem">
							<span class="flag-icon flag-icon-jp"></span>
							日本語</a>
						<a href="{{url('lang', ['locale' => 'ko'])}}" class="dropdown-item" role="menuitem">
							<span class="flag-icon flag-icon-kr"></span>
							한국어</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a href="#" aria-expanded="false" class="nav-link navbar-avatar" data-animation="scale-up" data-toggle="dropdown" role="button">
                <span class="avatar avatar-online">
	              @include('user.components.avatar')
                  <i></i>
                </span>
					</a>
					<div class="dropdown-menu" role="menu">
						@if(Auth::user()->is_admin)
							<a href="/admin" class="dropdown-item" role="menuitem">
								<i class="icon wb-user" aria-hidden="true"></i>
								{{trans('home.console')}}
							</a>
						@endif
						<a href="/profile" class="dropdown-item" role="menuitem">
							<i class="icon wb-user" aria-hidden="true"></i>
							{{trans('home.profile')}}
						</a>
						<div class="dropdown-divider" role="presentation"></div>
						<a href="/logout" class="dropdown-item" role="menuitem">
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
			<li class="site-menu-item {{in_array(Request::path(), ['/', 'profile', 'article']) ? 'active open' : ''}}">
				<a href="/">
					<i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.home')}}</span>
				</a>
			</li>
			<li class="site-menu-item {{in_array(Request::path(), ['services']) || in_array(Request::segment(1), ['buy', 'payment']) ? 'active open' : ''}}">
				<a href="/services">
					<i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.services')}}</span>
				</a>
			</li>
			<li class="site-menu-item {{in_array(Request::path(), ['nodeList']) || in_array(Request::segment(1), ['nodeList']) ? 'active open' : ''}}">
				<a href="/nodeList">
					<i class="site-menu-icon wb-grid-4" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.nodeList')}}</span>
				</a>
			</li>
			<li class="site-menu-item {{in_array(Request::path(), ['help', 'article']) ? 'active open' : ''}}">
				<a href="/help">
					<i class="site-menu-icon wb-info-circle" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.help')}}</span>
				</a>
			</li>
			<li class="site-menu-item {{in_array(Request::path(), ['tickets', 'replyTicket']) ? 'active open' : ''}}">
				<a href="/tickets">
					<i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.ticket_title')}}</span>
				</a>
			</li>
			<li class="site-menu-item {{in_array(Request::path(), ['invoices']) ? 'active open' : ''}}">
				<a href="/invoices">
					<i class="site-menu-icon wb-bookmark" aria-hidden="true"></i>
					<span class="site-menu-title">{{trans('home.invoices')}}</span>
				</a>
			</li>
			@if(!\App\Http\Models\Order::uid()->where('status', 2)->where('is_expire', 0)->where('origin_amount', '>', 0)->doesntExist())
				@if(\App\Components\Helpers::systemConfig()['is_invite_register'])
					<li class="site-menu-item {{in_array(Request::path(), ['invite']) ? 'active open' : ''}}">
						<a href="/invite">
							<i class="site-menu-icon wb-extension" aria-hidden="true"></i>
							<span class="site-menu-title">{{trans('home.invite_code')}}</span>
						</a>
					</li>
				@endif
				@if((\App\Components\Helpers::systemConfig()['referral_status']) )
					<li class="site-menu-item {{in_array(Request::path(), ['referral']) ? 'active open' : ''}}">
						<a href="/referral">
							<i class="site-menu-icon wb-star-outline" aria-hidden="true"></i>
							<span class="site-menu-title">{{trans('home.referrals')}}</span>
						</a>
					</li>
				@endif
			@endif
		</ul>
	</div>
</div>
<div class="page">
	<!--[if lt IE 8]>
	<p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
		<a href="http://browsehappy.com/" target="_blank">升级您的浏览器</a> <br/>You are using an
		<strong>outdated</strong> browser. Please
		<a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->
	@yield('content')
</div>
<footer class="site-footer">
	<div class="site-footer-legal"> Copyright &copy; 2017 - 2020
		<a href="{{\App\Components\Helpers::systemConfig()['website_url']}}" target="_blank">{{\App\Components\Helpers::systemConfig()['website_name']}}</a> | 基于<a href="https://github.com/ZBrettonYe/SSRPanel_OtakuMod">{{config('version.name')}}</a> 版本: {{config('version.number')}}  开发
	</div>
	<div class="site-footer-right">
		由 <i class="red-600 wb-heart"></i>
		<a href="https://github.com/ZBrettonYe">兔姬菌</a> 制作
	</div>
</footer>
@if(Session::get("admin"))
	<div class="panel panel-bordered w-300 bg-grey-200" style="position:fixed;right:20px;bottom:0;">
		<div class="panel-body text-right">
			<h5>当前身份：{{Auth::user()->email}}</h5>
			<button type="button" class="btn btn-danger btn-block mt-20" id="return_to_admin">
				返回管理页面
			</button>
		</div>
	</div>
@endif
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

@if(Session::get("admin"))
	<script type="text/javascript">
        $("#return_to_admin").click(function () {
            $.ajax({
                type: "POST",
                url: "/switchToAdmin",
                data: {'_token': '{{csrf_token()}}'},
                dataType: "json",
                success: function (ret) {
                    swal.fire({
                        title: ret.message,
                        type: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => window.location.href = "/admin")
                },
                error: function (ret) {
                    swal.fire({
                        title: ret.message,
                        type: 'error',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
	</script>
@endif
<!-- 统计 -->
{!! \App\Components\Helpers::systemConfig()['website_analytics'] !!}
<!-- 客服 -->
{!! \App\Components\Helpers::systemConfig()['website_customer_service'] !!}
</body>
</html>
