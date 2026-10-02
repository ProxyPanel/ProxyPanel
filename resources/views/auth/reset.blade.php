@extends('auth.layouts')
@section('title', trans('auth.password.reset.attribute'))
@section('content')
    <form class="register-form" action="{{ url(Request::getRequestUri()) }}" method="post">
        @csrf
        @if (Session::has('successMsg'))
            <x-alert :message="Session::pull('successMsg')" />
        @endif
        @if ($errors->any())
            <x-alert type="danger" :message="$errors->all()" />
        @else
            <div class="form-title">
                {{ trans('auth.password.reset.attribute') }}
            </div>
            <x-form.floating-row name="password" type="password" autocomplete="" :label="trans('auth.password.new')" />
            <x-form.floating-row name="password_confirmation" type="password" autocomplete="" :label="ucfirst(trans('validation.attributes.password_confirmation'))" />
        @endif
        <a class="btn btn-danger btn-lg {{ $verify->status === 0 ? 'float-left' : 'btn-block' }}" href="{{ route('login') }}">{{ trans('common.back') }}</a>
        @if ($verify->status === 0)
            <button class="btn btn-primary btn-lg float-right" type="submit">{{ trans('common.submit') }}</button>
        @endif
    </form>
@endsection
