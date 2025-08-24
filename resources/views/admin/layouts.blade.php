@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    @yield('css')
@endsection
@section('body_class', 'dashboard')
@section('layout_content')
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega {{ config('theme.navbar.inverse') }} {{ config('theme.navbar.skin') }}"
         role="navigation">
        <div class="navbar-header">
            <button class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar" type="button">
                <span class="sr-only">{{ trans('common.toggle_action', ['action' => trans('common.function.navigation')]) }}</span>
                <span class="hamburger-bar"></span>
            </button>
            <button class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse" type="button">
                <i class="icon wb-more-horizontal" aria-hidden="true"></i>
            </button>
            <div class="navbar-brand navbar-brand-center">
                <img class="navbar-brand-logo" src="{{ sysConfig('website_logo') ? asset(sysConfig('website_logo')) : '/assets/images/logo.png' }}"
                     alt="logo" />
                <span class="navbar-brand-text hidden-xs-down"> {{ sysConfig('website_name') }}</span>
            </div>
        </div>
        <div class="navbar-container container-fluid">
            <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
                <ul class="nav navbar-toolbar">
                    <li class="nav-item hidden-float" id="toggleMenubar">
                        <a class="nav-link" data-toggle="menubar" href="#" role="button">
                            <i class="icon hamburger hamburger-arrow-left">
                                <span class="sr-only">{{ trans('common.toggle_action', ['action' => trans('common.function.menubar')]) }}</span>
                                <span class="hamburger-bar"></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item hidden-sm-down">
                        <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                            <span class="sr-only">{{ trans('common.toggle_action', ['action' => trans('common.function.fullscreen')]) }}</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)" role="button">
                            <span class="icon wb-globe"></span>
                            <span class="icon wb-chevron-down-mini"></span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @foreach (config('common.language') as $key => $value)
                                <a class="dropdown-item" href="{{ route('lang', ['locale' => $key]) }}" role="menuitem">
                                    <i class="fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                                    <span style="padding: inherit;">{{ $value[0] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                </ul>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link navbar-avatar" data-toggle="dropdown" data-animation="scale-up" href="#" role="button" aria-expanded="false">
                            <span class="avatar avatar-online">
                                <img data-uid="{{ auth()->user()->id }}" data-qq="{{ auth()->user()->qq }}" data-username="{{ auth()->user()->username }}"
                                     src="" alt="{{ trans('common.avatar') }}" loading="lazy" />
                                <i></i>
                            </span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="/" role="menuitem">
                                <i class="icon wb-settings" aria-hidden="true"></i>
                                {{ trans('admin.user_dashboard') }}
                            </a>
                            <div class="dropdown-divider" role="presentation"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}" role="menuitem">
                                <i class="icon wb-power" aria-hidden="true"></i>
                                {{ trans('auth.logout') }}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @php
        $apply_count = auth()->user()->can('admin.aff.index')
            ? Cache::rememberForever('open_referral_apply_count', static function () {
                return App\Models\ReferralApply::whereStatus(0)->count();
            })
            : null;
        $ticket_count = auth()->user()->can('admin.ticket.index')
            ? Cache::rememberForever('open_ticket_count', static function () {
                return App\Models\Ticket::whereStatus(0)->count();
            })
            : null;
        $env = config('app.env') === 'local' && config('app.debug');
    @endphp
    <x-ui.site.menubar :items="[
        ['icon' => 'wb-dashboard', 'route' => 'admin.index', 'text' => trans('admin.menu.dashboard'), 'can' => 'admin.index'],
        [
            'icon' => 'wb-user',
            'text' => trans('admin.menu.user.attribute'),
            'active' => ['admin.user.*', 'admin.log.credit', 'admin.subscribe.*'],
            'can' => ['admin.user.index', 'admin.user.group.index', 'admin.log.credit', 'admin.subscribe.index'],
            'children' => [
                [
                    'route' => 'admin.user.index',
                    'active' => ['admin.user.index', 'admin.user.edit', 'admin.user.monitor', 'admin.user.online', 'admin.user.online', 'admin.user.export'],
                    'text' => trans('admin.menu.user.list'),
                    'can' => 'admin.user.index',
                ],
                ['route' => 'admin.user.oauth', 'active' => 'admin.user.oauth', 'text' => trans('admin.menu.user.oauth'), 'can' => 'admin.user.oauth'],
                [
                    'route' => 'admin.user.group.index',
                    'active' => 'admin.user.group.*',
                    'text' => trans('admin.menu.user.group'),
                    'can' => 'admin.user.group.index',
                ],
                ['route' => 'admin.log.credit', 'active' => 'admin.log.credit', 'text' => trans('admin.menu.user.credit_log'), 'can' => 'admin.log.credit'],
                [
                    'route' => 'admin.subscribe.index',
                    'active' => 'admin.subscribe.*',
                    'text' => trans('admin.menu.user.subscribe'),
                    'can' => 'admin.subscribe.index',
                ],
            ],
        ],
        [
            'icon' => 'wb-users',
            'text' => trans('admin.menu.rbac.attribute'),
            'active' => ['admin.permission.*', 'admin.role.*'],
            'can' => ['admin.permission.index', 'admin.role.index'],
            'children' => [
                [
                    'route' => 'admin.permission.index',
                    'active' => 'admin.permission.*',
                    'text' => trans('admin.menu.rbac.permission'),
                    'can' => 'admin.permission.index',
                ],
                ['route' => 'admin.role.index', 'active' => 'admin.role.*', 'text' => trans('admin.menu.rbac.role'), 'can' => 'admin.role.index'],
            ],
        ],
        [
            'icon' => 'wb-chat-working',
            'text' => trans('admin.menu.customer_service.attribute'),
            'active' => ['admin.ticket.*', 'admin.article.*', 'admin.marketing.*'],
            'can' => ['admin.ticket.index', 'admin.article.index', 'admin.marketing.index'],
            'badge' => $ticket_count,
            'children' => [
                [
                    'route' => 'admin.ticket.index',
                    'active' => 'admin.ticket.*',
                    'text' => trans('admin.menu.customer_service.ticket'),
                    'can' => 'admin.ticket.index',
                    'badge' => $ticket_count,
                ],
                [
                    'route' => 'admin.article.index',
                    'active' => 'admin.article.*',
                    'text' => trans('admin.menu.customer_service.article'),
                    'can' => 'admin.article.index',
                ],
                [
                    'route' => 'admin.marketing.index',
                    'active' => 'admin.marketing.*',
                    'text' => trans('admin.menu.customer_service.marketing'),
                    'can' => 'admin.marketing.index',
                ],
            ],
        ],
        [
            'icon' => 'wb-cloud',
            'text' => trans('admin.menu.node.attribute'),
            'active' => ['admin.node.*', 'admin.node.auth.*', 'admin.node.cert.*'],
            'can' => ['admin.node.index', 'admin.node.auth.index', 'admin.node.cert.index'],
            'children' => [
                [
                    'route' => 'admin.node.index',
                    'active' => ['admin.node.index', 'admin.node.create', 'admin.node.edit'],
                    'text' => trans('admin.menu.node.list'),
                    'can' => 'admin.node.index',
                ],
                [
                    'route' => 'admin.node.auth.index',
                    'active' => 'admin.node.auth.*',
                    'text' => trans('admin.menu.node.auth'),
                    'can' => 'admin.node.auth.index',
                ],
                [
                    'route' => 'admin.node.cert.index',
                    'active' => 'admin.node.cert.*',
                    'text' => trans('admin.menu.node.cert'),
                    'can' => 'admin.node.cert.index',
                ],
            ],
        ],
        [
            'icon' => 'wb-eye',
            'text' => trans('admin.menu.rule.attribute'),
            'active' => 'admin.rule.*',
            'can' => ['admin.rule.index', 'admin.rule.group.index', 'admin.rule.log'],
            'children' => [
                ['route' => 'admin.rule.index', 'active' => 'admin.rule.index', 'text' => trans('admin.menu.rule.list'), 'can' => 'admin.rule.index'],
                [
                    'route' => 'admin.rule.group.index',
                    'active' => 'admin.rule.group.*',
                    'text' => trans('admin.menu.rule.group'),
                    'can' => 'admin.rule.group.index',
                ],
                ['route' => 'admin.rule.log', 'active' => 'admin.rule.log', 'text' => trans('admin.menu.rule.trigger'), 'can' => 'admin.rule.log'],
            ],
        ],
        [
            'icon' => 'wb-shopping-cart',
            'text' => trans('admin.menu.shop.attribute'),
            'active' => ['admin.goods.*', 'admin.coupon.*', 'admin.order'],
            'can' => ['admin.goods.index', 'admin.coupon.index', 'admin.order'],
            'children' => [
                ['route' => 'admin.goods.index', 'active' => 'admin.goods.*', 'text' => trans('admin.menu.shop.goods'), 'can' => 'admin.goods.index'],
                ['route' => 'admin.coupon.index', 'active' => 'admin.coupon.*', 'text' => trans('admin.menu.shop.coupon'), 'can' => 'admin.coupon.index'],
                ['route' => 'admin.order', 'active' => 'admin.order', 'text' => trans('admin.menu.shop.order'), 'can' => 'admin.order'],
            ],
        ],
        [
            'icon' => 'wb-thumb-up',
            'text' => trans('admin.menu.promotion.attribute'),
            'active' => ['admin.invite.*', 'admin.aff.*'],
            'can' => ['admin.invite.index', 'admin.aff.index', 'admin.aff.rebate'],
            'badge' => $apply_count,
            'children' => [
                ['route' => 'admin.invite.index', 'active' => 'admin.invite.*', 'text' => trans('admin.menu.promotion.invite'), 'can' => 'admin.invite.index'],
                [
                    'route' => 'admin.aff.index',
                    'active' => ['admin.aff.index', 'admin.aff.detail'],
                    'text' => trans('admin.menu.promotion.withdraw'),
                    'can' => 'admin.aff.index',
                    'badge' => $apply_count,
                ],
                [
                    'route' => 'admin.aff.rebate',
                    'active' => 'admin.aff.rebate',
                    'text' => trans('admin.menu.promotion.rebate_flow'),
                    'can' => 'admin.aff.rebate',
                ],
            ],
        ],
        [
            'icon' => 'wb-stats-bars',
            'text' => trans('admin.menu.analysis.attribute'),
            'active' => 'admin.report.*',
            'can' => ['admin.report.accounting', 'admin.report.userAnalysis', 'admin.report.nodeAnalysis', 'admin.report.siteAnalysis'],
            'children' => [
                [
                    'route' => 'admin.report.accounting',
                    'active' => 'admin.report.accounting',
                    'text' => trans('admin.menu.analysis.accounting'),
                    'can' => 'admin.report.accounting',
                ],
                [
                    'route' => 'admin.report.userAnalysis',
                    'active' => 'admin.report.userAnalysis',
                    'text' => trans('admin.menu.analysis.user_flow'),
                    'can' => 'admin.report.userAnalysis',
                ],
                [
                    'route' => 'admin.report.nodeAnalysis',
                    'active' => 'admin.report.nodeAnalysis',
                    'text' => trans('admin.menu.analysis.node_flow'),
                    'can' => 'admin.report.nodeAnalysis',
                ],
                [
                    'route' => 'admin.report.siteAnalysis',
                    'active' => 'admin.report.siteAnalysis',
                    'text' => trans('admin.menu.analysis.site_flow'),
                    'can' => 'admin.report.siteAnalysis',
                ],
            ],
        ],
        [
            'icon' => 'wb-calendar',
            'text' => trans('admin.menu.log.attribute'),
            'active' => [
                'admin.log.traffic',
                'admin.log.flow',
                'admin.log.ban',
                'admin.log.ip',
                'admin.log.online',
                'admin.log.notify',
                'admin.payment.callback',
            ],
            'can' => ['admin.log.traffic', 'admin.log.flow', 'admin.log.ban', 'admin.log.ip', 'admin.log.online', 'admin.log.notify', 'admin.payment.callback'],
            'children' => [
                ['route' => 'admin.log.traffic', 'active' => 'admin.log.traffic', 'text' => trans('admin.menu.log.traffic'), 'can' => 'admin.log.traffic'],
                ['route' => 'admin.log.flow', 'active' => 'admin.log.flow', 'text' => trans('admin.menu.log.traffic_flow'), 'can' => 'admin.log.flow'],
                ['route' => 'admin.log.ban', 'active' => 'admin.log.ban', 'text' => trans('admin.menu.log.service_ban'), 'can' => 'admin.log.ban'],
                ['route' => 'admin.log.ip', 'active' => 'admin.log.ip', 'text' => trans('admin.menu.log.online_logs'), 'can' => 'admin.log.ip'],
                ['route' => 'admin.log.online', 'active' => 'admin.log.online', 'text' => trans('admin.menu.log.online_monitor'), 'can' => 'admin.log.online'],
                ['route' => 'admin.log.notify', 'active' => 'admin.log.notify', 'text' => trans('admin.menu.log.notify'), 'can' => 'admin.log.notify'],
                [
                    'route' => 'admin.payment.callback',
                    'active' => 'admin.payment.callback',
                    'text' => trans('admin.menu.log.payment_callback'),
                    'can' => 'admin.payment.callback',
                ],
                ['route' => 'log-viewer::dashboard', 'text' => trans('admin.menu.log.system'), 'can' => 'log-viewer'],
                ['route' => 'horizon.index', 'text' => 'Horizon', 'can' => 'viewHorizon'],
                ['route' => 'telescope', 'text' => 'Telescope', 'can' => 'viewTelescope', 'show' => $env],
            ],
        ],
        [
            'icon' => 'wb-briefcase',
            'text' => trans('admin.menu.tools.attribute'),
            'active' => 'admin.tools.*',
            'can' => ['admin.tools.decompile', 'admin.tools.convert', 'admin.tools.import', 'admin.tools.analysis'],
            'children' => [
                [
                    'route' => 'admin.tools.decompile',
                    'active' => 'admin.tools.decompile',
                    'text' => trans('admin.menu.tools.decompile'),
                    'can' => 'admin.tools.decompile',
                ],
                [
                    'route' => 'admin.tools.convert',
                    'active' => 'admin.tools.convert',
                    'text' => trans('admin.menu.tools.convert'),
                    'can' => 'admin.tools.convert',
                ],
                ['route' => 'admin.tools.import', 'active' => 'admin.tools.import', 'text' => trans('admin.menu.tools.import'), 'can' => 'admin.tools.import'],
                [
                    'route' => 'admin.tools.analysis',
                    'active' => 'admin.tools.analysis',
                    'text' => trans('admin.menu.tools.analysis'),
                    'can' => 'admin.tools.analysis',
                ],
            ],
        ],
        [
            'icon' => 'wb-settings',
            'text' => trans('admin.menu.setting.attribute'),
            'active' => ['admin.config.*', 'admin.system.index'],
            'can' => ['admin.config.filter.index', 'admin.config.index', 'admin.system.index'],
            'children' => [
                [
                    'route' => 'admin.config.filter.index',
                    'active' => 'admin.config.filter.index',
                    'text' => trans('admin.menu.setting.email_suffix'),
                    'can' => 'admin.config.filter.index',
                ],
                [
                    'route' => 'admin.config.index',
                    'active' => 'admin.config.index',
                    'text' => trans('admin.menu.setting.universal'),
                    'can' => 'admin.config.index',
                ],
                [
                    'route' => 'admin.system.index',
                    'active' => 'admin.system.index',
                    'text' => trans('admin.menu.setting.system'),
                    'can' => 'admin.system.index',
                ],
            ],
        ],
    ]" />

    <div class="page">
        @yield('content')
    </div>
@endsection
@section('layout_javascript')
    <script src="/assets/custom/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        // 全局变量，用于common.js
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const TRANS = {
            warning: '{{ trans('common.warning') }}',
            confirm: {
                delete: '{{ trans('admin.confirm.delete', ['attribute' => '{attribute}', 'name' => '{name}']) }}'
            },
            btn: {
                close: '{{ trans('common.close') }}',
                confirm: '{{ trans('common.confirm') }}'
            },
            copy: {
                success: '{{ trans('common.copy.success') }}',
                failed: '{{ trans('common.copy.failed') }}'
            }
        };

        const $buoop = {
            required: {
                e: 11,
                f: -6,
                o: -6,
                s: -6,
                c: -6
            },
            insecure: true,
            unsupported: true,
            api: 2024.07
        };

        function $buo_f() {
            const e = document.createElement("script");
            e.src = "//browser-update.org/update.min.js";
            document.body.appendChild(e);
        }

        try {
            document.addEventListener("DOMContentLoaded", $buo_f, false);
        } catch (e) {
            window.attachEvent("onload", $buo_f);
        }
    </script>
    <script src="/assets/js/config/common.js"></script>
    <script src="/assets/js/config/admin.js"></script>
    @yield('javascript')
@endsection
