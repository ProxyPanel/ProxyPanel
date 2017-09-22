@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="javascript:;">设置</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/config')}}">系统配置</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PROFILE CONTENT -->
                <div class="profile-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <div class="portlet-title tabbable-line">
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab_1" data-toggle="tab"> 常规设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_2" data-toggle="tab"> 拓展设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_3" data-toggle="tab"> 积分设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_4" data-toggle="tab"> 推广返利设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_5" data-toggle="tab"> 充值二维码设置 </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="portlet-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="website_name" class="col-md-2 control-label">网站名称</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="website_name" value="{{$website_name}}" id="website_name" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setWebsiteName()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 发邮件时展示 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="website_url" class="col-md-2 control-label">网站地址</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="website_url" value="{{$website_url}}" id="website_url" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setWebsiteUrl()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 生成重置密码必备，示例：https://github.com </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_register" class="col-md-2 control-label">用户注册</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_register) checked @endif id="is_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 关闭后无法注册 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_invite_register" class="col-md-2 control-label">邀请注册</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_invite_register) checked @endif id="is_invite_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 启用后必须使用邀请码进行注册 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_active_register" class="col-md-2 control-label">激活账号</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_active_register) checked @endif id="is_active_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 启用后用户需要通过邮件来激活账号 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_reset_password" class="col-md-2 control-label">重置密码</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_reset_password) checked @endif id="is_reset_password" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 启用后不允许用户通过邮件重置密码 </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_2">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="is_rand_port" class="col-md-2 control-label">随机端口</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_rand_port) checked @endif id="is_rand_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 随机生成端口 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_user_rand_port" class="col-md-2 control-label">自定义端口</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_user_rand_port) checked @endif id="is_user_rand_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 用户可以自定义端口 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="default_traffic" class="col-md-2 control-label">注册初始流量</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="default_traffic" value="{{$default_traffic}}" id="default_traffic" />
                                                                <span class="input-group-addon">Byte</span>
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setDefaultTraffic()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 用户注册时默认可用流量 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="invite_num" class="col-md-2 control-label">可生成邀请码数</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="invite_num" value="{{$invite_num}}" id="invite_num" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setInviteNum()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 用户可以生成的邀请码数 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="reset_password_times" class="col-md-2 control-label">重置密码次数</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="reset_password_times" value="{{$reset_password_times}}" id="reset_password_times" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setResetPasswordTimes()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 24小时内可以通过邮件重置密码次数 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="active_times" class="col-md-2 control-label">激活账号次数</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="active_times" value="{{$active_times}}" id="active_times" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setActiveTimes()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 24小时内可以通过邮件激活账号次数 </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_3">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="login_add_score" class="col-md-2 control-label">登录加积分</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($login_add_score) checked @endif id="login_add_score" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 登录时将根据积分范围得到积分 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="login_add_score_range" class="col-md-2 control-label">时间间隔</label>
                                                        <div class="col-md-2">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="login_add_score_range" value="{{$login_add_score_range}}" id="login_add_score_range" />
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setLoginAddScoreRange()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 每隔多久登录才会加积分(单位分钟) </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">积分范围</label>
                                                        <div class="col-md-2">
                                                            <div class="input-group input-large input-daterange">
                                                                <input type="text" class="form-control" name="min_rand_score" value="{{$min_rand_score}}" id="min_rand_score">
                                                                <span class="input-group-addon"> ~ </span>
                                                                <input type="text" class="form-control" name="max_rand_score" value="{{$max_rand_score}}" id="max_rand_score">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_4">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="referral_status" class="col-md-2 control-label">本功能</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($referral_status) checked @endif id="referral_status" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 关闭后用户不可见 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="referral_traffic" class="col-md-2 control-label">注册送流量</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="referral_gift_traffic" value="{{$referral_traffic}}" id="referral_traffic" />
                                                                <span class="input-group-addon">Byte</span>
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setReferralTraffic()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 根据推广链接注册则送多少流量（叠加在默认流量上） </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="referral_percent" class="col-md-2 control-label">返利比例</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="referral_percent" value="{{$referral_percent * 100}}" id="referral_percent" />
                                                                <span class="input-group-addon">%</span>
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setReferralPercent()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 根据推广链接注册的账号每笔消费推广人可以分成的比例 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="referral_money" class="col-md-2 control-label">提现限制</label>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="referral_money" value="{{$referral_money}}" id="referral_money" />
                                                                <span class="input-group-addon">元</span>
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setReferralMoney()">修改</button>
                                                                </span>
                                                            </div>
                                                            <span class="help-block"> 满多少元才可以申请提现 </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_5">
                                            <form action="{{url('admin/setQrcode')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                                <div class="form-body">
                                                    <div class="portlet-body">
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">微信</label>
                                                            <div class="col-md-6">
                                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                                    <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                                        @if ($wechat_qrcode)
                                                                            <img src="{{$wechat_qrcode}}" alt="" />
                                                                        @else
                                                                            <img src="/assets/images/noimage.png" alt="" />
                                                                        @endif
                                                                    </div>
                                                                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                                                    <div>
                                                                        <span class="btn default btn-file">
                                                                            <span class="fileinput-new"> 选择 </span>
                                                                            <span class="fileinput-exists"> 更换 </span>
                                                                            <input type="file" name="wechat_qrcode" id="wechat_qrcode">
                                                                        </span>
                                                                        <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-2 control-label">支付宝</label>
                                                            <div class="col-md-6">
                                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                                    <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                                        @if ($alipay_qrcode)
                                                                            <img src="{{$alipay_qrcode}}" alt="" />
                                                                        @else
                                                                            <img src="/assets/images/noimage.png" alt="" />
                                                                        @endif
                                                                    </div>
                                                                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                                                    <div>
                                                                        <span class="btn default btn-file">
                                                                            <span class="fileinput-new"> 选择 </span>
                                                                            <span class="fileinput-exists"> 更换 </span>
                                                                            <input type="file" name="alipay_qrcode" id="alipay_qrcode">
                                                                        </span>
                                                                        <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-6 col-md-6">
                                                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                            <button type="submit" class="btn green">提 交</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END PROFILE CONTENT -->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 启用、禁用随机端口
        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_rand_port = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_rand_port', value:is_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用自定义端口
        $('#is_user_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_user_rand_port = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_user_rand_port', value:is_user_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用登录加积分
        $('#login_add_score').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var login_add_score = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'login_add_score', value:login_add_score}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用注册
        $('#is_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_register = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_register', value:is_register}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用邀请注册
        $('#is_invite_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_invite_register = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_invite_register', value:is_invite_register}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用用户重置密码
        $('#is_reset_password').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_reset_password = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_reset_password', value:is_reset_password}, function (ret) {
                    if (ret.status == 'fail') {
                        bootbox.alert(ret.message, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 启用、禁用用户激活账号
        $('#is_active_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_active_register = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_active_register', value:is_active_register}, function (ret) {
                    if (ret.status == 'fail') {
                        bootbox.alert(ret.message, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 启用、禁用退关返利用户可见与否
        $('#referral_status').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var referral_status = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_status', value:referral_status}, function (ret) {
                    if (ret.status == 'fail') {
                        bootbox.alert(ret.message, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 设置最小积分
        $("#min_rand_score").change(function () {
            var min_rand_score = $(this).val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'min_rand_score', value:min_rand_score}, function (ret) {
                if (ret.status == 'fail') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        });

        // 设置最大积分
        $("#max_rand_score").change(function () {
            var max_rand_score = $(this).val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'max_rand_score', value:max_rand_score}, function (ret) {
                if (ret.status == 'fail') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        });

        // 设置注册时默认流量
        function setDefaultTraffic() {
            var default_traffic = $("#default_traffic").val();

            $.post("{{url('admin/setDefaultTraffic')}}", {_token:'{{csrf_token()}}', value:default_traffic}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置可生成邀请码数量
        function setInviteNum() {
            var invite_num = $("#invite_num").val();

            $.post("{{url('admin/setInviteNum')}}", {_token:'{{csrf_token()}}', value:invite_num}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置重置密码次数
        function setResetPasswordTimes() {
            var reset_password_times = $("#reset_password_times").val();

            $.post("{{url('admin/setResetPasswordTimes')}}", {_token:'{{csrf_token()}}', value:reset_password_times}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置激活账号次数
        function setActiveTimes() {
            var active_times = $("#active_times").val();

            $.post("{{url('admin/setActiveTimes')}}", {_token:'{{csrf_token()}}', value:active_times}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置网站名称
        function setWebsiteName() {
            var website_name = $("#website_name").val();

            $.post("{{url('admin/setWebsiteName')}}", {_token:'{{csrf_token()}}', value:website_name}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置网站地址
        function setWebsiteUrl() {
            var website_url = $("#website_url").val();

            $.post("{{url('admin/setWebsiteUrl')}}", {_token:'{{csrf_token()}}', value:website_url}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 登录加积分的时间间隔
        function setLoginAddScoreRange() {
            var login_add_score_range = $("#login_add_score_range").val();

            $.post("{{url('admin/setAddScoreRange')}}", {_token:'{{csrf_token()}}', value:login_add_score_range}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置根据推广链接注册送流量
        function setReferralTraffic() {
            var referral_traffic = $("#referral_traffic").val();

            $.post("{{url('admin/setReferralTraffic')}}", {_token:'{{csrf_token()}}', value:referral_traffic}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置根据推广链接注册人每产生一笔消费，则推广人可以获得的返利比例
        function setReferralPercent() {
            var referral_percent = $("#referral_percent").val();

            $.post("{{url('admin/setReferralPercent')}}", {_token:'{{csrf_token()}}', value:referral_percent}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置返利满多少元才可以提现
        function setReferralMoney() {
            var referral_money = $("#referral_money").val();

            $.post("{{url('admin/setReferralMoney')}}", {_token:'{{csrf_token()}}', value:referral_money}, function (ret) {
                if (ret.status == 'success') {
                    bootbox.alert(ret.message, function() {
                        window.location.reload();
                    });
                }
            });
        }
    </script>
@endsection