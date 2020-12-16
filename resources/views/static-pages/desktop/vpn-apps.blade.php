@extends('layout.static-desktop-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/desktop/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')

    <script>
        window.download_configs={
            'android_qr':'/site/qr-link?to=android&utm=direct%3A%2A%3A%2A%3A%2A',
            'android_google_play':'/site/down?to=android-play&utm=direct%3A%2A%3A%2A%3A%2A',
            'android_apk':'/site/direct-download?to=android',
            'ios_qr':'/site/qr-link?to=ios&utm=direct%3A%2A%3A%2A%3A%2A',
            'appstore':'/site/down?to=ios&utm=direct%3A%2A%3A%2A%3A%2A',
            'windows':'/site/direct-download?to=windows',
            'mac':'/site/down?to=mac&utm=direct%3A%2A%3A%2A%3A%2A'
        }
    </script>

        <main>
            <div class="page-content page-content--subpage">

                <div class="platform-chooser">
                    <div class="platform-chooser__inner">
                        <a class="platform-chooser__link active" href="#android">
                            <img class="base" src="{{ asset('assets/static/desktop/images/vpn-apps/android-platform.png') }}" alt="Android">
                            <img class="active" src="{{ asset('assets/static/desktop/images/vpn-apps/android-platform-active.png') }}" alt="Android">
                            <span>Android</span>
                        </a>
                        <a class="platform-chooser__link" href="#windows">
                            <img class="base" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform.png') }}" alt="Windows">
                            <img class="active" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform-active.png') }}" alt="Windows">
                            <span>Windows</span>
                        </a>
                      
                        <a class="platform-chooser__link" href="#mac">
                            <img class="base" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform.png') }}" alt="mac">
                            <img class="active" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform-active.png') }}" alt="mac">
                            <span>MAC</span>
                        </a>
                      
                         <a class="platform-chooser__link" href="#ios">
                            <img class="base" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform.png') }}" alt="ios">
                            <img class="active" src="{{ asset('assets/static/desktop/images/vpn-apps/windows-platform-active.png') }}" alt="ios">
                            <span>IOS</span>
                        </a>
                        
                      
                        
                    </div>
                </div>

                <div class="subpage-content">
                    <div class="container">

                        <div class="app-download-items">
                            <div class="app-download"  id="android">
                                <div class="app-download__left">
                                    <h2>{!!  __('static.dsktp_vpn_android_title') !!}</h2>
                                    <div class="app-download__btn-group">
                                        <a href="#" class="app-download__btn app-download__btn--android" aria-label="Donwload for android"></a>
                                        <a href="#" class="app-download__link"><i class="icon-download"></i>{!! __('static.dsktp_vpn_android_btn') !!}</a>
                                    </div>
                                </div>
                                <div class="app-download__right">
                                    <img src="{{ asset('assets/static/desktop/images/vpn-apps/phones.jpg') }}" alt="download 77vpn">
                                </div>
                            </div>
                            
                         

                            <div class="app-download" style="display: none" id="windows">
                                <div class="app-download__left">
                                    <h2>{!! __('static.dsktp_vpn_win_title') !!}</h2>
                                    <a href="#" class="app-download__btn" aria-label="Donwload for Windows"></a>
                                </div>
                                <div class="app-download__right">
                                    <img src="{{ asset('assets/static/desktop/images/vpn-apps/bg-windows.jpg') }}" alt="download 77vpn">
                                </div>
                            </div>
                            
                             <div class="app-download"   style="display: none" id="mac">
                                <div class="app-download__left">
                                    <h2>{!!  __('static.dsktp_vpn_mac_title') !!}</h2>
                                    <div class="app-download__btn-group">
                                        <a href="#" class="app-download__btn app-download__btn--android" aria-label="Donwload for mac"></a>
                                        <a href="#" class="app-download__link"><i class="icon-download"></i>{!! __('static.dsktp_vpn_mac_btn') !!}</a>
                                    </div>
                                </div>
                                <div class="app-download__right">
                                    <img src="{{ asset('assets/static/desktop/images/vpn-apps/phones.jpg') }}" alt="download 77vpn">
                                </div>
                            </div>
                            
                             <div class="app-download" style="display: none" id="ios">
                                <div class="app-download__left">
                                    <h2>{!!  __('static.dsktp_vpn_mac_title') !!}</h2>
                                    <div class="app-download__btn-group">
                                        <a href="#" class="app-download__btn app-download__btn--android" aria-label="Donwload for ios"></a>
                                        <a href="#" class="app-download__link"><i class="icon-download"></i>{!! __('static.dsktp_vpn_mac_btn') !!}</a>
                                    </div>
                                </div>
                                <div class="app-download__right">
                                    <img src="{{ asset('assets/static/desktop/images/vpn-apps/phones.jpg') }}" alt="download 77vpn">
                                </div>
                            </div>
                            
                            
                        </div>

                    </div>
                </div>

                <section class="section section--benefits">
                    <div class="container">
                        <div class="benefits-list">
                            <div class="benefits-list__item">
                                <img src="{{ asset('assets/static/desktop/images/vpn-apps/graphics-01.svg') }}" alt="Get a Free trial via watching ads">
                                <h3>{{ __('static.dsktp_vpn_service_1') }}</h3>
                            </div>
                            <div class="benefits-list__item">
                                <img src="{{ asset('assets/static/desktop/images/vpn-apps/graphics-02.svg') }}" alt="Do not log online activity">
                                <h3>{{ __('static.dsktp_vpn_service_2') }}</h3>
                            </div>
                            <div class="benefits-list__item">
                                <img src="{{ asset('assets/static/desktop/images/vpn-apps/graphics-03.svg') }}" alt="Connect 4 devices simultaneously">
                                <h3>{{ __('static.dsktp_vpn_service_3') }}</h3>
                            </div>
                            <div class="benefits-list__item">
                                <img src="{{ asset('assets/static/desktop/images/vpn-apps/graphics-04.svg') }}" alt="7-day money-back guarantee">
                                <h3>{{ __('static.dsktp_vpn_service_4') }}</h3>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </main>

@endsection

@section('footer-script')
    <!-- 3rd party JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>

    <script src="{{ asset('assets/static/desktop/js/app.js') }}"></script>

@endsection
