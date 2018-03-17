<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    @yield('css')
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components-rounded.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="/assets/layouts/layout4/css/layout.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/layouts/layout4/css/themes/default.min.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="/assets/layouts/layout4/css/custom.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}" />
</head>

<body class="page-container-bg-solid page-header-fixed page-sidebar-closed-hide-logo">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="{{url('/user')}}">
                <img src="/assets/images/logo.png" alt="logo" class="logo-default" /> </a>
            <div class="menu-toggler sidebar-toggler">
                <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN PAGE TOP -->
        <div class="page-top">
            <!-- BEGIN TOP NAVIGATION MENU -->
            <div class="top-menu">
                <ul class="nav navbar-nav pull-right">
                    <li class="separator hide"> </li>
                    <!-- BEGIN USER LOGIN DROPDOWN -->
                    <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                    <li class="dropdown dropdown-user dropdown-dark">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <span class="username username-hide-on-mobile"> {{Session::get('user')['username']}} </span>
                            <!-- DOC: Do not remove below empty space(&nbsp;) as its purposely used -->
                            <img alt="" class="img-circle" src="/assets/images/avatar.png" /> </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                            @if(Session::get('user')['is_admin'])
                                <li>
                                    <a href="{{url('admin')}}"> <i class="icon-settings"></i> 管理中心 </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{url('user/profile')}}"> <i class="icon-user"></i> 个人资料 </a>
                            </li>
                            <li class="divider"> </li>
                            <li>
                                <a href="{{url('logout')}}"> <i class="icon-key"></i> 退出 </a>
                            </li>
                        </ul>
                    </li>
                    <!-- END USER LOGIN DROPDOWN -->
                </ul>
            </div>
            <!-- END TOP NAVIGATION MENU -->
        </div>
        <!-- END PAGE TOP -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar-wrapper">
        <!-- BEGIN SIDEBAR -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
        <div class="page-sidebar navbar-collapse collapse">
            <!-- BEGIN SIDEBAR MENU -->
            <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
            <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
            <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
            <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
            <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
            <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
            <ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
                <li class="nav-item start {{in_array(Request::path(), ['/', 'user', 'user/subscribe', 'user/profile']) ? 'active open' : ''}}">
                    <a href="{{url('user')}}" class="nav-link nav-toggle">
                        <i class="icon-home"></i>
                        <span class="title">{{trans('home.home')}}</span>
                        <span class="selected"></span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['user/invite']) ? 'active open' : ''}}">
                    <a href="{{url('user/invite')}}" class="nav-link nav-toggle">
                        <i class="icon-user-follow"></i>
                        <span class="title">{{trans('home.invite_code')}}</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['user/goodsList', 'user/addOrder']) ? 'active open' : ''}}">
                    <a href="{{url('user/goodsList')}}" class="nav-link nav-toggle">
                        <i class="icon-basket"></i>
                        <span class="title">{{trans('home.services')}}</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['user/orderList']) ? 'active open' : ''}}">
                    <a href="{{url('user/orderList')}}" class="nav-link nav-toggle">
                        <i class="icon-wallet"></i>
                        <span class="title">{{trans('home.invoices')}}</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['user/ticketList', 'user/replyTicket']) ? 'active open' : ''}}">
                    <a href="{{url('user/ticketList')}}" class="nav-link nav-toggle">
                        <i class="icon-question"></i>
                        <span class="title">{{trans('home.tickets')}}</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['user/trafficLog']) ? 'active open' : ''}}">
                    <a href="{{url('user/trafficLog')}}" class="nav-link nav-toggle">
                        <i class="icon-speedometer"></i>
                        <span class="title">{{trans('home.traffic_log')}}</span>
                    </a>
                </li>
                @if(Session::get('referral_status'))
                <li class="nav-item {{in_array(Request::path(), ['user/referral']) ? 'active open' : ''}}">
                    <a href="{{url('user/referral')}}" class="nav-link nav-toggle">
                        <i class="icon-diamond"></i>
                        <span class="title">{{trans('home.referrals')}}</span>
                    </a>
                </li>
                @endif
            </ul>
            <!-- END SIDEBAR MENU -->
        </div>
        <!-- END SIDEBAR -->
    </div>
    <!-- END SIDEBAR -->
    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
        @yield('content')
    </div>
    @if(Session::get("admin"))
        <div class="portlet light bordered" style="position:fixed;right:20px;bottom:0px;width:270px;">
            <div class="portlet-body text-right">
                <h5>当前身份：{{Session::get("user")['username']}}</h5>
                <button class="btn btn-sm btn-danger" id="return_to_admin"> 返回管理页面 </button>
            </div>
        </div>
    @endif
    <!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<div class="page-footer">
    <div class="page-footer-inner"> 2017 - 2018 &copy; <a href="https://github.com/ssrpanel/ssrpanel" target="_blank">SSRPanel</a> </div>
    <div class="scroll-to-top">
        <i class="icon-arrow-up"></i>
    </div>
</div>
<!-- END FOOTER -->
<!--[if lt IE 9]>
<script src="/assets/global/plugins/respond.min.js"></script>
<script src="/assets/global/plugins/excanvas.min.js"></script>
<script src="/assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
@yield('script')

@if(Session::get("admin"))
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
        $("#return_to_admin").click(function () {
            $.ajax({
                'url': "{{url("/user/switchToAdmin")}}",
                'data': {
                    '_token': "{{csrf_token()}}"
                },
                'dataType': "json",
                'type': "POST",
                success: function (ret) {
                    layer.msg(ret.message, {time: 1000}, function () {
                        if (ret.status == 'success') {
                            window.location.href = "{{url('admin')}}";
                        }
                    });
                },
                error: function (ret) {
                    layer.msg("操作失败：" + ret, {time: 5000});
                }
            });
        });
    </script>
@endif
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/assets/layouts/layout4/scripts/layout.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->
</body>

</html>
