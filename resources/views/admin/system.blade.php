@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
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
                                    </ul>
                                </div>
                                <div class="portlet-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <form action="#" method="post" class="form-horizontal" onsubmit="return do_submit();">
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
                                                        <label for="invite_num" class="col-md-2 control-label">可生成邀请码数</label>
                                                        <div class="col-md-2">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="invite_num" value="{{$invite_num}}" id="invite_num" />
                                                                <span class="input-group-btn">
                                                        <button class="btn btn-success" type="button" onclick="setInviteNum()">修改</button>
                                                    </span>
                                                            </div>
                                                            <span class="help-block"> 用户可以生成的邀请码数 </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_2">
                                            <form action="#" method="post" class="form-horizontal" onsubmit="return do_submit();">
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
                                                        <label for="is_reset_password" class="col-md-2 control-label">重置密码</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_reset_password) checked @endif id="is_reset_password" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 开启后不允许用户重置密码 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="reset_password_times" class="col-md-2 control-label">重置密码次数</label>
                                                        <div class="col-md-2">
                                                            <div class="input-group">
                                                                <input class="form-control" type="text" name="reset_password_times" value="{{$reset_password_times}}" id="reset_password_times" />
                                                                <span class="input-group-btn">
                                                        <button class="btn btn-success" type="button" onclick="setResetPasswordTimes()">修改</button>
                                                    </span>
                                                            </div>
                                                            <span class="help-block"> 用户可以重置密码次数 </span>
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

    <script type="text/javascript">
        // 启用、禁用随机端口
        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_rand_port = 0;

                if (state) {
                    is_rand_port = 1;
                }

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_rand_port', value:is_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用自定义端口
        $('#is_user_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_user_rand_port = 0;

                if (state) {
                    is_user_rand_port = 1;
                }

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_user_rand_port', value:is_user_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用注册
        $('#is_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_register = 0;

                if (state) {
                    is_register = 1;
                }

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_register', value:is_register}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用邀请注册
        $('#is_invite_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_invite_register = 0;

                if (state) {
                    is_invite_register = 1;
                }

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_invite_register', value:is_invite_register}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用用户重置密码
        $('#is_reset_password').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_reset_password = 0;

                if (state) {
                    is_reset_password = 1;
                }

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_reset_password', value:is_reset_password}, function (ret) {
                    if (ret.status == 'fail') {
                        bootbox.alert(ret.message, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

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
    </script>
@endsection