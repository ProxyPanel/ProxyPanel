@extends('layout.static-mobile-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')

        <div class="hero" aria-label="Fantastic Tv Shows and Movies on Netflix"></div>

        <main>

            <section class="section section--features">
                <div class="container features">
                    <div class="feature">
                        <img src="{{ asset('assets/static/mobile/images/index/graphics-01.jpg') }}" alt="feature desc">
                        <div class="text-box">
                            <h2 class="feature__title">{{ __('static.mbl_feature_title_1') }}</h2>
                            <p class="feature__text">{{ __('static.mbl_feature_text_1') }}</p>
                            <a href="#" class="cs-btn cs-btn--primary">{{ __('static.mbl_feature_btn') }}</a>
                        </div>
                    </div>

                    <div class="feature">
                        <img src="{{ asset('assets/static/mobile/images/index/graphics-02.jpg') }}" alt="feature desc">
                        <div class="text-box">
                            <h2 class="feature__title">{{ __('static.mbl_feature_title_2') }}</h2>
                            <p class="feature__text">{{ __('static.mbl_feature_text_2') }}</p>
                            <a href="#" class="cs-btn cs-btn--primary">{{ __('static.mbl_feature_btn') }}</a>
                        </div>
                    </div>

                    <div class="feature">
                        <img src="{{ asset('assets/static/mobile/images/index/graphics-03.jpg') }}" alt="feature desc">
                        <div class="text-box">
                            <h2 class="feature__title">{{ __('static.mbl_feature_title_3') }}</h2>
                            <p class="feature__text">{{ __('static.mbl_feature_text_3') }}</p>
                            <a class="cs-btn cs-btn--primary" href="#">{{ __('static.mbl_feature_btn') }}</a>
                        </div>
                    </div>
                </div>
            </section>

            <!--
            <section class="section section--testimonials">
                <div class="container-fluid">

                    <div class="section-head">
                        <img src="{{ asset('assets/static/mobile/images/index/comment-graphics.png') }}" alt="tesimonials comments">
                        <h2 class="section-title section-title--light">{{ __('static.mbl_section_about_title') }}</h2>
                    </div>

                    <div class="testimonial-items-wrapper">
                        <div class="testimonials-items">
                                <div class="testimonial-item">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/mobile/images/index/avatar-img.jpeg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                        </div>
                                        <div class="public-date">2019-11-15 06:20:40</div>
                                        <p>Just finished a video call with my girlfriend! Ritavpn really helps a lot!
                                            It's convenient for making calls. </p>
                                    </div>
                                </div>

                                <div class="testimonial-item">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/mobile/images/index/avatar-img.jpeg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                        </div>
                                        <div class="public-date">2019-11-15 06:20:40</div>
                                        <p>Just finished a video call with my girlfriend! Ritavpn really helps a lot!
                                            It's convenient for making calls. </p>
                                    </div>
                                </div>

                                <div class="testimonial-item">
                                    <img class="testimonial-item__img" src="{{ asset('assets/static/mobile/images/index/avatar-img.jpeg') }}"
                                        alt="avatar">
                                    <div class="testimonial-item__data">
                                        <div class="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                            <img src="{{ asset('assets/static/mobile/images/icons/star.svg') }}" alt="rating">
                                        </div>
                                        <div class="public-date">2019-11-15 06:20:40</div>
                                        <p>Just finished a video call with my girlfriend! Ritavpn really helps a lot!
                                            It's convenient for making calls. </p>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </section>
               
                -->

            <section class="section section--prices">
                <div class="container">
                    <h2 class="section-title">{{ __('static.mbl_section_plan_title') }}</h2>

                    <div class="prices-items">
                         @foreach($packageList as $package)
                        <a href="#" class="price-item">
                            <div class="price-item__row">{{$package->name}}</div>
                            <div class="price-item__row">Price ${{$package->price}}</div>
                            <div class="price-item__row">{{$package->days}} Days </div>
                            <div class="price-item__subtext">Device num {{$package->usage}} </div>
                            <div class="price-item__subtext">Unlimited traffic </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </section>


          <!--
            <section class="section section--boxes">
                <div class="container">
                    <h2 class="section-title">{!! __('static.mbl_section_vpn_title') !!}</h2>
                    <p class="section-brief">{{ __('static.mbl_section_vpn_text') }}</p>

                    <div class="boxes-items">
                        <a href="#" class="box-item">
                            <img class="box-item__img" src="{{ asset('assets/static/mobile/images/index/blog-img.jpg') }}" alt="image">
                            <h2 class="box-item__title">How to Permanently Delete Your Zoom Account?</h2>
                            <p class="box-item__text">The Internet has made our life much more convenient than before.
                                Whether you have no time to watch…</p>
                            <div class="box-item__date">April 13, 2020</div>
                        </a>
                        <a href="#" class="box-item">
                            <img class="box-item__img" src="{{ asset('assets/static/mobile/images/index/blog-img.jpg') }}" alt="image">
                            <h2 class="box-item__title">How to Permanently Delete Your Zoom Account?</h2>
                            <p class="box-item__text">The Internet has made our life much more convenient than before.
                                Whether you have no time to watch…</p>
                            <div class="box-item__date">April 13, 2020</div>
                        </a>
                        <a href="#" class="box-item">
                            <img class="box-item__img" src="{{ asset('assets/static/mobile/images/index/blog-img.jpg') }}" alt="image">
                            <h2 class="box-item__title">How to Permanently Delete Your Zoom Account?</h2>
                            <p class="box-item__text">The Internet has made our life much more convenient than before.
                                Whether you have no time to watch…</p>
                            <div class="box-item__date">April 13, 2020</div>
                        </a>
                    </div>
                </div>
            </section>
            
            -->
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

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

    <script>
        $(function() {
            /**
             * Language switching functions
             */
            // $("#languageChangeSelect").on('change', function () {
            //     var selectedLoc = $(this).find('option:selected').val();
            //     console.log(selectedLoc);

            // });

            // $(".js-footer-lang").on('change', function () {
            //     var selectedLoc = $(this).find('option:selected').val();
            //     console.log(selectedLoc);
            // });

        });
    </script>

@endsection
