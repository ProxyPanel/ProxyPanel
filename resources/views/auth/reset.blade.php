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
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" name="password" type="password" autocomplete="off" required />
                <label class="floating-label" for="password">{{ trans('auth.password.new') }}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" name="password_confirmation" type="password" autocomplete="off" required />
                <label class="floating-label" for="password_confirmation">{{ ucfirst(trans('validation.attributes.password_confirmation')) }}</label>
            </div>
        @endif
        <a class="btn btn-danger btn-lg {{ $verify->status === 0 ? 'float-left' : 'btn-block' }}" href="{{ route('login') }}">{{ trans('common.back') }}</a>
        @if ($verify->status === 0)
            <button class="btn btn-primary btn-lg float-right" type="submit">{{ trans('common.submit') }}</button>
        @endif
    </form>
@endsection
