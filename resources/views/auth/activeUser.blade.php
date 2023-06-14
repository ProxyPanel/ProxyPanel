@extends('auth.layouts')
@section('title', trans('common.active_item', ['attribute' => trans('common.account')]))
@section('content')
    @if (Session::has('successMsg'))
        <x-alert type="success" :message="Session::pull('successMsg')"/>
    @endif
    @if($errors->any())
        <x-alert type="danger" :message="$errors->all()"/>
    @endif
    <form action="{{route('active')}}" method="post">
        @csrf
        @if(sysConfig('is_activate_account'))
            <div class="form-title">
                <span class="form-title">{{trans('common.active_item', ['attribute' => trans('common.account')])}}</span>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" required/>
                <label class="floating-label" for="username">
                    {{sysConfig('username_type') === 'email' || sysConfig('username_type') === null ? trans('validation.attributes.email') : trans('model.user.username')}}
                </label>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.active.error.disable')"/>
        @endif
        <a href="{{route('login')}}" class="btn btn-danger btn-lg {{sysConfig('is_activate_account')? 'float-left':'btn-block'}}">
            {{trans('common.back')}}
        </a>
        @if(sysConfig('is_activate_account'))
            <button type="submit" class="btn btn-lg btn-primary float-right">{{trans('auth.active.attribute')}}</button>
        @endif
    </form>
@endsection
