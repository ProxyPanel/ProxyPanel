@extends('auth.layouts')
@section('title', trans('auth.resetPassword'))
@section('content')
	<form action="{{url(Request::getRequestUri())}}" method="post" class="register-form">
		@if(Session::get('successMsg'))
			<div class="alert alert-success">
				<span> {{Session::get('successMsg')}} </span>
			</div>
		@endif
		@if($errors->any())
			<div class="alert alert-danger">
				<span> {{$errors->first()}} </span>
			</div>
		@endif
		@if ($verify->status > 0 && count($errors) <= 0 && empty(Session::get('successMsg')))
			<div class="alert alert-danger">
				<span> {{trans('auth.overtime')}} </span>
			</div>
		@else
			<div class="form-title">
				{{trans('auth.resetPassword')}}
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input class="form-control" type="password" autocomplete="off" name="password" required/>
				<label class="floating-label" for="password">{{trans('auth.new_password')}}</label>
				{{csrf_field()}}
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input class="form-control" type="password" autocomplete="off" name="confirmPassword" required/>
				<label class="floating-label" for="confirmPassword">{{trans('auth.confirm_Password')}}</label>
			</div>
		@endif
		<a href="/login"
				class="btn btn-danger btn-lg {{$verify->status== 0? 'float-left': 'btn-block'}}">{{trans('auth.back')}}</a>
		@if ($verify->status == 0)
			<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
		@endif
	</form>
@endsection
