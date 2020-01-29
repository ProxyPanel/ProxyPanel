@extends('auth.layouts')
@section('title', trans('auth.active_account'))
@section('content')
	@if (Session::get('successMsg'))
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span
						class="sr-only">{{trans('auth.close')}}</span></button>
			<span> {{Session::get('successMsg')}} </span>
		</div>
	@endif
	@if($errors->any())
		<div class="alert alert-danger">
			<span> {{$errors->first()}} </span>
		</div>
	@endif
	<form action="/activeUser" method="post">
		@if(\App\Components\Helpers::systemConfig()['is_active_register'])
			<div class="form-title">
				<span class="form-title">{{trans('auth.active_account')}}</span>
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="email" class="form-control" name="username" value="{{Request::get('username')}}" required/>
				<label class="floating-label" for="username">{{trans('auth.username')}}</label>
				{{csrf_field()}}
			</div>
		@else
			<div class="alert alert-danger">
				<span> {{trans('auth.system_maintenance_tip',['email' => \App\Components\Helpers::systemConfig()['webmaster_email']])}}</span>
			</div>
		@endif
		<a href="/login" class="btn btn-danger btn-lg {{\App\Components\Helpers::systemConfig()['is_active_register']? 'float-left':'btn-block'}}">{{trans('auth.back')}}</a>
		@if(\App\Components\Helpers::systemConfig()['is_active_register'])
			<button type="submit" class="btn btn-lg btn-primary float-right">{{trans('auth.active')}}</button>
		@endif
	</form>
@endsection
