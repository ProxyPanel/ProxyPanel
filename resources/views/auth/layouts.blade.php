@extends('_layout')
@section('title', sysConfig('website_name'))
@section('layout_css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/login-v3.min.css" rel="stylesheet">
    @yield('css')
    @if (config('version.ads'))
        <style>
            @media (max-width: 768px) {
                #ad img {
                    width: 40vw;
                }
            }

            @media (min-width: 768px) {
                #ad img {
                    width: 30vw;
                }
            }

            @media (min-width: 1200px) {
                #ad img {
                    width: 20vw;
                }
            }

            #ad {
                position: fixed;
                z-index: 9999;
                left: 0;
                bottom: 0;
                background-color: rgba(255, 255, 255, 0.80);
            }

            #ad img {
                max-width: 300px;
            }

            #ad > button {
                position: absolute;
                right: 0;
                top: 0;
            }
        </style>
    @endif
@endsection
@section('body_class', 'page-login-v3 layout-full position-relative')
@section('layout_content')
    @if (Agent::isMobile() && Agent::is('iOS') && str_contains(Agent::getUserAgent(), 'MicroMessenger'))
        <style>
            .cover-up {
                opacity: 0.1;
                filter: alpha(opacity=10);
            }
        </style>
        <div class="m-0 p-0 w-full h-full text-white" style="z-index: 10; position: absolute;">
            <div class="font-size-16 h-p33 pl-20 pt-20"
                 style="line-height: 1.8; background: url(//gw.alicdn.com/tfs/TB1eSZaNFXXXXb.XXXXXXXXXXXX-750-234.png) center top/contain no-repeat">
                <p>{!! trans('common.to_safari') !!}</p>
            </div>
        </div>
    @endif

    @if (config('version.ads'))
        <div id="ad" class="px-25 py-10">
            <button class="btn btn-pure btn-outline-default icon wb-close" type="button" onclick="document.getElementById('ad').style.display = 'none'"></button>
            {!! config('version.ads') !!}
        </div>
    @endif
    <div class="page vertical-align text-center cover-up" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <div class="animation-slide-top animation-duration-1">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <div class="brand">
                                <img class="brand-img"
                                     src="{{ sysConfig('website_home_logo') ? asset(sysConfig('website_home_logo')) : '/assets/images/logo.png' }}"
                                     alt="logo" />
                                <h1 class="brand-text">{{ sysConfig('website_name') }}</h1>
                            </div>
                        </div>
                        <div class="ribbon ribbon-reverse ribbon-info ribbon-clip">
                            <button class="ribbon-inner btn dropdown-toggle pt-0" id="language" data-toggle="dropdown" aria-expanded="false">
                                <i class="font-size-20 wb-globe" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-bullet" role="menu" aria-labelledby="language">
                                @foreach (config('common.language') as $key => $value)
                                    <a class="dropdown-item" href="{{ route('lang', ['locale' => $key]) }}" role="menuitem">
                                        <i class="fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                                        <span style="padding: inherit;">{{ $value[0] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        @yield('content')
                    </div>
                    @yield('footer')
                </div>
            </div>
            @yield('modal')
        </div>
    </div>
@endsection
@section('layout_javascript')
    <script src="/assets/global/vendor/jquery-placeholder/jquery.placeholder.min.js"></script>
    <script src="/assets/global/js/Plugin/jquery-placeholder.js"></script>
    <script src="/assets/global/js/Plugin/material.js"></script>
    <script>
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
    @yield('javascript')
    <!-- 统计 -->
    {!! sysConfig('website_statistics_code') !!}
    <!-- 客服 -->
    {!! sysConfig('website_customer_service_code') !!}
@endsection
