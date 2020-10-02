@extends('auth.layouts')
@section('title', trans('auth.resetPassword'))
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
                {{trans('auth.resetPassword')}}
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="email" class="form-control" name="email" value="{{Request::old('email')}}" required="required" autofocus="autofocus"/>
                <label class="floating-label">{{trans('auth.email')}}</label>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.system_maintenance_tip' ,['email' => sysConfig('webmaster_email')])"/>
        @endif
        <a href="{{route('login')}}" class="btn btn-danger btn-lg {{sysConfig('is_reset_password')? 'float-left':'btn-block'}}">
            {{trans('auth.back')}}
        </a>
        @if(sysConfig('is_reset_password'))
            <button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.submit')}}</button>
        @endif
    </form>
@endsection
