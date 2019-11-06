@extends('auth.layouts')
@section('title', trans('auth.restPassword'))
@section('content')
	@if (Session::get('successMsg'))
		<div class="alert alert-success">
			<span> {{Session::get('successMsg')}} </span>
		</div>
	@endif
	@if($errors->any())
		<div class="alert alert-danger">
			<span> {{$errors->first()}} </span>
		</div>
	@endif
	<form method="post" action="/resetPassword">
		@if(\App\Components\Helpers::systemConfig()['is_reset_password'])
			<div class="form-title">
				{{trans('auth.restPassword')}}
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="email" class="form-control" name="username" value="{{Request::old('username')}}" required="required" autofocus="autofocus"/>
				<label class="floating-label">{{trans('auth.username')}}</label>
				<input type="hidden" name="_token" value="{{csrf_token()}}"/>
			</div>
		@else
			<div class="alert alert-danger">
				<span> {{trans('auth.system_maintenance_tip',['email' => \App\Components\Helpers::systemConfig()['admin_email']])}} </span>
			</div>
		@endif
		<div class="form-actions">
			<button class="btn btn-danger btn-lg float-left" onclick="login()">{{trans('auth.back')}}</button>
			@if(\App\Components\Helpers::systemConfig()['is_reset_password'])
				<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
			@endif
		</div>
	</form>
@endsection
