@extends('auth.layouts')
@section('title', trans('register.title'))
@section('css')
    <link href="/assets/pages/css/login-2.min.css" rel="stylesheet" type="text/css" />
    <style>
        @media screen and (max-height: 575px){  
            .g-recaptcha {
                -webkit-transform:scale(0.81);
                transform:scale(0.81);
                -webkit-transform-origin:0 0; 
                transform-origin:0 0;
            }
        }  
        .geetest_holder.geetest_wind {
            min-width: 245px !important;
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN REGISTRATION FORM -->
    <form class="register-form" id="register-form" action="{{url('register')}}" method="post" style="display: block;">
        @if(\App\Components\Helpers::systemConfig()['is_register'])
            @if($errors->any())
                <div class="alert alert-danger">
                    <span> {{$errors->first()}} </span>
                </div>
            @endif
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">{{trans('register.username')}}</label>
                <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="{{trans('register.username_placeholder')}}" name="username" id="username" value="{{Request::old('username')}}" required />
                <input type="hidden" name="register_token" value="{{Session::get('register_token')}}" />
                <input type="hidden" name="_token" value="{{csrf_token()}}" />
                <input type="hidden" name="aff" value="{{Session::get('register_aff')}}" />
            </div>
            @if(\App\Components\Helpers::systemConfig()['is_verify_register'])
                <div class="form-group" style="margin-bottom:75px;">
                    <label class="control-label visible-ie8 visible-ie9">验证码</label>
                    <input class="form-control placeholder-no-fix" style="width:60%;float:left;" type="text" autocomplete="off" placeholder="验证码" name="verify_code" value="{{Request::old('verify_code')}}" required />
                    <input type="button" class="btn grey" id="sendCode" value="发送" style="float:right;" onclick="sendVerifyCode()" >
                </div>
            @endif
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
            @if(!\App\Components\Helpers::systemConfig()['is_verify_register'])
                @switch(\App\Components\Helpers::systemConfig()['is_captcha'])
                    @case(2)
                        <!-- Geetest -->
                        <div class="form-group">
                            {!! Geetest::render() !!}
                        </div>
                        @break
                    @case(3)
                        <!-- Google noCAPTCHA -->
                        <div class="form-group">
                            {!! NoCaptcha::display() !!}
                            {!! NoCaptcha::renderJs(session::get('locale')) !!}
                        </div>
                        @break
                    @case(1)
                        <!-- Default Captcha -->
                        <div class="form-group" style="margin-bottom:75px;">
                            <label class="control-label visible-ie8 visible-ie9">{{trans('register.captcha')}}</label>
                            <input class="form-control placeholder-no-fix" style="width:60%;float:left;" type="text" autocomplete="off" placeholder="{{trans('register.captcha')}}" name="captcha" value="" required />
                            <img src="{{captcha_src()}}" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('register.captcha')}}" style="float:right;" />
                        </div>
                        @break
                    @default
                @endswitch
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
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 登录
        function login() {
            window.location.href = '{{url('login')}}';
        }

        // 服务条款
        function showTnc() {
            layer.open({
                type: 1,
                title: false, //不显示标题栏
                closeBtn: false,
                area: '500px;',
                shade: 0.8,
                id: 'tnc', //设定一个id，防止重复弹出
                resize: false,
                btn: ['{{trans('register.tnc_title')}}'],
                btnAlign: 'c',
                moveType: 1, //拖拽模式，0或者1
                content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">{!! trans('register.tnc_content') !!}</div>',
                success: function(layero){
                }
            });
        }

        // 发送注册验证码
        function sendVerifyCode() {
            var flag = true; // 请求成功与否标记
            var username = $("#username").val();

            if (username == '' || username == undefined) {
                layer.msg("请填入邮箱", {time: 1000});
                return false;
            }

            $.ajax({
                type: "POST",
                url: "{{url('sendCode')}}",
                async: false,
                data: {_token: '{{csrf_token()}}', username: username},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status == 'fail') {
                        layer.msg(ret.message, {time: 1000});
                        $("#sendCode").attr('disabled', false);
                        flag = false;
                    } else {
                        layer.alert('验证码已发送至您的邮箱，请稍作等待或查看垃圾箱', {icon:1, title:'提示'});
                        $("#sendCode").attr('disabled', true);
                        flag = true;
                    }
                },
                error: function (ret) {
                    layer.msg('请求异常，请刷新页面重试', {time: 1000});
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

        $('#register-form').submit(function(event){
            // 先检查Google reCAPTCHA有没有进行验证
            if ( $('#g-recaptcha-response').val() === '' ) {
                Msg(false, "{{trans('login.required_captcha')}}", 'error');
                return false;
            }
        })

        // 生成提示
        function Msg(clear, msg, type) {
            if ( !clear ) $('.register-form .alert').remove();
            
            var typeClass = 'alert-danger',
                clear = clear ? clear : false,
                $elem = $('.register-form');
            type === 'error' ? typeClass = 'alert-danger' : typeClass = 'alert-success';

            var tpl = '<div class="alert ' + typeClass + '">' +
                    '<button type="button" class="close" onclick="$(this).parent().remove();"></button>' +
                    '<span> ' + msg + ' </span></div>';
            
            if ( !clear ) {
                $elem.prepend(tpl);
            } else {
                $('.register-form .alert').remove();
            }
        }
    </script>
@endsection