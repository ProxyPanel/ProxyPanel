@extends('auth.layouts')
@section('title', trans('auth.register'))
@section('css')
	<link href="/assets/custom/Plugin/sweetalert2/sweetalert2.min.css" type="text/css" rel="stylesheet">
	<style type="text/css">
		@media screen and (max-height: 575px) {
			.g-recaptcha {
				-webkit-transform: scale(0.81);
				transform: scale(0.81);
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
			}
		}

		.geetest_holder.geetest_wind {
			min-width: 245px !important;
		}
	</style>
@endsection
@section('content')
	<form action="/register" method="post" id="register-form">
		@if(\App\Components\Helpers::systemConfig()['is_register'])
			@if($errors->any())
				<div class="alert alert-danger">
					<span>{{$errors->first()}}</span>
				</div>
			@endif
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="email" class="form-control" autocomplete="off" name="username" value="{{Request::old('username')}}" id="username" required/>
				<label class="floating-label" for="username">{{trans('auth.username')}}</label>
				<input type="hidden" name="register_token" value="{{Session::get('register_token')}}"/>
				<input type="hidden" name="_token" value="{{csrf_token()}}"/>
				<input type="hidden" name="aff" value="{{Session::get('register_aff')}}"/>
			</div>
			@if(\App\Components\Helpers::systemConfig()['is_verify_register'])
				<div class="form-group form-material floating" data-plugin="formMaterial">
					<div class="input-group" data-plugin="inputGroupFile">
						<input type="text" class="form-control" autocomplete="off" name="verify_code" value="{{Request::old('verify_code')}}" required/>
						<label class="floating-label" for="verify_code">{{trans('auth.captcha')}}</label>
						<span class="input-group-btn">
                            <span class="btn btn-success" id="sendCode" onclick="sendVerifyCode()">
                                {{trans('auth.request')}}
                            </span>
                        </span>
					</div>
				</div>
			@endif
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="password" class="form-control" autocomplete="off" name="password" value="" required/>
				<label class="floating-label" for="password">{{trans('auth.password')}}</label>
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="password" class="form-control" autocomplete="off" name="repassword" value="" required/>
				<label class="floating-label" for="repassword">{{trans('auth.retype_password')}}</label>
			</div>
			@if(\App\Components\Helpers::systemConfig()['is_invite_register'])
				<div class="form-group form-material floating" data-plugin="formMaterial">
					<input type="password" class="form-control" name="code" value="{{Request::old('code') ? Request::old('code') : Request::get('code')}}" @if(\App\Components\Helpers::systemConfig()['is_invite_register'] == 2) required @endif/>
					<label class="floating-label" for="code">{{trans('auth.code')}}</label>
				</div>
				@if(\App\Components\Helpers::systemConfig()['is_free_code'])
					<p class="hint">
						<a href="/free" target="_blank">{{trans('auth.get_free_code')}}</a>
					</p>
				@endif
			@endif
			@if(!\App\Components\Helpers::systemConfig()['is_verify_register'])
				@switch(\App\Components\Helpers::systemConfig()['is_captcha'])
					@case(2)
				<!-- Geetest -->
					<div class="form-group form-material floating input-group" data-plugin="formMaterial">
						{!! Geetest::render() !!}
					</div>
					@break
					@case(3)
				<!-- Google noCAPTCHA -->
					<div class="form-group form-material floating input-group" data-plugin="formMaterialis_verify_register">
						{!! NoCaptcha::display() !!}
						{!! NoCaptcha::renderJs(session::get('locale')) !!}
					</div>
					@break
					@case(1)
				<!-- Default Captcha -->
					<div class="form-group form-material floating input-group" data-plugin="formMaterial">
						<input type="text" class="form-control" name="captcha" value="" required/>
						<label class="floating-label" for="captcha">{{trans('auth.captcha')}}</label>
						<img src="{{captcha_src()}}" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('auth.captcha')}}" style="float:right;"/>
					</div>
					@break
					@default
				@endswitch
			@endif
			<div class="form-group mt-20 mb-20">
				<label class="mt-checkbox mt-checkbox-outline">
					<input type="checkbox" name="tnc" checked="checked"/>
					{{trans('auth.accept_term')}}
					<a class="ml-0" href="https://docs.hentaiworld.cc/rule/tos">
						{{trans('auth.tos')}}
					</a>&
					<a class="ml-0" href="https://docs.hentaiworld.cc/rule/aup">
						{{trans('auth.aup')}}
					</a>
					<span></span>
				</label>
			</div>
		@else
			<div class="alert alert-danger">
                <span>
                    {{trans('auth.system_maintenance')}}
                </span>
			</div>
		@endif
		<div class="form-actions">
			<button class="btn btn-danger btn-lg float-left" onclick="login()">{{trans('auth.back')}}</button>
			@if(\App\Components\Helpers::systemConfig()['is_register'])
				<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
			@endif
		</div>
	</form>
	@endsection
@section('script')
	<!--[if lt IE 11]>
	<script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js" type="text/javascript"></script>
	<![endif]-->
	<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.min.js" type="text/javascript"></script>
	<script type="text/javascript">
        // 发送注册验证码
        function sendVerifyCode() {
            let flag = true; // 请求成功与否标记
            const username = $("#username").val();

            if (username.trim() === '') {
                swal.fire({title: '{{trans('auth.username_null')}}', type: 'warning', timer: 1500});
                return false;
            }

            $.ajax({
                type: "POST",
                url: "/sendCode",
                async: false,
                data: {_token: '{{csrf_token()}}', username: username},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'fail') {
                        swal.fire({title: ret.message, type: 'error', timer: 1000, showConfirmButton: false});
                        $("#sendCode").attr('disabled', false);
                        flag = false;
                    } else {
                        swal.fire('{{trans('auth.captcha_send')}}', '{{trans('auth.captcha_send')}}', 'error');
                        $("#sendCode").attr('disabled', true);
                        flag = true;
                    }
                },
                error: function () {
                    swal.fire('{{trans('auth.captcha_send')}}', '{{trans('auth.captcha_send')}}', 'error');
                    flag = false;
                }
            });

            // 请求成功才开始倒计时
            if (flag) {
                // 60秒后重新发送
                let left_time = 60;
                var tt = window.setInterval(function () {
                    left_time = left_time - 1;
                    if (left_time <= 0) {
                        window.clearInterval(tt);
                        $("#sendCode").attr('disabled', false).val('{{trans('auth.send')}}');
                    } else {
                        $("#sendCode").val(left_time);
                    }
                }, 1000);
            }
        }

        $('#register-form').submit(function (event) {
            // 先检查Google reCAPTCHA有没有进行验证
            if ($('#g-recaptcha-response').val() === '') {
                Msg(false, "{{trans('login.required_captcha')}}", 'error');
                return false;
            }
        });

        // 生成提示
        function Msg(clear, msg, type) {
            if (!clear) $('.register-form .alert').remove();

            var typeClass = 'alert-danger',
                clear = clear ? clear : false,
                $elem = $('.register-form');
            type === 'error' ? typeClass = 'alert-danger' : typeClass = 'alert-success';

            var tpl = '<div class="alert ' + typeClass + '">' +
                '<button type="button" class="close" onclick="$(this).parent().remove();"></button>' +
                '<span> ' + msg + ' </span></div>';

            if (!clear) {
                $elem.prepend(tpl);
            } else {
                $('.register-form .alert').remove();
            }
        }
	</script>
@endsection
