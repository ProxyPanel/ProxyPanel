@php use App\Models\ReferralLog, App\Models\Order; @endphp
@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
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
                    <li class="nav-item hidden-sm-down" id="toggleFullscreen">
                        <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                            <span class="sr-only">{{ trans('common.toggle_action', ['action' => trans('common.function.fullscreen')]) }}</span>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    @include('user.components.notification')
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" data-animation="scale-up" href="javascript:void(0)" role="button" aria-expanded="false">
                            <span class="icon font-size-16 wb-globe"></span>
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
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" data-animation="scale-up" href="javascript:void(0)" role="button" aria-expanded="false">
                            <span class="icon wb-payment"></span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @foreach (config('common.currency') as $country_code => $currency)
                                <a class="dropdown-item" href="{{ route('currency', ['code' => $currency['code']]) }}" role="menuitem">
                                    <i class="fi fi-{{ $country_code }}" aria-hidden="true"></i>
                                    <span style="padding: inherit;">{{ $currency['symbol'] . ' ' . $currency['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link navbar-avatar" data-animation="scale-up" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
                            <span class="avatar avatar-online">
                                <img data-uid="{{ auth()->user()->id }}" data-qq="{{ auth()->user()->qq }}" data-username="{{ auth()->user()->username }}"
                                     src="" alt="{{ trans('common.avatar') }}" loading="lazy" />
                            </span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @can('admin.index')
                                <a class="dropdown-item" href="{{ route('admin.index') }}" role="menuitem">
                                    <i class="icon wb-dashboard" aria-hidden="true"></i>
                                    {{ trans('user.menu.admin_dashboard') }}
                                </a>
                            @endcan
                            <a class="dropdown-item" href="{{ route('profile.show') }}" role="menuitem">
                                <i class="icon wb-settings" aria-hidden="true"></i>
                                {{ trans('user.menu.profile') }}
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
        $ticket_count = auth()->user()->tickets()->where('status', '<>', 2)->count();
        $activePlan = Order::uid()->whereStatus(2)->exists();
    @endphp
    <x-ui.site.menubar :items="[
        ['icon' => 'wb-home', 'route' => 'home', 'text' => trans('user.menu.home')],
        ['icon' => 'wb-shopping-cart', 'route' => 'shop.index', 'active' => 'shop.*', 'text' => trans('user.menu.shop')],
        ['icon' => 'wb-cloud', 'route' => 'node.index', 'active' => 'node.*', 'text' => trans('user.menu.nodes')],
        ['icon' => 'wb-info-circle', 'route' => 'knowledge.index', 'active' => 'knowledge.*', 'text' => trans('user.menu.help')],
        ['icon' => 'wb-settings', 'route' => 'profile.show', 'active' => 'profile.*', 'text' => trans('user.menu.profile')],
        [
            'icon' => 'wb-chat-working',
            'route' => 'ticket.index',
            'active' => 'ticket.*',
            'text' => trans('user.menu.tickets'),
            'badge' => $ticket_count,
        ],
        ['icon' => 'wb-bookmark', 'route' => 'invoice.index', 'active' => 'invoice.*', 'text' => trans('user.menu.invoices')],
        [
            'icon' => 'wb-extension',
            'route' => 'invite.index',
            'active' => 'invite.*',
            'text' => trans('user.menu.invites'),
            'show' => sysConfig('is_invite_register') && ReferralLog::uid()->exists(),
        ],
        [
            'icon' => 'wb-star-outline',
            'route' => 'referral.index',
            'active' => 'referral.*',
            'text' => trans('user.menu.promotion'),
            'show' => sysConfig('referral_status') && $activePlan,
        ],
        '',
        ['icon' => 'wb-dashboard', 'route' => 'admin.index', 'text' => trans('user.menu.admin_dashboard'), 'can' => 'admin.index'],
        ['icon' => 'wb-power', 'route' => 'logout', 'text' => trans('auth.logout')],
    ]" />
    <div class="page">
        @yield('content')
    </div>
    <footer class="site-footer">
        <div class="site-footer-legal">
            © 2017 - {{ now()->year }} <a href="https://github.com/ProxyPanel/ProxyPanel" target="_blank">{{ config('version.name') }}
                {{ __('All rights reserved.') }}</a>
            🚀 Version: <code> {{ config('version.number') }} </code>
        </div>
        <div class="site-footer-right">
            <a href="{{ sysConfig('website_url') }}" target="_blank">{{ sysConfig('website_name') }}</a> 🈺
        </div>
    </footer>
    @if (Session::has('admin'))
        <div class="panel panel-bordered w-300 bg-grey-200" style="position:fixed;right:20px;bottom:0;">
            <div class="panel-body text-right">
                <h5>{{ trans('user.current_role') }}：{{ auth()->user()->username }}</h5>
                <button class="btn btn-danger btn-block mt-20" id="return_to_admin" type="button">
                    {{ trans('common.back_to', ['page' => trans('user.menu.admin_dashboard')]) }}
                </button>
            </div>
        </div>
    @endif
@endsection
@section('layout_javascript')
    <script src="/assets/custom/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="/assets/js/config/common.js"></script>
    <script>
        // 全局变量，用于common.js
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const TRANS = {
            warning: '{{ trans('common.warning') }}',
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

        @if (Session::has('admin'))
            $("#return_to_admin").click(function() {
                ajaxPost('{{ route('switch') }}', {}, {
                    success: function(ret) {
                        handleResponse(ret, {
                            redirectUrl: '{{ route('admin.index') }}'
                        });
                    }
                });
            });
        @endif
    </script>
    @yield('javascript')

    <!-- 统计 -->
    {!! sysConfig('website_statistics_code') !!}
    <!-- 客服 -->
    {!! sysConfig('website_customer_service_code') !!}
@endsection
