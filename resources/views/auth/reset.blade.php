@extends('auth.layouts')
@section('title', trans('auth.restPassword'))
@section('content')
	<form class="register-form" action="{{url(Request::getRequestUri())}}" method="post">
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
				{{trans('auth.restPassword')}}
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input class="form-control" type="password" autocomplete="off" name="password" value="" required/>
				<label class="floating-label" for="password">{{trans('auth.new_password')}}</label>
				<input type="hidden" name="_token" value="{{csrf_token()}}"/>
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input class="form-control" type="password" autocomplete="off" name="repassword" value="" required/>
				<label class="floating-label" for="repassword">{{trans('auth.retype_password')}}</label>
			</div>
		@endif
		<div class="form-actions">
			<button class="btn btn-danger btn-lg float-left" onclick="login()">{{trans('auth.back')}}</button>
			@if ($verify->status == 0)
				<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
			@endif
		</div>
	</form>
@endsection