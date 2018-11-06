<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>{{trans('register.title')}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components-rounded.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/assets/pages/css/login-2.min.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}" />
</head>

<body class=" login">
<!-- BEGIN LOGO -->
<div class="logo">
    @if(\App\Components\Helpers::systemConfig()['website_home_logo'])
        <a href="{{url('/')}}"> <img src="{{\App\Components\Helpers::systemConfig()['website_home_logo']}}" alt="" style="width:300px; height:90px;"/> </a>
    @else
        <a href="{{url('/')}}"> <img src="/assets/images/home_logo.png" alt="" /> </a>
    @endif
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content">
    <nav style="padding-bottom: 20px;text-align: center;">
        @if(app()->getLocale() == 'zh-CN')
            <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
            <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
            <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
            <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
        @elseif(app()->getLocale() == 'zh-tw')
            <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
            <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
            <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
            <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
        @elseif(app()->getLocale() == 'en')
            <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
            <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
            <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
            <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
        @elseif(app()->getLocale() == 'ko')
            <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
            <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
            <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
            <a href="{{url('lang', ['locale' => 'ja'])}}">日本語</a>
        @elseif(app()->getLocale() == 'ja')
            <a href="{{url('lang', ['locale' => 'zh-CN'])}}">简体中文</a>
            <a href="{{url('lang', ['locale' => 'zh-tw'])}}">繁體中文</a>
            <a href="{{url('lang', ['locale' => 'en'])}}">English</a>
            <a href="{{url('lang', ['locale' => 'ko'])}}">한국어</a>
        @else
        @endif
    </nav>
    <!-- BEGIN REGISTRATION FORM -->
    <form class="register-form" action="{{url('register')}}" method="post" style="display: block;">
        @if(\App\Components\Helpers::systemConfig()['is_register'])
            @if(Session::get('errorMsg'))
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span> {{Session::get('errorMsg')}} </span>
                </div>
            @endif
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">{{trans('register.username')}}</label>
                <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="{{trans('register.username_placeholder')}}" name="username" id="username" value="{{Request::old('username')}}" required />
                <input type="hidden" name="register_token" value="{{Session::get('register_token')}}" />
                <input type="hidden" name="_token" value="{{csrf_token()}}" />
                <input type="hidden" name="aff" value="{{Session::get('register_aff')}}" />
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">{{trans('register.password')}}</label>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="{{trans('register.password')}}" name="password" value="{{Request::old('password')}}" required />
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">{{trans('register.retype_password')}}</label>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="{{trans('register.retype_password')}}" name="repassword" value="{{Request::old('repassword')}}" required />
            </div>
            @if(\App\Components\Helpers::systemConfig()['is_invite_register'])
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">{{trans('register.code')}}</label>
                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="{{trans('register.code')}}" name="code" value="{{Request::old('code') ? Request::old('code') : Request::get('code')}}" @if(\App\Components\Helpers::systemConfig()['is_invite_register'] == 2) required @endif />
                </div>
                @if(\App\Components\Helpers::systemConfig()['is_free_code'])
                    <p class="hint"> <a href="{{url('free')}}" target="_blank">{{trans('register.get_free_code')}}</a> </p>
                @endif
            @endif
            @if(\App\Components\Helpers::systemConfig()['is_verify_register'])
                <div class="form-group" style="margin-bottom:75px;">
                    <label class="control-label visible-ie8 visible-ie9">验证码</label>
                    <input class="form-control placeholder-no-fix" style="width:60%;float:left;" type="text" autocomplete="off" placeholder="验证码" name="verify_code" value="" required />
                    <input type="button" class="btn grey" id="sendCode" value="发送" style="float:right;" onclick="sendVerifyCode()" >
                </div>
            @elseif(\App\Components\Helpers::systemConfig()['is_captcha'])
                <div class="form-group" style="margin-bottom:75px;">
                    <label class="control-label visible-ie8 visible-ie9">{{trans('register.captcha')}}</label>
                    <input class="form-control placeholder-no-fix" style="width:60%;float:left;" type="text" autocomplete="off" placeholder="{{trans('register.captcha')}}" name="captcha" value="" required />
                    <img src="{{captcha_src()}}" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('register.captcha')}}" style="float:right;" />
                </div>
            @endif
            <div class="form-group margin-top-20 margin-bottom-20">
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="checkbox" name="tnc" checked disabled /> {{trans('register.tnc_button')}}
                    <a href="javascript:showTnc();"> {{trans('register.tnc_link')}} </a>
                    <span></span>
                </label>
            </div>
        @else
            <div class="alert alert-danger">
                <span> {{trans('register.register_alter')}} </span>
            </div>
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-default" onclick="login()">{{trans('register.back')}}</button>
            @if(\App\Components\Helpers::systemConfig()['is_register'])
                <button type="submit" class="btn red uppercase pull-right">{{trans('register.submit')}}</button>
            @endif
        </div>
    </form>
    <!-- END REGISTRATION FORM -->
</div>

<!-- END LOGIN -->
<!--[if lt IE 9]>
<script src="/assets/global/plugins/respond.min.js"></script>
<script src="/assets/global/plugins/excanvas.min.js"></script>
<script src="/assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
<script src="/js/layer/layer.js" type="text/javascript"></script>
<script type="text/javascript">
    // 登录
    function login() {
        window.location.href = '{{url('login')}}';
    }

    // 服务条款
    function showTnc() {
        layer.open({
            type: 1
            ,title: false //不显示标题栏
            ,closeBtn: false
            ,area: '500px;'
            ,shade: 0.8
            ,id: 'tnc' //设定一个id，防止重复弹出
            ,resize: false
            ,btn: ['{{trans('register.tnc_title')}}']
            ,btnAlign: 'c'
            ,moveType: 1 //拖拽模式，0或者1
            ,content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">{!! trans('register.tnc_content') !!}</div>'
            ,success: function(layero){
//                var btn = layero.find('.layui-layer-btn');
//                btn.find('.layui-layer-btn0').attr({
//                    href: 'http://www.layui.com/'
//                    ,target: '_blank'
//                });
            }
        });
    }

    // 发送注册验证码
    function sendVerifyCode() {
        var flag = true; // 请求成功与否标记
        var token = '{{csrf_token()}}';
        var username = $("#username").val();
        $.ajax({
            type: "POST",
            url: "{{url('sendCode')}}",
            async: false,
            data: {_token: token, username: username},
            dataType: 'json',
            success: function (ret) {
                if (ret.status == 'fail') {
                    layer.msg(ret.message, {time: 1000});
                    $("#sendCode").attr('disabled', false);
                    flag = false;
                } else {
                    $("#sendCode").attr('disabled', true);
                    flag = true;
                }
            },
            error: function (ret) {
                layer.msg('请求异常，请重试', {time: 1000});
                flag = false;
            }
        });

        // 请求成功才开始倒计时
        if (flag) {
            // 60秒后重新发送
            var left_time = 60;
            var tt = window.setInterval(function () {
                left_time = left_time - 1;
                if (left_time <= 0) {
                    window.clearInterval(tt);
                    $("#sendCode").attr('disabled', false).val('发送');
                } else {
                    $("#sendCode").val(left_time);
                }
            }, 1000);
        }
    }
</script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-122312249-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-122312249-1');
</script>


<!-- 统计 -->
{!! \App\Components\Helpers::systemConfig()['website_analytics'] !!}
<!-- 客服 -->
{!! \App\Components\Helpers::systemConfig()['website_customer_service'] !!}
</body>

</html>