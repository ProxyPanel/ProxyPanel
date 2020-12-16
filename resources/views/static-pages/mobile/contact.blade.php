@extends('layout.static-mobile-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')
        <main>
            <div class="page-content page-content--contact page-content--subpage">

                <div class="page-content__inner">

                    <div class="page-content__head">
                        <div class="container">
                            <h1>Contact 77VPN</h1>
                        </div>
                    </div>

                    <section class="contact-s">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 contact-form-col">
                                    <p class="contact-form-col__intro">{{ __('static.mbl_contact-title-1') }}</p>
                                    <form class="form--contact" action="">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="email" placeholder="Email Address">
                                        </div>
                                        <div class="form-group">
                                            <select name="" id="" class="form-control" name="question" >
                                                <option value="">Can't Log in</option>
                                                <option value="">Connection failure</option>
                                                <option value="">Connected, but can't unblock websites or stream videos</option>
                                                <option value="">Connected, but Internet is slow</option>
                                                <option value="">Payment failure</option>
                                                <option value="">Others</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <textarea type="text" class="form-control" placeholder="How can we help you?"></textarea>
                                        </div>
                                        <button class="btn btn-primary">{{ __('static.mbl_contact-form-btn') }}</button>
                                    </form>
                                </div>
                                <div class="col-md-12 contact-data-col">
                                    <p class="contact-data-col__intro">{{ __('static.mbl_contact-title-2') }}</p>
                                    <ul class="contact-data-col__list">
                                        <li>
                                            <div class="data">
                                                <p>Email</p>
                                                <div class="data-value"><a href="mailto:service@ritavpn.com">service@77cloud.com</a></div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="data">
                                                <p>WhatsApp</p>
                                                <div class="data-value"><a href="+1 323-508-5800">+1 323-508-5800</a></div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="data">
                                                <div class="data-value">Weikawen technology limited</div><br>
                                                <p>Room 19C, Lockhart Ctr.,301-307 Lockhart Rd., Wan Chai,Hong Kong</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
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

    <script src="https://unpkg.com/swiper/js/swiper.min.js"></script>

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

@endsection
