@extends('auth.layouts')
@section('title', trans('home.reset_password_title'))
@section('css')
    <link href="/assets/pages/css/login-2.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN REGISTRATION FORM -->
    <form class="register-form" action="{{url(Request::getRequestUri())}}" method="post" style="display: block;">
        @if(Session::get('successMsg'))
            <div class="alert alert-success">
                <span> {{Session::get('successMsg')}} </span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <span> {{$errors->first()}} </span>
            </div>
        @endif
        @if ($verify->status > 0 && count($errors) <= 0 && empty(Session::get('successMsg')))
            <div class="alert alert-danger">
                <span> 该链接已失效 </span>
            </div>
        @else
            <div class="form-title">
                <span class="form-title">设置新密码</span>
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">密码</label>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="密码" name="password" value="" required />
                <input type="hidden" name="_token" value="{{csrf_token()}}" />
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">重复密码</label>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="重复密码" name="repassword" value="" required />
            </div>
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-default" onclick="login()">返 回</button>
            @if ($verify->status == 0)
                <button type="submit" class="btn red uppercase pull-right">提 交</button>
            @endif
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