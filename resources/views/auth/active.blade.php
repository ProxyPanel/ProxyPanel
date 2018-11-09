@extends('auth.layouts')
@section('title', trans('active.title'))
@section('css')
    <link href="/assets/pages/css/login-2.min.css" rel="stylesheet" type="text/css" />
@endsection
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
    <!-- BEGIN REGISTRATION FORM -->
    <form class="register-form" action="{{url(Request::getRequestUri())}}" method="post" style="display: block;">
        <div class="form-actions">
            <button type="button" class="btn btn-default" onclick="login()">{{trans('active.login_button')}}</button>
        </div>
    </form>
    <!-- END REGISTRATION FORM -->
@endsection
@section('script')
    <script type="text/javascript">
        // 登录
        function login() {
            window.location.href = '{{url('login')}}';
        }
    </script>
@endsection