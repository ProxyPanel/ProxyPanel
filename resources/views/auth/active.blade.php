@extends('auth.layouts')
@section('title', trans('auth.active_account'))
@section('content')
    @if(Session::get('errorMsg'))
        <div class="alert alert-danger">
            <span> {{Session::get('errorMsg')}} </span>
        </div>
    @endif
    @if(Session::get('successMsg'))
        <div class="alert alert-success">
            <span> {{Session::get('successMsg')}} </span>
        </div>
    @endif
    <form action="{{url(Request::getRequestUri())}}" method="post">
        <a href="/login" class="btn btn-lg btn-block btn-success">{{trans('auth.login')}}</a>
    </form>
@endsection
