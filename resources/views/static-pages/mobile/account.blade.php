@extends('layout.static-mobile-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')

        <main>
            <div class="auth-content">
                <div class="container">

                    <div class="auth-content__head">
                        <a href="#collapseLogin" class="active">{{ __('static.mbl_form_login') }}</a><span>|</span><a href="#collapseReg">{{ __('static.mbl_form_register') }}</a>
                    </div>

                    <form id="collapseLogin" class="form--login" action="{{url('login')}}" method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="md-form">
                                <input type="text" class="form-control" autocomplete="off" placeholder="{{__('login.username')}}" name="username" value="{{Request::old('username')}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Password</label>
                            <div class="md-form">
                                <input type="text" id="pass" class="form-control" autocomplete="off" placeholder="{{__('login.password')}}" name="password" value="{{Request::old('password')}}" required>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{csrf_token()}}" />

                        <button type="submit" class="cs-btn cs-btn--primary">Sign In</button>

                        <div class="text-right">
                            <a href="javascript:void(0)" class="password-reset">Password Reset</a>
                        </div>

                        <div class="form-bottom">
                            <a href="javascript:void(0)">{{ __('static.mbl_account_login_help_text') }}</a>
                        </div>
                    </form>

                    <?php /* Registration */ ?>
                    <form id="collapseReg" class="form--registration" style="display: none;" action="{{url('registration')}}" method="post">
                        <input type="hidden" name="register_token" value="{{Session::get('register_token')}}" />
                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                        <input type="hidden" name="aff" value="{{Session::get('register_aff')}}" />

                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="md-form">
                                <input type="text" class="form-control" autocomplete="off" placeholder="{{__('register.username_placeholder')}}" name="username" value="{{Request::old('username')}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Password</label>
                            <div class="md-form">
                                <input class="form-control" autocomplete="off" placeholder="{{__('register.password')}}" name="password" value="{{Request::old('password')}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">RePassword</label>
                            <div class="md-form">
                                <input class="form-control" autocomplete="off" placeholder="{{__('register.retype_password')}}" name="repassword" value="{{Request::old('repassword')}}" required>
                            </div>
                        </div>

                        @if(\App\Components\Helpers::systemConfig()['is_register'])
                            <button type="submit" class="cs-btn cs-btn--primary">Sign Up</button>
                        @endif

                        <div class="form-bottom">
                            <a href="javascript:void(0)">{{ __('static.mbl_account_login_help_text') }}</a>
                        </div>
                    </form>

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

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

    <script>
        $(function() {
            $('.auth-content__head').find('a').on('click', function(e) {
                e.preventDefault();
                $('.auth-content__head').find('.active').removeClass('active');
                let id = $(this).attr('href');
                $('form').hide();
                $(id).show();
                $(this).addClass('active');
            });

        });
    </script>
@endsection
