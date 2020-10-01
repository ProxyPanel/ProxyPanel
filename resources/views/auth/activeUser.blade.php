@extends('auth.layouts')
@section('title', trans('auth.active_account'))
@section('content')
    @if (Session::get('successMsg'))
        <x-alert type="success" :message="Session::get('successMsg')"/>
    @endif
    @if($errors->any())
        <x-alert type="danger" :message="$errors->all()"/>
    @endif
    <form action="/activeUser" method="post">
        @csrf
        @if(sysConfig('is_activate_account') == 2)
            <div class="form-title">
                <span class="form-title">{{trans('auth.active_account')}}</span>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="email" class="form-control" name="email" value="{{Request::get('email')}}" required/>
                <label class="floating-label" for="email">{{trans('auth.email')}}</label>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.system_maintenance_tip',['email' => sysConfig('webmaster_email')])"/>
        @endif
        <a href="/login"
           class="btn btn-danger btn-lg {{sysConfig('is_activate_account')==2? 'float-left':'btn-block'}}">{{trans('auth.back')}}</a>
        @if(sysConfig('is_activate_account')==2)
            <button type="submit" class="btn btn-lg btn-primary float-right">{{trans('auth.active')}}</button>
        @endif
    </form>
@endsection
