@extends('auth.layouts')
@section('title', trans('auth.password.reset.attribute'))
@section('content')
    @if (Session::has('successMsg'))
        <x-alert :message="Session::pull('successMsg')" />
    @endif
    @if ($errors->any())
        <x-alert type="danger" :message="$errors->all()" />
    @endif
    <form method="post" action="{{ route('resetPasswd') }}">
        @csrf
        @if (sysConfig('password_reset_notification'))
            <div class="form-title">
                {{ trans('auth.password.reset.attribute') }}
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" name="username" type="text" value="{{ old('username') }}" autofocus required />
                <label class="floating-label" for="username">
                    {{ sysConfig('username_type') === 'email' || sysConfig('username_type') === null ? ucfirst(trans('validation.attributes.email')) : trans('model.user.username') }}
                </label>
            </div>
        @else
            <x-alert type="danger" :message="trans('auth.password.reset.error.disabled')" />
        @endif
        <a class="btn btn-danger btn-lg {{ sysConfig('password_reset_notification') ? 'float-left' : 'btn-block' }}" href="{{ route('login') }}">
            {{ trans('common.back') }}
        </a>
        @if (sysConfig('password_reset_notification'))
            <button class="btn btn-primary btn-lg float-right" type="submit">{{ trans('common.submit') }}</button>
        @endif
    </form>
@endsection
