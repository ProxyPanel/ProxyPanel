@extends('auth.layouts')
@section('title', trans('auth.active_account'))
@section('content')
    @if(Session::get('errorMsg'))
        <x-alert type="danger" :message="Session::get('errorMsg')"/>
    @endif
    @if(Session::get('successMsg'))
        <x-alert type="success" :message="Session::get('successMsg')"/>
    @endif
    <form action="{{url(Request::getRequestUri())}}" method="post">
        <a href="{{route('login')}}" class="btn btn-lg btn-block btn-success">{{trans('auth.login')}}</a>
    </form>
@endsection
