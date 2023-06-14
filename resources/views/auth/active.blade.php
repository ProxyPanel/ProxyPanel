@extends('auth.layouts')
@section('title', trans('common.active_item', ['attribute' => trans('common.account')]))
@section('content')
    @if(Session::has('errorMsg'))
        <x-alert type="danger" :message="Session::pull('errorMsg')"/>
    @endif
    @if(Session::has('successMsg'))
        <x-alert type="success" :message="Session::pull('successMsg')"/>
    @endif
    <form action="{{url(Request::getRequestUri())}}" method="post">
        <a href="{{route('login')}}" class="btn btn-lg btn-block btn-success">{{trans('auth.login')}}</a>
    </form>
@endsection
