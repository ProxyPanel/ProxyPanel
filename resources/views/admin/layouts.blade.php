<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>{{\App\Components\Helpers::systemConfig()['website_name']}}</title>
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
            <a href="{{url('/')}}"> <img src="/assets/images/logo.png" alt="logo" class="logo-default" /> </a>
            <div class="menu-toggler sidebar-toggler">
                <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
            </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu" style="float:right">
            <ul class="nav navbar-nav pull-right">
                <li class="separator hide"> </li>
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-user dropdown-dark">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <span class="username username-hide-on-mobile"> {{Auth::user()->username}} </span>
                        <!-- DOC: Do not remove below empty space(&nbsp;) as its purposely used -->
                        <img alt="" class="img-circle" src="/assets/images/avatar.png" /> </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <a href="{{url('/')}}"> <i class="icon-home"></i> 个人中心 </a>
                        </li>
                        <li>
                            <a href="{{url('admin/profile')}}"> <i class="icon-user"></i> 个人设置 </a>
                        </li>
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
                <li class="nav-item start {{in_array(Request::path(), ['admin']) ? 'active open' : ''}}">
                    <a href="{{url('admin')}}" class="nav-link nav-toggle">
                        <i class="fa fa-home"></i>
                        <span class="title">管理中心</span>
                        <span class="selected"></span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/inviteList']) ? 'active open' : ''}}">
                    <a href="{{url('admin/inviteList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-puzzle-piece"></i>
                        <span class="title">邀请管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/applyList', 'admin/applyDetail']) ? 'active open' : ''}}">
                    <a href="{{url('admin/applyList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-credit-card"></i>
                        <span class="title">提现管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['shop/goodsList', 'shop/addGoods', 'shop/editGoods']) ? 'active open' : ''}}">
                    <a href="{{url('shop/goodsList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-shopping-cart"></i>
                        <span class="title">商品管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['coupon/couponList', 'coupon/addCoupon']) ? 'active open' : ''}}">
                    <a href="{{url('coupon/couponList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-ticket"></i>
                        <span class="title">卡券管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['ticket/ticketList', 'ticket/replyTicket']) ? 'active open' : ''}}">
                    <a href="{{url('ticket/ticketList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-question-circle"></i>
                        <span class="title">工单管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/labelList', 'admin/addLabel', 'admin/editLabel']) ? 'active open' : ''}}">
                    <a href="{{url('admin/labelList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-sticky-note-o"></i>
                        <span class="title">标签管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/orderList']) ? 'active open' : ''}}">
                    <a href="{{url('admin/orderList')}}" class="nav-link nav-toggle">
                        <i class="fa fa-reorder"></i>
                        <span class="title">订单管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/articleList', 'admin/addArticle', 'admin/editArticle']) ? 'active open' : ''}}">
                    <a href="{{url('admin/articleList')}}" class="nav-link">
                        <i class="fa fa-file-word-o"></i>
                        <span class="title">文章管理</span>
                    </a>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/userOrderList', 'admin/userBalanceLogList', 'admin/userTrafficLogList', 'admin/userRebateList', 'admin/userBanLogList', 'admin/export', 'admin/userMonitor', 'admin/subscribeLog', 'admin/userOnlineIPList', 'admin/onlineIPMonitor']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-users"></i>
                        <span class="title">用户管理</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['admin/userList', 'admin/addUser', 'admin/editUser', 'admin/export', 'admin/userMonitor']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userList')}}" class="nav-link ">
                                <i class="fa fa-user"></i>
                                <span class="title">用户列表</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/userOnlineIPList']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userOnlineIPList')}}" class="nav-link">
                                <i class="icon-list"></i>
                                <span class="title">用户在线IP</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/onlineIPMonitor']) ? 'active open' : ''}}">
                            <a href="{{url('admin/onlineIPMonitor')}}" class="nav-link">
                                <i class="icon-list"></i>
                                <span class="title">在线IP监控</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/userBalanceLogList']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userBalanceLogList')}}" class="nav-link ">
                                <i class="fa fa-money"></i>
                                <span class="title">余额变动记录</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/userTrafficLogList']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userTrafficLogList')}}" class="nav-link ">
                                <i class="fa fa-area-chart"></i>
                                <span class="title">流量变动记录</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/userRebateList']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userRebateList')}}" class="nav-link ">
                                <i class="fa fa-credit-card"></i>
                                <span class="title">返利流水记录</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/userBanLogList']) ? 'active open' : ''}}">
                            <a href="{{url('admin/userBanLogList')}}" class="nav-link ">
                                <i class="fa fa-user-times"></i>
                                <span class="title">用户封禁记录</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['subscribe/subscribeList', 'subscribe/deviceList']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-list-alt"></i>
                        <span class="title">订阅管理</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['subscribe/subscribeList']) ? 'active open' : ''}}">
                            <a href="{{url('subscribe/subscribeList')}}" class="nav-link">
                                <i class="icon-list"></i>
                                <span class="title">订阅码列表</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['subscribe/deviceList']) ? 'active open' : ''}}">
                            <a href="{{url('subscribe/deviceList')}}" class="nav-link ">
                                <i class="fa fa-list"></i>
                                <span class="title">订阅设备列表</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/nodeList', 'admin/addNode', 'admin/editNode', 'admin/groupList', 'admin/addGroup', 'admin/editGroup', 'admin/nodeMonitor']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-list-alt"></i>
                        <span class="title">节点管理</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['admin/nodeList', 'admin/addNode', 'admin/editNode', 'admin/nodeMonitor']) ? 'active open' : ''}}">
                            <a href="{{url('admin/nodeList')}}" class="nav-link ">
                                <i class="fa fa-list"></i>
                                <span class="title">节点列表</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/groupList', 'admin/addGroup', 'admin/editGroup']) ? 'active open' : ''}}">
                            <a href="{{url('admin/groupList')}}" class="nav-link ">
                                <i class="fa fa-list-ul"></i>
                                <span class="title">节点分组</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['marketing/emailList', 'marketing/pushList']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-send-o"></i>
                        <span class="title">营销管理</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['marketing/emailList']) ? 'active open' : ''}}">
                            <a href="{{url('marketing/emailList')}}" class="nav-link ">
                                <i class="fa fa-inbox"></i>
                                <span class="title">邮件群发</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/decompile', 'admin/convert', 'admin/import', 'admin/trafficLog', 'admin/analysis', 'admin/emailLog', 'payment/callbackList', 'sensitiveWords/list', 'sensitiveWords/add']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-wrench"></i>
                        <span class="title">工具箱</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['admin/decompile']) ? 'active open' : ''}}">
                            <a href="{{url('admin/decompile')}}" class="nav-link">
                                <i class="icon-reload"></i>
                                <span class="title">反解析</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/convert']) ? 'active open' : ''}}">
                            <a href="{{url('admin/convert')}}" class="nav-link">
                                <i class="icon-refresh"></i>
                                <span class="title">格式转换</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/import']) ? 'active open' : ''}}">
                            <a href="{{url('admin/import')}}" class="nav-link">
                                <i class="icon-plus"></i>
                                <span class="title">数据导入</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/trafficLog']) ? 'active open' : ''}}">
                            <a href="{{url('admin/trafficLog')}}" class="nav-link">
                                <i class="fa fa-area-chart"></i>
                                <span class="title">流量日志</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/analysis']) ? 'active open' : ''}}">
                            <a href="{{url('admin/analysis')}}" class="nav-link">
                                <i class="fa fa-bar-chart"></i>
                                <span class="title">日志分析</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['sensitiveWords/list', 'sensitiveWords/add']) ? 'active open' : ''}}">
                            <a href="{{url('sensitiveWords/list')}}" class="nav-link">
                                <i class="fa fa-font"></i>
                                <span class="title">敏感词管理</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/emailLog']) ? 'active open' : ''}}">
                            <a href="{{url('admin/emailLog')}}" class="nav-link">
                                <i class="fa fa-envelope-o"></i>
                                <span class="title">邮件投递记录</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['payment/callbackList']) ? 'active open' : ''}}">
                            <a href="{{url('payment/callbackList')}}" class="nav-link">
                                <i class="fa fa-th"></i>
                                <span class="title">支付回调日志</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['logs']) ? 'active open' : ''}}">
                            <a href="{{url('logs')}}" class="nav-link" target="_blank">
                                <i class="fa fa-cubes"></i>
                                <span class="title">系统运行日志</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{in_array(Request::path(), ['admin/config', 'admin/addConfig', 'admin/system']) ? 'active open' : ''}}">
                    <a href="javascript:;" class="nav-link nav-toggle">
                        <i class="fa fa-gear"></i>
                        <span class="title">设置</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="nav-item {{in_array(Request::path(), ['admin/config']) ? 'active open' : ''}}">
                            <a href="{{url('admin/config')}}" class="nav-link ">
                                <i class="fa fa-cog"></i>
                                <span class="title">通用配置</span>
                            </a>
                        </li>
                        <li class="nav-item {{in_array(Request::path(), ['admin/system']) ? 'active open' : ''}}">
                            <a href="{{url('admin/system')}}" class="nav-link ">
                                <i class="fa fa-cogs"></i>
                                <span class="title">系统设置</span>
                            </a>
                        </li>
                    </ul>
                </li>
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
    <!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<div class="page-footer">
    <div class="page-footer-inner"> Copyright &copy; 2017 - 2019 <a href="https://github.com/ssrpanel/ssrpanel" target="_blank">SSRPanel</a> {{config('version.name')}} </div>
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
<script src="/js/layer/layer.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
@yield('script')
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/assets/layouts/layout4/scripts/layout.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-122312249-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-122312249-1');
</script>

</body>

</html>