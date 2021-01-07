@extends('auth.layouts')
@section('title', trans('auth.password.reset.attribute'))
@section('content')
    @if (Session::get('successMsg'))
        <x-alert type="success" :message="Session::get('successMsg')"/>
    @endif
    @if($errors->any())
        <x-alert type="danger" :message="$errors->all()"/>
    @endif
    <form method="post" action="{{route('resetPasswd')}}">
        @csrf
        @if(sysConfig('is_reset_password'))
            <div class="form-title">
                {{trans('auth.password.reset.attribute')}}
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="email" class="form-control" name="email" value="{{Request::old('email')}}" required="required" autofocus="autofocus"/>
                <label class="floating-label">{{trans('validation.attributes.email')}}</label>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.password.reset.error.disabled' ,['email' => sysConfig('webmaster_email')])"/>
        @endif
        <a href="{{route('login')}}" class="btn btn-danger btn-lg {{sysConfig('is_reset_password')? 'float-left':'btn-block'}}">
            {{trans('common.back')}}
        </a>
        @if(sysConfig('is_reset_password'))
            <button type="submit" class="btn btn-primary btn-lg float-right">{{trans('common.submit')}}</button>
        @endif
    </form>
@endsection
