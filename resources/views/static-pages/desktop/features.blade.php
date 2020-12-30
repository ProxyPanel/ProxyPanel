@extends('layout.static-desktop-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/desktop/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')
        <div class="hero hero--features">
            <div class="container">
                <h1 class="hero__title">{!! __('static.dsktp_feature_hero_title') !!}</h1>
                <h2 class="hero__subtitle">{{ __('static.dsktp_feature_hero_text') }}</h2>
                <a href="#" class="hero__btn">{{ __('static.dsktp_feature_hero_btn') }}</a>
            </div>
        </div>

        <main>
            <div class="page-content page-content--features">

                <section class="feature feature--01">
                    <div class="container">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/features/feature-01.jpg') }}" alt="Feature">
                        </div>
                        <div class="feature__right">
                            <div class="text-content">
                                <h2>{{ __('static.dsktp_feature_section_title_1') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_1') }}</p>
                                <a class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_1') }}</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="feature feature--02">
                    <div class="container">
                        <div class="feature__left">
                            <div class="text-content">
                                <h2>{{ __('static.dsktp_feature_section_title_2') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_2') }}</p>
                                <a  class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_2') }}</a>
                            </div>
                        </div>
                        <div class="feature__right">
                            <img src="{{ asset('assets/static/desktop/images/features/feature-02.jpg') }}" alt="Feature">
                        </div>
                    </div>
                </section>


                <section class="feature feature--01">
                    <div class="container">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/features/feature-01.jpg') }}" alt="Feature">
                        </div>
                        <div class="feature__right">
                            <div class="text-content">
                                <h2>{{ __('static.dsktp_feature_section_title_6') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_6') }}</p>
                                <a class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_6') }}</a>
                            </div>
                        </div>
                    </div>
                </section>
                
                
                <section class="feature feature--03">
                    <div class="container">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/features/tv.png') }}" alt="tv">
                        </div>
                        <div class="feature__right">
                            <div class="text-content text-content--light">
                                <img src="{{ asset('assets/static/desktop/images/features/inner-logo.png') }}" alt="RitaVpn logo">
                                <h2>{{ __('static.dsktp_feature_section_title_3') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_3') }}</p>
                                <a class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_3') }}</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="feature feature--04">
                    <div class="container">
                        <div class="feature__left">
                            <div class="text-content">
                                <h2>{{ __('static.dsktp_feature_section_title_4') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_4') }}</p>
                                <a  class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_4') }}</a>
                            </div>
                        </div>
                        <div class="feature__right">
                            <img src="{{ asset('assets/static/desktop/images/features/feature-03.jpg') }}" alt="tv">
                        </div>
                    </div>
                </section>

                <section class="feature feature--05">
                    <div class="container">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/features/feature-04.jpg') }}" alt="tv">
                        </div>
                        <div class="feature__right">
                            <div class="text-content">
                                <h2>{{ __('static.dsktp_feature_section_title_5') }}</h2>
                                <p>{{ __('static.dsktp_feature_section_text_5') }}</p>
                                <a class="cs-btn cs-btn--primary cs-btn--lg" href="#">{{ __('static.dsktp_feature_section_btn_5') }}</a>
                            </div>
                        </div>
                    </div>
                </section>
                

            </div>
        </main>

    </div> <!-- ./end wrappers -->

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

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script src="{{ asset('assets/static/desktop/js/app.js') }}"></script>
@endsection