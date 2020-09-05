@extends('auth.layouts')
@section('title', trans('auth.login'))
@section('content')
	<form action="/login" method="post" id="login-form">
		@if($errors->any())
			<x-alert type="danger" :message="$errors->first()"/>
		@endif
		@if (Session::get('regSuccessMsg'))
			<x-alert type="success" :message="Session::get('regSuccessMsg')"/>
		@endif
		<div class="form-group form-material floating" data-plugin="formMaterial">
			<input type="email" class="form-control" name="email" value="{{Request::old('email')}}" required/>
			<label class="floating-label" for="email">{{trans('auth.email')}}</label>
		</div>
		<div class="form-group form-material floating" data-plugin="formMaterial">
			<input type="password" class="form-control" name="password" value="{{Request::old('password')}}"
					autocomplete required/>
			<label class="floating-label" for="password">{{trans('auth.password')}}</label>
			{{csrf_field()}}
		</div>
		@switch(sysConfig('is_captcha'))
			@case(1)<!-- Default Captcha -->
			<div class="form-group form-material floating input-group" data-plugin="formMaterial">
				<input type="text" class="form-control" name="captcha"/>
				<label class="floating-label" for="captcha">{{trans('auth.captcha')}}</label>
				<img src="{{captcha_src()}}" class="float-right" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('auth.captcha')}}"/>
			</div>
			@break
			@case(2)<!-- Geetest -->
			<div class="form-group form-material floating vertical-align-middle" data-plugin="formMaterial">
				{!! Geetest::render() !!}
			</div>
			@break
			@case(3)<!-- Google reCaptcha -->
			<div class="form-group form-material floating vertical-align-middle" data-plugin="formMaterial">
				{!! NoCaptcha::display() !!}
				{!! NoCaptcha::renderJs(session::get('locale')) !!}
			</div>
			@break
			@case(4)<!-- hCaptcha -->
			<div class="form-group form-material floating vertical-align-middle" data-plugin="formMaterial">
				{!! HCaptcha::display() !!}
				{!! HCaptcha::renderJs(session::get('locale')) !!}
			</div>
			@break
			@default
		@endswitch
		<div class="form-group clearfix">
			<div class="checkbox-custom checkbox-inline checkbox-primary checkbox-lg float-left">
				<input type="checkbox" id="inputCheckbox" name="remember">
				<label for="inputCheckbox" for="remember">{{trans('auth.remember')}}</label>
			</div>
			<a href="/resetPassword"
					class="btn btn-xs bg-red-500 text-white float-right">{{trans('auth.forget_password')}}</a>
		</div>
		<button type="submit"
				class="btn btn-lg btn-block mt-40 bg-indigo-500 text-white">{{trans('auth.login')}}</button>
	</form>
	@if(sysConfig('is_register'))
		<p>{{trans('auth.register_tip')}} <a href="/register"
					class="btn btn-xs bg-purple-500 text-white">{{trans('auth.register')}} <i
						class="icon wb-arrow-right" aria-hidden="true"></i></a></p>
	@endif
@endsection
@section('script')
	<script type="text/javascript">
		$('#login-form').submit(function (event) {
			@switch(sysConfig('is_captcha'))
			@case(3)
			// 先检查Google reCAPTCHA有没有进行验证
			if ($('#g-recaptcha-response').val() === '') {
				swal.fire({title: '{{trans('auth.required_captcha')}}', type: 'error'});
				return false;
			}
			@break
			@case(4)
			// 先检查Google reCAPTCHA有没有进行验证
			if ($('#h-captcha-response').val() === '') {
				swal.fire({title: '{{trans('auth.required_captcha')}}', type: 'error'});
				return false;
			}
			@break
			@default
			@endswitch
		});
	</script>
@endsection
