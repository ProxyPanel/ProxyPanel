<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>{{trans('home.free_invite_codes_title')}}</title>
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
                @if(\App\Components\Helpers::systemConfig()['website_logo'])
                    <a href="{{url('/')}}"> <img src="{{\App\Components\Helpers::systemConfig()['website_logo']}}" alt="" style="width:300px; height:90px;"/> </a>
                @else
                    <a href="{{url('/')}}"> <img src="/assets/images/logo.png" alt="logo" class="logo-default" /> </a>
                @endif
            </div>
            <!-- END LOGO -->
            <!-- BEGIN PAGE TOP -->
            <div class="page-top">
                <div class="top-menu">
                    <ul class="nav navbar-nav pull-right"></ul>
                </div>
            </div>
        </div>
        <!-- END HEADER INNER -->
    </div>
    <!-- END HEADER -->
    <!-- BEGIN HEADER & CONTENT DIVIDER -->
    <div class="clearfix"> </div>
    <!-- END HEADER & CONTENT DIVIDER -->
    <!-- BEGIN CONTAINER -->
    <div class="page-container">

        <!-- BEGIN CONTENT BODY -->
        <div class="page-content" style="padding-top:0;">
            <!-- BEGIN PAGE BASE CONTENT -->
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-pane active">
                        <div class="portlet light bordered">
                            <div class="portlet-title">
                                <div class="caption">
                                    <span class="caption-subject font-dark bold">{{trans('home.free_invite_codes_title')}}</span>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light">
                                        @if(\App\Components\Helpers::systemConfig()['is_invite_register'])
                                            @if(\App\Components\Helpers::systemConfig()['is_free_code'])
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;"> {{trans('home.invite_code_table_name')}} </th>
                                                        <th style="text-align: center;"> {{trans('home.invite_code_table_date')}} </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if($inviteList->isEmpty())
                                                        <tr>
                                                            <td colspan="2" style="text-align: center;">{{trans('home.invite_code_table_none_codes')}}</td>
                                                        </tr>
                                                    @else
                                                        @foreach($inviteList as $key => $invite)
                                                            <tr>
                                                                <td style="width: 50%; text-align: center;"> <a href="{{url('register?code='.$invite->code)}}" target="_blank">{{$invite->code}}</a> </td>
                                                                <td style="width: 50%; text-align: center;"> {{$invite->dateline}} </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @else
                                                <tbody>
                                                    <tr>
                                                        <td colspan="2" style="text-align: center;">{{trans('home.invite_code_table_none_codes')}}</td>
                                                    </tr>
                                                </tbody>
                                            @endif
                                        @else
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" style="text-align: center;">{{trans('home.no_need_invite_codes')}}</td>
                                                </tr>
                                            </tbody>
                                        @endif
                                    </table>
                                </div>
                                @if(\App\Components\Helpers::systemConfig()['is_invite_register'] && \App\Components\Helpers::systemConfig()['is_free_code'])
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                                {{ $inviteList->links() }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BASE CONTENT -->
        </div>
        <!-- END CONTENT BODY -->

    <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->
    <!--[if lt IE 9]>
    <script src="/assets/global/plugins/respond.min.js"></script>
    <script src="/assets/global/plugins/excanvas.min.js"></script>
    <script src="/assets/global/plugins/ie8.fix.min.js"></script>
    <![endif]-->
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
    <script src="/assets/layouts/layout4/scripts/layout.min.js" type="text/javascript"></script>
    <!-- 统计 -->
    {!! \App\Components\Helpers::systemConfig()['website_analytics'] !!}
    <!-- 客服 -->
    {!! \App\Components\Helpers::systemConfig()['website_customer_service'] !!}
</body>

</html>