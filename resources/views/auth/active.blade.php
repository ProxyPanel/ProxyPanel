@extends('auth.layouts')
@section('title', trans('common.active_item', ['attribute' => trans('common.account')]))
@section('content')
    @if (Session::has('errorMsg'))
        <x-alert type="danger" :message="Session::pull('errorMsg')" />
    @endif
    @if (Session::has('successMsg'))
        <x-alert :message="Session::pull('successMsg')" />
    @endif
    <form action="{{ url(Request::getRequestUri()) }}" method="post">
        <a class="btn btn-lg btn-block btn-success" href="{{ route('login') }}">{{ trans('auth.login') }}</a>
    </form>
@endsection
