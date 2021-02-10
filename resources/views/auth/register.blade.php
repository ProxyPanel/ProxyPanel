@extends('auth.layouts')
@section('title', trans('auth.register.attribute'))
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <form action="{{route('register')}}" method="post" id="register-form">
        @if(sysConfig('is_register'))
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            @csrf
            <input type="hidden" name="register_token" value="{{Session::get('register_token')}}"/>
            <input type="hidden" name="aff" value="{{Request::input('aff')}}"/>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="text" class="form-control" name="username" id="username" value="{{Request::old('username') ? : Request::input('username')}}" autocomplete="off" required/>
                <label class="floating-label" for="username">{{trans('validation.attributes.username')}}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                @if($emailList)
                    <div class="input-group">
                        <input type="text" class="form-control" name="emailHead" id="emailHead" value="{{Request::old('emailHead')}}" required/>
                        <label class="floating-label" for="emailHead">{{trans('validation.attributes.email')}}</label>
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-indigo-600 text-white">@</span>
                        </div>
                        <select class="form-control" name="emailTail" id="emailTail" data-plugin="selectpicker" data-style="btn-outline-primary">
                            @foreach($emailList as $email)
                                <option value="{{$email->words}}">{{$email->words}}</option>
                            @endforeach
                        </select>
                        <input type="text" name="email" id="email" hidden/>
                    </div>
                @else
                    <input type="email" class="form-control" name="email" id="email" value="{{Request::old('email')}}" required/>
                    <label class="floating-label" for="email">{{trans('validation.attributes.email')}}</label>
                @endif
            </div>
            @if(sysConfig('is_activate_account') == 1)
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    <div class="input-group" data-plugin="inputGroupFile">
                        <input type="text" class="form-control" name="verify_code" value="{{Request::old('verify_code')}}" required/>
                        <label class="floating-label" for="verify_code">{{trans('auth.captcha.attribute')}}</label>
                        <span class="input-group-btn">
                            <button class="btn btn-success" id="sendCode" onclick="sendVerifyCode()">
                                {{trans('auth.request')}}
                            </button>
                        </span>
                    </div>
                </div>
            @endif
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="password" class="form-control" name="password" required/>
                <label class="floating-label" for="password">{{trans('validation.attributes.password')}}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="password" class="form-control" name="password_confirmation" required/>
                <label class="floating-label" for="password_confirmation">{{trans('validation.attributes.password_confirmation')}}</label>
            </div>
            @if(sysConfig('is_invite_register'))
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    <input type="password" class="form-control" name="code" value="{{Request::old('code') ?: Request::input('code')}}"
                           @if(sysConfig('is_invite_register') == 2) required @endif/>
                    <label class="floating-label" for="code">
                        {{trans('auth.invite.attribute')}}@if(sysConfig('is_invite_register') == 1)({{trans('auth.optional')}}) @endif
                    </label>
                </div>
                @if(sysConfig('is_free_code'))
                    <p class="hint">
                        <a href="{{route('freeInvitationCode')}}" target="_blank">{{trans('auth.invite.get')}}</a>
                    </p>
                @endif
            @endif
            @yield('captcha', view('auth.captcha'))
            <div class="form-group mt-20 mb-20">
                <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" name="term" id="term" {{Request::old('term') ? 'checked':''}} />
                    <label for="term">{{trans('auth.accept_term')}}
                        <button class="btn btn-xs btn-primary" data-target="#tos" data-toggle="modal" type="button">
                            {{trans('auth.tos')}}
                        </button>
                        &
                        <button class="btn btn-xs btn-primary" data-target="#aup" data-toggle="modal" type="button">
                            {{trans('auth.aup')}}
                        </button>
                    </label>
                </div>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.register.error.disable')"/>
        @endif
        <a href="{{route('login')}}" class="btn btn-danger btn-lg {{sysConfig('is_register')? 'float-left': 'btn-block'}}">
            {{trans('common.back')}}
        </a>
        @if(sysConfig('is_register'))
            <button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.register.attribute')}}</button>
        @endif
    </form>
@endsection
@section('modal')
    <div class="modal fade modal-info text-left" id="tos" aria-hidden="true" aria-labelledby="tos" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-simple modal-sidebar modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close" style="position:absolute;">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">{{sysConfig('website_name')}}- {{trans('auth.tos')}} <small>2019年11月28日10:49</small></h4>
                </div>
                <div class="modal-body">
                    @include('auth.docs.tos')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-block bg-red-500 text-white mb-25" data-dismiss="modal">{{trans('common.close')}}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-info text-left" id="aup" aria-hidden="true" aria-labelledby="aup" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-simple modal-sidebar modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close" style="position:absolute;">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">{{sysConfig('website_name')}}- {{trans('auth.aup')}} <small>2019年11月28日10:49</small></h4>
                </div>
                <div class="modal-body">
                    @include('auth.docs.aup')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-block bg-red-500 text-white mb-25" data-dismiss="modal">{{trans('common.close')}}</button>
                </div>
            </div>
        </div>
    </div>
    @endsection
@section('javascript')
	<!--[if lt IE 11]>
    <script src="/assets/custom/sweetalert2/polyfill.min.js"></script>
    <![endif]-->
    <script src="/assets/custom/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        @if($emailList)
        function getEmail() {
          let email = $('#emailHead').val().trim();
          const emailTail = $('#emailTail').val();
          if (email === '') {
            swal.fire({title: '{{trans('validation.required', ['attribute' => trans('validation.attributes.email')])}}', icon: 'warning', timer: 1500});
            return false;
          }
          email += '@' + emailTail;
          $('#email').val(email);
          return email;
        }
        @endif

        // 发送注册验证码
        function sendVerifyCode() {
          let flag = true; // 请求成功与否标记
          let email = $('#email').val().trim();
            @if($emailList)
                email = getEmail();
            @endif

            if (email === '') {
              swal.fire({title: '{{trans('validation.required', ['attribute' => trans('validation.attributes.email')])}}', icon: 'warning', timer: 1500});
              return false;
            }

          $.ajax({
            method: 'POST',
            url: '{{route('sendVerificationCode')}}',
            dataType: 'json',
            data: {_token: '{{csrf_token()}}', email: email},
            success: function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success'});
                $('#sendCode').attr('disabled', true);
                flag = true;
              } else {
                swal.fire({title: ret.message, icon: 'error', timer: 1000, showConfirmButton: false});
                $('#sendCode').attr('disabled', false);
                flag = false;
              }
            },
            error: function() {
              swal.fire({title: '发送失败', icon: 'error'});
              flag = false;
            },
          });

          // 请求成功才开始倒计时
          if (flag) {
            // 60秒后才能重新申请发送
            let left_time = 60;
            const tt = window.setInterval(function() {
              left_time--;
              if (left_time <= 0) {
                window.clearInterval(tt);
                $('#sendCode').removeAttr('disabled').text('{{trans('auth.request')}}');
              } else {
                $('#sendCode').text(left_time + ' s');
              }
            }, 1000);
          }
        }

        $('#register-form').submit(function(event) {
            @if($emailList)
            getEmail();
            @endif

            @switch(sysConfig('is_captcha'))
            @case(3)
          // 先检查Google reCAPTCHA有没有进行验证
          if ($('#g-recaptcha-response').val() === '') {
            swal.fire({title: '{{trans('auth.captcha.required')}}', icon: 'error'});
            return false;
          }
            @break
            @case(4)
          // 先检查Google reCAPTCHA有没有进行验证
          if ($('#h-captcha-response').val() === '') {
            swal.fire({title: '{{trans('auth.captcha.required')}}', icon: 'error'});
            return false;
          }
            @break
            @default
            @endswitch
        });
    </script>
@endsection
