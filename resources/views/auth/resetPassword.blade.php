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
				{{csrf_field()}}
			</div>
		@else
			<div class="alert alert-danger">
				<span> {{trans('auth.system_maintenance_tip',['email' => \App\Components\Helpers::systemConfig()['admin_email']])}} </span>
			</div>
		@endif
		<a href="/login" class="btn btn-danger btn-lg {{\App\Components\Helpers::systemConfig()['is_reset_password']? 'float-left':'btn-block'}}">{{trans('auth.back')}}</a>
		@if(\App\Components\Helpers::systemConfig()['is_reset_password'])
			<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
		@endif
	</form>
@endsection
