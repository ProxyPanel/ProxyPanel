@php use App\Models\ReferralLog, App\Models\Order; @endphp
@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    @yield('css')
@endsection
@section('body_class', 'dashboard')
@section('layout_content')
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega {{config('theme.navbar.inverse')}} {{config('theme.navbar.skin')}}" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided"
                    data-toggle="menubar">
                <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.navigation')])}}</span>
                <span class="hamburger-bar"></span>
            </button>
            <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse"
                    data-toggle="collapse">
                <i class="icon wb-more-horizontal" aria-hidden="true"></i>
            </button>
            <div class="navbar-brand navbar-brand-center">
                <img src="{{sysConfig('website_logo') ? asset(sysConfig('website_logo')) : '/assets/images/logo64.png'}}"
                     class="navbar-brand-logo" alt="logo"/>
                <span class="navbar-brand-text hidden-xs-down"> {{sysConfig('website_name')}}</span>
            </div>
        </div>
        <div class="navbar-container container-fluid">
            <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
                <ul class="nav navbar-toolbar">
                    <li class="nav-item hidden-float" id="toggleMenubar">
                        <a class="nav-link" data-toggle="menubar" href="#" role="button">
                            <i class="icon hamburger hamburger-arrow-left">
                                <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.menubar')])}}</span>
                                <span class="hamburger-bar"></span>
                            </i>
                        </a>
                    </li>
                    <li class="nav-item hidden-sm-down" id="toggleFullscreen">
                        <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                            <span class="sr-only">{{trans('common.toggle_action', ['action' => trans('common.function.fullscreen')])}}</span>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    @include('user.components.notification')
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" data-animation="scale-up"
                           aria-expanded="false" role="button">
                            <span class="icon font-size-16 wb-globe"></span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @foreach (config('common.language') as $key => $value)
                                <a class="dropdown-item" href="{{route('lang', ['locale' => $key])}}" role="menuitem">
                                    <i class="fi fi-{{$value[1]}}" aria-hidden="true"></i>
                                    <span style="padding: inherit;">{{$value[0]}}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" data-animation="scale-up"
                           aria-expanded="false" role="button">
                            <span class="icon wb-payment"></span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @foreach (config('common.currency') as $country_code => $currency)
                                <a class="dropdown-item" href="{{route('currency', ['code' => $currency['code']])}}" role="menuitem">
                                    <i class="fi fi-{{$country_code}}" aria-hidden="true"></i>
                                    <span style="padding: inherit;">{{$currency['symbol'].' '.$currency['name']}}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" aria-expanded="false" class="nav-link navbar-avatar" data-animation="scale-up"
                           data-toggle="dropdown" role="button">
                        <span class="avatar avatar-online">
                            <img src="{{Auth::getUser()->avatar}}" alt="{{trans('common.avatar')}}"/><i></i>
                        </span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            @can('admin.index')
                                <a href="{{route('admin.index')}}" class="dropdown-item" role="menuitem">
                                    <i class="icon wb-dashboard" aria-hidden="true"></i>
                                    {{trans('user.menu.admin_dashboard')}}
                                </a>
                            @endcan
                            <a href="{{route('profile')}}" class="dropdown-item" role="menuitem">
                                <i class="icon wb-settings" aria-hidden="true"></i>
                                {{trans('user.menu.profile')}}
                            </a>
                            <div class="dropdown-divider" role="presentation"></div>
                            <a href="{{route('logout')}}" class="dropdown-item" role="menuitem">
                                <i class="icon wb-power" aria-hidden="true"></i>
                                {{trans('auth.logout')}}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="site-menubar {{config('theme.sidebar')}}">
        <div class="site-menubar-body">
            <ul class="site-menu" data-plugin="menu">
                <li class="site-menu-item {{request()->routeIs('home', 'profile' ,'article') ? 'active open' : ''}}">
                    <a href="{{route('home')}}">
                        <i class="site-menu-icon wb-home" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.home')}}</span>
                    </a>
                </li>
                <li class="site-menu-item {{request()->routeIs('shop', 'buy', 'orderDetail') ? 'active open' : ''}}">
                    <a href="{{route('shop')}}">
                        <i class="site-menu-icon wb-shopping-cart" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.shop')}}</span>
                    </a>
                </li>
                <li class="site-menu-item {{request()->routeIs('node') ? 'active open' : ''}}">
                    <a href="{{route('node')}}">
                        <i class="site-menu-icon wb-cloud" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.nodes')}}</span>
                    </a>
                </li>
                <li class="site-menu-item {{request()->routeIs('knowledge') ? 'active open' : ''}}">
                    <a href="{{route('knowledge')}}">
                        <i class="site-menu-icon wb-info-circle" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.helps')}}</span>
                    </a>
                </li>
                <li class="site-menu-item {{request()->routeIs('profile') ? 'active open' : ''}}">
                    <a href="{{route('profile')}}">
                        <i class="site-menu-icon wb-settings" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.profile')}}</span>
                    </a>
                </li>
                @php
                    $openTicket = auth()->user()->tickets()->where('status','<>',2)->count()
                @endphp
                <li class="site-menu-item {{request()->routeIs('ticket', 'replyTicket') ? 'active open' : ''}}">
                    <a href="{{route('ticket')}}">
                        <i class="site-menu-icon wb-chat-working" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('user.menu.tickets')}}</span>
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
                        <span class="site-menu-title">{{trans('user.menu.invoices')}}</span>
                    </a>
                </li>
                @if(ReferralLog::uid()->exists() || Order::uid()->whereStatus(2)->exists())
                    @if(sysConfig('is_invite_register'))
                        <li class="site-menu-item {{request()->routeIs('invite') ? 'active open' : ''}}">
                            <a href="{{route('invite')}}">
                                <i class="site-menu-icon wb-extension" aria-hidden="true"></i>
                                <span class="site-menu-title">{{trans('user.menu.invites')}}</span>
                            </a>
                        </li>
                    @endif
                    @if((sysConfig('referral_status')))
                        <li class="site-menu-item {{request()->routeIs('commission') ? 'active open' : ''}}">
                            <a href="{{route('commission')}}">
                                <i class="site-menu-icon wb-star-outline" aria-hidden="true"></i>
                                <span class="site-menu-title">{{trans('user.menu.referrals')}}</span>
                            </a>
                        </li>
                    @endif
                @endif
                <hr>
                @can('admin.index')
                    <li class="site-menu-item">
                        <a href="{{route('admin.index')}}">
                            <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
                            <span class="site-menu-title">{{trans('user.menu.admin_dashboard')}}</span>
                        </a>
                    </li>
                @endcan
                <li class="site-menu-item">
                    <a href="{{route('logout')}}">
                        <i class="site-menu-icon wb-power" aria-hidden="true"></i>
                        <span class="site-menu-title">{{trans('auth.logout')}}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="page">
        <!--[if lt IE 8]><p class="browserupgrade">{{trans('common.update_browser.0')}}<strong>{{trans('common.update_browser.1')}}</strong>
{{trans('common.update_browser.2')}}<a href="https://browsehappy.com/" target="_blank">{{trans('common.update_browser.3')}}</a>{{trans('common.update_browser.4')}}</p><![endif]-->
        @yield('content')
    </div>
    <footer class="site-footer">
        <div class="site-footer-legal">
            © 2017 - 2024 <a href="https://github.com/ProxyPanel/ProxyPanel" target="_blank">{{config('version.name')}} {{__('All rights reserved.')}}</a>
            🚀 Version: <code> {{config('version.number')}} </code>
        </div>
        <div class="site-footer-right">
            <a href="{{sysConfig('website_url')}}" target="_blank">{{sysConfig('website_name')}}</a> 🈺
        </div>
    </footer>
    @if(Session::has("admin"))
        <div class="panel panel-bordered w-300 bg-grey-200" style="position:fixed;right:20px;bottom:0;">
            <div class="panel-body text-right">
                <h5>{{trans('user.current_role')}}：{{Auth::getUser()->username}}</h5>
                <button type="button" class="btn btn-danger btn-block mt-20" id="return_to_admin">
                    {{ trans('common.back_to', ['page' => trans('user.menu.admin_dashboard')]) }}
                </button>
            </div>
        </div>
        @endif
@endsection
@section('layout_javascript')
    <!--[if lt IE 11]>
        <script src="/assets/custom/sweetalert2/polyfill.min.js"></script>
        <![endif]-->
        <script src="/assets/custom/sweetalert2/sweetalert2.all.min.js"></script>
        @yield('javascript')
        @if(Session::has('admin'))
            <script>
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
        @endsection
