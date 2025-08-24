@extends('auth.layouts')
@section('title', sysConfig('website_name') . ' - ' . trans('auth.login'))
@section('content')
    <form id="login-form" action="{{ request()->routeIs('login', 'admin.login') ? Request::url() : route('login') }}" method="post">
        @csrf
        @if ($errors->any())
            <x-alert type="danger" :message="$errors->all()" />
        @endif
        @if (Session::has('successMsg'))
            <x-alert :message="Session::pull('successMsg')" />
        @endif
        <div class="form-group form-material floating" data-plugin="formMaterial">
            <input class="form-control" name="username" type="text" value="{{ old('username') }}" required />
            <label class="floating-label" for="username">
                {{ sysConfig('username_type') === 'email' || sysConfig('username_type') === null ? ucfirst(trans('validation.attributes.email')) : trans('model.user.username') }}
            </label>
        </div>
        <div class="form-group form-material floating" data-plugin="formMaterial">
            <input class="form-control" name="password" type="password" value="{{ old('password') }}" autocomplete required />
            <label class="floating-label" for="password">{{ ucfirst(trans('validation.attributes.password')) }}</label>
        </div>
        @yield('captcha', view('auth.captcha'))
        <div class="form-group clearfix">
            <div class="checkbox-custom checkbox-inline checkbox-primary checkbox-lg float-left">
                <input id="inputCheckbox" name="remember" type="checkbox">
                <label for="inputCheckbox" for="remember">{{ trans('auth.remember_me') }}</label>
            </div>
            <a class="btn btn-xs bg-red-500 text-white float-right" href="{{ route('resetPasswd') }}">
                {{ trans('auth.password.forget') }}
            </a>
        </div>
        <button class="btn btn-lg btn-block mt-40 bg-indigo-500 text-white" type="submit">{{ trans('auth.login') }}</button>
    </form>
    @if (sysConfig('oauth_path'))
        <div class="pb-5">
            <div class="line">
                <span> {{ trans('auth.one-click_login') }} </span>
            </div>
            @foreach (json_decode(sysConfig('oauth_path')) as $provider)
                @if ($provider === 'telegram')
                    <div>
                        <script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="{{ config('services.telegram.bot') }}" data-size="medium"
                                data-auth-url="{{ route('oauth.login', ['provider' => $provider]) }}" data-request-access="write"></script>
                    </div>
                @else
                    <a class="btn btn-icon btn-pure" href="{{ route('oauth.route', ['provider' => $provider, 'operation' => 'login']) }}">
                        <i class="fa-brands {{ config('common.oauth.icon')[$provider] }} fa-lg" aria-hidden="true"></i>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
    @if (sysConfig('is_register'))
        <p>
            {{ trans('auth.register.promotion') }}
            <a class="btn btn-xs bg-purple-500 text-white" href="{{ route('register') }}">
                {{ trans('auth.register.attribute') }}<i class="icon wb-arrow-right" aria-hidden="true"></i>
            </a>
        </p>
    @endif
@endsection
@section('javascript')
    <script>
        $('#login-form').submit(function(event) {
            @switch(sysConfig('is_captcha'))
                @case(3)
                // 先检查Google reCAPTCHA有没有进行验证
                if ($('#g-recaptcha-response').val() === '') {
                    swal.fire({
                        title: '{{ trans('auth.captcha.required') }}',
                        icon: 'error'
                    });
                    return false;
                }
                @break

                @case(4)
                // 先检查Google reCAPTCHA有没有进行验证
                if ($('#h-captcha-response').val() === '') {
                    swal.fire({
                        title: '{{ trans('auth.captcha.required') }}',
                        icon: 'error'
                    });
                    return false;
                }
                @break

                @default
            @endswitch
        });
    </script>
    @if(config('app.env') === 'demo')
        <script src="https://ad.ddo.jp/728x90.js.php?ddo_id=proxypanel&ddo_i={{(int) floor(time() / 60)}}" type="text/javascript" defer></script>
    @endif
@endsection
