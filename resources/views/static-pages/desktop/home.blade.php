@extends('layout.static-desktop-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/desktop/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')

        <div class="hero" aria-label="Fantastic Tv Shows and Movies on Netflix"></div>

        <main>
            <section class="section section--features">
                <div class="container features">
                    <div class="feature">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/index/feature-img-01.jpg') }}" alt="feature desc">
                        </div>
                        <div class="feature__right">
                            <div class="text-box">
                                <h2 class="feature__title">{{ __('static.dsktp_feature_title_1') }}</h2>
                                <p class="feature__text">{{ __('static.dsktp_feature_text_1') }}</p>
                                <a href="#" class="cs-btn cs-btn--primary">{{ __('static.dsktp_feature_btn') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature__left">
                            <div class="text-box">
                                <h2 class="feature__title">{{ __('static.dsktp_feature_title_2') }}</h2>
                                <p class="feature__text">{{ __('static.dsktp_feature_text_2') }}</p>
                                <a href="#" class="cs-btn cs-btn--primary">{{ __('static.dsktp_feature_btn') }}</a>
                            </div>
                        </div>
                        <div class="feature__right">
                            <img src="{{ asset('assets/static/desktop/images/index/feature-img-02.jpg') }}" alt="feature desc">
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature__left">
                            <img src="{{ asset('assets/static/desktop/images/index/feature-img-03.jpg') }}" alt="feature desc">
                        </div>
                        <div class="feature__right">
                            <div class="text-box">
                                <h2 class="feature__title">{{ __('static.dsktp_feature_title_3') }}</h2>
                                <p class="feature__text">{{ __('static.dsktp_feature_text_3') }}</p>
                                <a class="cs-btn cs-btn--primary" href="#">{{ __('static.dsktp_feature_btn') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section section--testimonials">
                <div class="container-fluid">
                    <h2 class="section-title">{{ __('static.dsktp_section_about_title') }}</h2>

                    <div class="testimonial-items-wrapper">
                        <div class="testimonials-items testimonial-slider swiper-container">
                            <div class="swiper-wrapper">
                                <div class="testimonial-item swiper-slide">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/desktop/images/index/avatar-img.jpg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                        </div>
                                        <div class="public-date">2019-11-15 06:20:40</div>
                                        <p>The fastest VPN I have used on my Android phone. It is very convenient to use in foreign trade, with one-click connection.</p>
                                    </div>
                                </div>

                                <div class="testimonial-item swiper-slide">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/desktop/images/index/avatar-img2.jpg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rating">
                                        </div>
                                        <div class="public-date">2019-11-15 06:20:40</div>
                                        <p>Can unlock a lot of services, very easy to use software, support multiple platforms </p>
                                    </div>
                                </div>

                                <div class="testimonial-item swiper-slide">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/desktop/images/index/avatar-img3.jpg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                        </div>
                                        <div class="public-date">2018-11-15 06:20:40</div>
                                        <p>Just finished a video call with my girlfriend! 77vpn really helps a lot2!
                                            It's convenient for making calls. </p>
                                    </div>
                                </div>

                                <div class="testimonial-item swiper-slide">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/desktop/images/index/avatar-img4.jpg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                        </div>
                                        <div class="public-date">2017-11-15 06:20:40</div>
                                        <p>77vpn is the fastest and most stable vpn I have ever used. The speed is very fast and very stable.</p>
                                    </div>
                                </div>

                                <div class="testimonial-item swiper-slide">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/desktop/images/index/avatar-img5.jpg') }}" alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                            <img src="{{ asset('assets/static/desktop/images/icons/star.svg') }}" alt="rate">
                                        </div>
                                        <div class="public-date">2020-11-15 06:20:40</div>
                                        <p>The effect of game acceleration is very good, the delay is very low. </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="prev"><i class="fas fa-chevron-right"></i></div>
                        <div class="next"><i class="fas fa-chevron-left"></i></div>
                    </div>
                </div>
            </section>

            <section class="section section--prices">
                <div class="container">
                    <h2 class="section-title">{{ __('static.dsktp_section_plan_title') }}</h2>

                    <div class="prices-items">
                       @foreach($packageList as $package)
                        <a href="{{ url('/price') }}" class="price-item">
                            <h2 class="price-item__time">{{$package->name}}</h2>
                            <p class="price-item__value">$ {{$package->price}}</p>
                            <div class="price-item__duration">{{$package->days}} Day</div>
                            <div class="price-item__duration">Device num {{$package->usage}}  </div>
                            <div class="price-item__subtext">Unlimited traffic</div>
                        </a>
                       @endforeach
                    </div>
                </div>
            </section>

            <section class="section section--boxes">
                <div class="container">
                    <h2 class="section-title">{!! __('static.dsktp_section_vpn_title') !!}</h2>
                    <p class="section-brief">{{ __('static.dsktp_section_vpn_text') }}</p>

                    <div class="boxes-items">
                         @foreach($articleList as $article)
                        <a href="{{url('/tutorial?id=') . $article->id}}"  class="box-item">
                            <img class="box-item__img" src="{{$article->logo}}" alt="image">
                            <h2 class="box-item__title">{{$article->title}} </h2>
                            <p class="box-item__text">{{$article->summary}}</p>
                            <div class="box-item__date">{{$article->created_at}}</div>
                        </a>
                         @endforeach
                        </a>
                    </div>
                </div>
            </section>

            <section class="section section--downloads">
                <div class="container">
                    <h2 class="section-title">{{ __('static.dsktp_section_download_title') }}</h2>

                    <ul class="download-list">
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <div class="circle"><img src="{{ asset('assets/static/desktop/images/logos/apple-logo.svg') }}" alt="ios"></div>
                                <span>iOS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <div class="circle"><img src="{{ asset('assets/static/desktop/images/logos/android-logo.svg') }}" alt="android"></div>
                                <span>Android</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <div class="circle"><img src="{{ asset('assets/static/desktop/images/logos/windows-logo.svg') }}" alt="windows"></div>
                                <span>Windows</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <div class="circle"><img src="{{ asset('assets/static/desktop/images/logos/mac-logo.svg') }}" alt="mac"></div>
                                <span>Mac</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </section>
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

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script src="{{ asset('assets/static/desktop/js/app.js') }}"></script>

@endsection
