@extends('auth.layouts')
@section('title', trans('auth.login'))
@section('content')
    <form action="{{request()->routeIs('login', 'admin.login')? Request::url() : route('login')}}" method="post" id="login-form">
        @csrf
        @if($errors->any())
            <x-alert type="danger" :message="$errors->all()"/>
        @endif
        @if (Session::get('successMsg'))
            <x-alert type="success" :message="Session::get('successMsg')"/>
        @endif
        <div class="form-group form-material floating" data-plugin="formMaterial">
            <input type="email" class="form-control" name="email" value="{{Request::old('email')}}" required/>
            <label class="floating-label" for="email">{{trans('auth.email')}}</label>
        </div>
        <div class="form-group form-material floating" data-plugin="formMaterial">
            <input type="password" class="form-control" name="password" value="{{Request::old('password')}}" autocomplete required/>
            <label class="floating-label" for="password">{{trans('auth.password')}}</label>
        </div>
        @yield('captcha', view('auth.captcha'))
        <div class="form-group clearfix">
            <div class="checkbox-custom checkbox-inline checkbox-primary checkbox-lg float-left">
                <input type="checkbox" id="inputCheckbox" name="remember">
                <label for="inputCheckbox" for="remember">{{trans('auth.remember')}}</label>
            </div>
            <a href="{{route('resetPasswd')}}" class="btn btn-xs bg-red-500 text-white float-right">
                {{trans('auth.forget_password')}}
            </a>
        </div>
        <button type="submit" class="btn btn-lg btn-block mt-40 bg-indigo-500 text-white">{{trans('auth.login')}}</button>
    </form>
    @if(sysConfig('is_register'))
        <p>
            {{trans('auth.register_tip')}}
            <a href="{{route('register')}}" class="btn btn-xs bg-purple-500 text-white">
                {{trans('auth.register')}}<i class="icon wb-arrow-right" aria-hidden="true"></i>
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
          swal.fire({title: '{{trans('auth.required_captcha')}}', icon: 'error'});
          return false;
        }
          @break
          @case(4)
        // 先检查Google reCAPTCHA有没有进行验证
        if ($('#h-captcha-response').val() === '') {
          swal.fire({title: '{{trans('auth.required_captcha')}}', icon: 'error'});
          return false;
        }
          @break
          @default
          @endswitch
      });
    </script>
@endsection
