@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <style>
        .page-header {
            border-bottom: 1px solid #8a8a8a;
            margin-bottom: 20px;
        }

        /*
         * Boxes
         */

        .box {
            display: block;
            padding: 0;
            min-height: 70px;
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 1.25rem;
        }

        .box > .box-icon > i,
        .box .box-content .box-text,
        .box .box-content .box-number {
            color: #FFF;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        }

        .box > .box-icon {
            border-radius: 1.25rem 0 0 1.25rem;
            display: block;
            float: left;
            height: 70px;
            width: 70px;
            text-align: center;
            font-size: 40px;
            line-height: 70px;
            background: rgba(0, 0, 0, 0.2);
        }

        .box .box-content {
            padding: 5px 10px;
            margin-left: 70px;
        }

        .box .box-content .box-text {
            display: block;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .box .box-content .box-number {
            display: block;
        }

        .box .box-content .progress {
            background: rgba(0, 0, 0, 0.2);
            margin: 5px -10px 5px -10px;
        }

        .box .box-content .progress .progress-bar {
            background-color: #FFF;
        }

        /*
         * Log Entry
         */

        .stack-content {
            color: #AE0E0E;
            font-family: consolas, Menlo, Courier, monospace;
            white-space: pre-line;
            font-size: .8rem;
        }

        /*
         * Colors: Badge & Infobox
         */

        .badge.badge-env,
        .badge.badge-level-all,
        .badge.badge-level-emergency,
        .badge.badge-level-alert,
        .badge.badge-level-critical,
        .badge.badge-level-error,
        .badge.badge-level-warning,
        .badge.badge-level-notice,
        .badge.badge-level-info,
        .badge.badge-level-debug,
        .badge.empty {
            color: #FFF;
        }

        .badge.badge-level-all,
        .box.level-all {
            background-color: {{ log_styler()->color('all') }};
        }

        .badge.badge-level-emergency,
        .box.level-emergency {
            background-color: {{ log_styler()->color('emergency') }};
        }

        .badge.badge-level-alert,
        .box.level-alert {
            background-color: {{ log_styler()->color('alert') }};
        }

        .badge.badge-level-critical,
        .box.level-critical {
            background-color: {{ log_styler()->color('critical') }};
        }

        .badge.badge-level-error,
        .box.level-error {
            background-color: {{ log_styler()->color('error') }};
        }

        .badge.badge-level-warning,
        .box.level-warning {
            background-color: {{ log_styler()->color('warning') }};
        }

        .badge.badge-level-notice,
        .box.level-notice {
            background-color: {{ log_styler()->color('notice') }};
        }

        .badge.badge-level-info,
        .box.level-info {
            background-color: {{ log_styler()->color('info') }};
        }

        .badge.badge-level-debug,
        .box.level-debug {
            background-color: {{ log_styler()->color('debug') }};
        }

        .badge.empty,
        .box.empty {
            background-color: {{ log_styler()->color('empty') }};
        }

        #entries {
            overflow-wrap: anywhere;
        }
    </style>
    @yield('css')
@endsection
@section('layout_content')
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega {{config('theme.navbar.inverse')}} {{config('theme.navbar.skin')}}" role="navigation">
        <div class="navbar-header">
            <div class="navbar-brand navbar-brand-center">
                <img src="{{sysConfig('website_logo')? asset(sysConfig('website_logo')) :'/assets/images/logo64.png'}}" class="navbar-brand-logo" alt="logo"/>
                <span class="navbar-brand-text hidden-xs-down"> {{sysConfig('website_name')}}</span>
            </div>
        </div>
        <div class="navbar-container container-fluid">
            <ul class="nav navbar-toolbar">
                <li class="nav-item {{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('log-viewer::dashboard') }}" role="button">
                        <i class="wb-pie-chart"></i> @lang('Dashboard')
                    </a>
                </li>
                <li class="nav-item {{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('log-viewer::logs.list') }}" role="button">
                        <i class="wb-inbox"></i> @lang('Logs')
                    </a>
                </li>
            </ul>
            <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                <li class="nav-item {{ Route::is('admin.index') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.index') }}" role="button">
                        <i class="wb-dashboard"></i> @lang('user.menu.admin_dashboard')
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="page ml-0">
        <!--[if lt IE 8]><p class="browserupgrade">{{trans('common.update_browser.0')}}<strong>{{trans('common.update_browser.1')}}</strong>
{{trans('common.update_browser.2')}}<a href="https://browsehappy.com/" target="_blank">{{trans('common.update_browser.3')}}</a>{{trans('common.update_browser.4')}}</p><![endif]-->
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>
    <footer class="site-footer ml-0">
        <div class="site-footer-legal">© 2017 -
            2023<a href="https://github.com/ProxyPanel/ProxyPanel" target="_blank">{{config('version.name')}} </a> {{__('All rights reserved.')}}
        </div>
        <div class="site-footer-right">
            Base on <a href="https://github.com/ARCANEDEV/LogViewer" target="_blank">LogViewer</a> 🚀
            Version:<code> {{ log_viewer()->version() }} </code>
        </div>
    </footer>
    @yield('modals')
@endsection
@section('layout_javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js" integrity="sha512-GMGzUEevhWh8Tc/njS0bDpwgxdCJLQBWG3Z2Ct+JGOpVnEmjvNx6ts4v6A2XJf1HOrtOsfhv3hBKpK9kE5z8AQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @yield('javascript')
@endsection
