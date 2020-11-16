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
    <title>{{sysConfig('website_name')}}</title>
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
    @yield('css')
</head>

<body class="animsition dashboard">
<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega navbar-inverse bg-indigo-600"
     role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided"
                data-toggle="menubar">
            <span class="sr-only">Toggle navigation切换导航</span>
            <span class="hamburger-bar"></span>
        </button>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse"
                data-toggle="collapse">
            <i class="icon wb-more-horizontal" aria-hidden="true"></i>
        </button>
        <div class="navbar-brand navbar-brand-center">
            <img src="{{sysConfig('website_logo') ?: '/assets/images/logo64.png'}}"
                 class="navbar-brand-logo" alt="logo"/>
            <span
                    class="navbar-brand-text hidden-xs-down"> {{sysConfig('website_name')}}</span>
        </div>
    </div>
    <div class="navbar-container container-fluid">
        <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
            <ul class="nav navbar-toolbar">
                <li class="nav-item hidden-float" id="toggleMenubar">
                    <a class="nav-link" data-toggle="menubar" href="#" role="button">
                        <i class="icon hamburger hamburger-arrow-left">
                            <span class="sr-only">Toggle menubar | 切换菜单栏</span>
                            <span class="hamburger-bar"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item hidden-sm-down" id="toggleFullscreen">
                    <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                        <span class="sr-only">Toggle fullscreen | 切换全屏</span>
                    </a>
                </li>
            </ul>
            <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" data-animation="scale-up"
                       aria-expanded="false" role="button">
                        <span class="flag-icon wb-flag"></span>
                        <span class="flag-icon icon wb-chevron-down-mini"></span>
                    </a>
                    <div class="dropdown-menu" role="menu">
                        <a href="{{route('lang', ['locale' => 'zh-CN'])}}" class="dropdown-item" role="menuitem">
                            <span class="flag-icon flag-icon-cn"></span>
                            简体中文</a>
                        <a href="{{route('lang', ['locale' => 'zh-tw'])}}" class="dropdown-item" role="menuitem">
                            <span class="flag-icon flag-icon-tw"></span>
                            繁體中文</a>
                        <a href="{{route('lang', ['locale' => 'en'])}}" class="dropdown-item" role="menuitem">
                            <span class="flag-icon flag-icon-gb"></span>
                            English</a>
                        <a href="{{route('lang', ['locale' => 'ja'])}}" class="dropdown-item" role="menuitem">
                            <span class="flag-icon flag-icon-jp"></span>
                            日本語</a>
                        <a href="{{route('lang', ['locale' => 'ko'])}}" class="dropdown-item" role="menuitem">
                            <span class="flag-icon flag-icon-kr"></span>
                            한국어</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" aria-expanded="false" class="nav-link navbar-avatar" data-animation="scale-up"
                       data-toggle="dropdown" role="button">
                        <span class="avatar avatar-online">
                            <x-avatar :user="Auth::getUser()"/><i></i>
                        </span>
                    </a>
                    <div class="dropdown-menu" role="menu">
                        @if(Auth::getUser()->is_admin)
                            <a href="{{route('admin.index')}}" class="dropdown-item" role="menuitem">
                                <i class="icon wb-user" aria-hidden="true"></i>
                                {{trans('home.console')}}
                            </a>
                        @endif
                        <a href="{{route('profile')}}" class="dropdown-item" role="menuitem">
                            <i class="icon wb-user" aria-hidden="true"></i>
                            {{trans('home.profile')}}
                        </a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a href="{{route('logout')}}" class="dropdown-item" role="menuitem">
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
            <li class="site-menu-item {{request()->routeIs('home', 'profile' ,'article') ? 'active open' : ''}}">
                <a href="{{route('home')}}">
                    <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.home')}}</span>
                </a>
            </li>
            <li class="site-menu-item {{request()->routeIs('shop', 'buy', 'orderDetail') ? 'active open' : ''}}">
                <a href="{{route('shop')}}">
                    <i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.services')}}</span>
                </a>
            </li>
            <li class="site-menu-item {{request()->routeIs('node') ? 'active open' : ''}}">
                <a href="{{route('node')}}">
                    <i class="site-menu-icon wb-grid-4" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.nodeList')}}</span>
                </a>
            </li>
            <li class="site-menu-item {{request()->routeIs('help') ? 'active open' : ''}}">
                <a href="{{route('help')}}">
                    <i class="site-menu-icon wb-info-circle" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.help')}}</span>
                </a>
            </li>
            @php
                $openTicket = App\Models\Ticket::uid()->whereStatus(1)->count()
            @endphp
            <li class="site-menu-item {{request()->routeIs('ticket', 'replyTicket') ? 'active open' : ''}}">
                <a href="{{route('ticket')}}">
                    <i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.ticket_title')}}</span>
                    @if($openTicket > 0)
                        <div class="site-menu-badge">
                            <span class="badge badge-pill badge-success">{{$openTicket}}</span>
                        </div>
                    @endif
                </a>
            </li>
            <li class="site-menu-item {{request()->routeIs('invoice', 'invoiceInfo') ? 'active open' : ''}}">
                <a href="{{route('invoice')}}">
                    <i class="site-menu-icon wb-bookmark" aria-hidden="true"></i>
                    <span class="site-menu-title">{{trans('home.invoices')}}</span>
                </a>
            </li>
            @if(\App\Models\ReferralLog::uid()->exists() || \App\Models\Order::uid()->whereStatus(2)->exists())
                @if(sysConfig('is_invite_register'))
                    <li class="site-menu-item {{request()->routeIs('invite') ? 'active open' : ''}}">
                        <a href="{{route('invite')}}">
                            <i class="site-menu-icon wb-extension" aria-hidden="true"></i>
                            <span class="site-menu-title">{{trans('home.invite_code')}}</span>
                        </a>
                    </li>
                @endif
                @if((sysConfig('referral_status')))
                    <li class="site-menu-item {{request()->routeIs('commission') ? 'active open' : ''}}">
                        <a href="{{route('commission')}}">
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
    <div class="site-footer-legal">
        Copyright ©️2017 - 2020 <a href="https://github.com/ProxyPanel/ProxyPanel" target="_blank">{{config('version.name')}}</a>
        🚀 版本: {{config('version.number')}}
    </div>
    <div class="site-footer-right">
        由 <a href="{{sysConfig('website_url')}}" target="_blank">{{sysConfig('website_name')}}</a> 🈺运营
    </div>
</footer>
@if(Session::get("admin"))
    <div class="panel panel-bordered w-300 bg-grey-200" style="position:fixed;right:20px;bottom:0;">
        <div class="panel-body text-right">
            <h5>当前身份：{{Auth::getUser()->email}}</h5>
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
<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.all.min.js" type="text/javascript"></script>

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
  (function(document, window, $) {
    'use strict';

    const Site = window.Site;
    $(document).ready(function() {
      Site.run();
    });
  })(document, window, jQuery);
</script>
@yield('javascript')

@if(Session::get("admin"))
    <script type="text/javascript">
      $('#return_to_admin').click(function() {
        $.ajax({
          method: 'POST',
          url: '{{route('switch')}}',
          data: {'_token': '{{csrf_token()}}'},
          dataType: 'json',
          success: function(ret) {
            swal.fire({
              title: ret.message,
              icon: 'success',
              timer: 1000,
              showConfirmButton: false,
            }).then(() => window.location.href = '{{route('admin.index')}}');
          },
          error: function(ret) {
            swal.fire({
              title: ret.message,
              icon: 'error',
              timer: 1500,
              showConfirmButton: false,
            });
          },
        });
      });
    </script>
@endif
<!-- 统计 -->
{!! sysConfig('website_analytics') !!}
<!-- 客服 -->
{!! sysConfig('website_customer_service') !!}
</body>
</html>
