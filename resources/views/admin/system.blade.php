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
                                            <a href="#tab_5" data-toggle="tab"> 警告提醒设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_6" data-toggle="tab"> 充值二维码设置 </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="portlet-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="website_name" class="col-md-3 control-label">网站名称</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="website_name" value="{{$website_name}}" id="website_name" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setWebsiteName()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 发邮件时展示 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="website_url" class="col-md-3 control-label">网站地址</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="website_url" value="{{$website_url}}" id="website_url" />
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setWebsiteUrl()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 生成重置密码必备，示例：https://github.com </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_register" class="col-md-3 control-label">用户注册</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_register) checked @endif id="is_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 关闭后无法注册 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="is_active_register" class="col-md-3 control-label">激活账号</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_active_register) checked @endif id="is_active_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后用户需要通过邮件来激活账号 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_invite_register" class="col-md-3 control-label">邀请注册</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_invite_register) checked @endif id="is_invite_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后必须使用邀请码进行注册 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="is_reset_password" class="col-md-3 control-label">重置密码</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_reset_password) checked @endif id="is_reset_password" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后用户可以通过邮件重置密码 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_captcha" class="col-md-3 control-label">验证码</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_captcha) checked @endif id="is_captcha" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后登录、注册需要输入验证码 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_2">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_rand_port" class="col-md-3 control-label">随机端口</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_rand_port) checked @endif id="is_rand_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 注册、添加账号时随机生成端口 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="col-md-3 control-label">端口范围</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group input-large input-daterange">
                                                                    <input type="text" class="form-control" name="min_port" value="{{$min_port}}" id="min_port">
                                                                    <span class="input-group-addon"> ~ </span>
                                                                    <input type="text" class="form-control" name="max_port" value="{{$max_port}}" id="max_port">
                                                                </div>
                                                                <span class="help-block"> 端口范围：1000 - 65535 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--
                                                    <div class="form-group">
                                                        <label for="is_user_rand_port" class="col-md-2 control-label">自定义端口</label>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" class="make-switch" @if($is_user_rand_port) checked @endif id="is_user_rand_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                            <span class="help-block"> 用户可以自定义端口 </span>
                                                        </div>
                                                    </div>
                                                    -->
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="default_days" class="col-md-3 control-label">初始有效期</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="default_days" value="{{$default_days}}" id="default_days" />
                                                                    <span class="input-group-addon">天</span>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setDefaultDays()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 用户注册时默认SS(R)有效天数 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="default_traffic" class="col-md-3 control-label">初始流量</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="default_traffic" value="{{$default_traffic}}" id="default_traffic" />
                                                                    <span class="input-group-addon">MiB</span>
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setDefaultTraffic()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 用户注册时默认可用流量 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="invite_num" class="col-md-3 control-label">可生成邀请码数</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="invite_num" value="{{$invite_num}}" id="invite_num" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setInviteNum()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 用户可以生成的邀请码数 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="reset_password_times" class="col-md-3 control-label">重置密码次数</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="reset_password_times" value="{{$reset_password_times}}" id="reset_password_times" />
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setResetPasswordTimes()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 24小时内可以通过邮件重置密码次数 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="active_times" class="col-md-3 control-label">激活账号次数</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="active_times" value="{{$active_times}}" id="active_times" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setActiveTimes()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 24小时内可以通过邮件激活账号次数 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="subscribe_max" class="col-md-3 control-label">订阅节点数</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="subscribe_max" value="{{$subscribe_max}}" id="subscribe_max" />
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setSubscribeMax()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 客户端订阅时随机取得几个节点 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_clear_log" class="col-md-3 control-label">自动清除日志</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_clear_log) checked @endif id="is_clear_log" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后自动清除无用日志（推荐） </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_3">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="login_add_score" class="col-md-3 control-label">登录加积分</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($login_add_score) checked @endif id="login_add_score" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 登录时将根据积分范围得到积分 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="login_add_score_range" class="col-md-3 control-label">时间间隔</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="login_add_score_range" value="{{$login_add_score_range}}" id="login_add_score_range" />
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setLoginAddScoreRange()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 每隔多久登录才会加积分(单位分钟) </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label class="col-md-3 control-label">积分范围</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group input-large input-daterange">
                                                                    <input type="text" class="form-control" name="min_rand_score" value="{{$min_rand_score}}" id="min_rand_score">
                                                                    <span class="input-group-addon"> ~ </span>
                                                                    <input type="text" class="form-control" name="max_rand_score" value="{{$max_rand_score}}" id="max_rand_score">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_4">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="referral_status" class="col-md-3 control-label">本功能</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($referral_status) checked @endif id="referral_status" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 关闭后用户不可见 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="referral_traffic" class="col-md-3 control-label">注册送流量</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="referral_gift_traffic" value="{{$referral_traffic}}" id="referral_traffic" />
                                                                    <span class="input-group-addon">MiB</span>
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setReferralTraffic()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 根据推广链接注册则送多少流量（叠加在默认流量上） </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="referral_percent" class="col-md-3 control-label">返利比例</label>
                                                            <div class="col-md-9">
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
                                                        <div class="col-md-6">
                                                            <label for="referral_money" class="col-md-3 control-label">提现限制</label>
                                                            <div class="col-md-9">
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
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_5">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="reset_traffic" class="col-md-3 control-label">流量自动重置</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($reset_traffic) checked @endif id="reset_traffic" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 账号会按其购买套餐的日期自动重置账号可用流量 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="is_traffic_ban" class="col-md-3 control-label">异常自动封号</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_traffic_ban) checked @endif id="is_traffic_ban" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 24小时内流量超过异常阈值则自动封号 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="expire_warning" class="col-md-3 control-label">过期警告</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($expire_warning) checked @endif id="expire_warning" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后账号距到期还剩阈值设置的值时自动发邮件提醒用户 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="expire_days" class="col-md-3 control-label">过期警告阈值</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="expire_days" value="{{$expire_days}}" id="expire_days" />
                                                                    <span class="input-group-addon">天</span>
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setExpireDays()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 账号距离过期还差多少天时发警告邮件 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="traffic_warning" class="col-md-3 control-label">流量警告</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($traffic_warning) checked @endif id="traffic_warning" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后账号已使用流量超过警告阈值时自动发邮件提醒用户 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="traffic_warning_percent" class="col-md-3 control-label">流量警告阈值</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="traffic_warning_percent" value="{{$traffic_warning_percent}}" id="traffic_warning_percent" />
                                                                    <span class="input-group-addon">%</span>
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setTrafficWarningPercent()">修改</button>
                                                                </span>
                                                                </div>
                                                                <span class="help-block"> 建议设置在70%~90% </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="traffic_ban_value" class="col-md-3 control-label">流量异常阈值</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="traffic_ban_value" value="{{$traffic_ban_value}}" id="traffic_ban_value" />
                                                                    <span class="input-group-addon">GiB</span>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setTrafficBanValue()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 24小时内超过该值，则触发自动封号 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="traffic_ban_time" class="col-md-3 control-label">封号时长</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="traffic_ban_time" value="{{$traffic_ban_time}}" id="traffic_ban_time" />
                                                                    <span class="input-group-addon">分钟</span>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setTrafficBanTime()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 触发流量异常导致账号被封禁的时长，到期后自动解封 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_6">
                                            <form action="{{url('admin/setQrcode')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                                <div class="form-body">
                                                    <div class="portlet-body">
                                                        <div class="form-group">
                                                            <div class="col-md-6">
                                                                <label class="control-label col-md-3">微信</label>
                                                                <div class="col-md-9">
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
                                                            <div class="col-md-6"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-6">
                                                                <label class="control-label col-md-3">支付宝</label>
                                                                <div class="col-md-9">
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
                                                            <div class="col-md-6"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-2 col-md-9">
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
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

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
                        layer.msg(ret.message, {time:1000}, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 启用、禁用验证码
        $('#is_captcha').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_captcha = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_captcha', value:is_captcha}, function (ret) {
                    if (ret.status == 'fail') {
                        layer.msg(ret.message, {time:1000}, function() {
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
                        layer.msg(ret.message, {time:1000}, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 启用、禁用账号到期自动邮件提醒
        $('#expire_warning').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var expire_warning = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'expire_warning', value:expire_warning}, function (ret) {
                    if (ret.status == 'fail') {
                        layer.msg(ret.message, {time:1000}, function() {
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
                        layer.msg(ret.message, {time:1000}, function() {
                            window.location.reload();
                        });
                    }
                });
            }
        });

        // 启用、禁用随机端口
        $('#traffic_warning').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var traffic_warning = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_warning', value:traffic_warning}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用随机端口
        $('#is_clear_log').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_clear_log = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_clear_log', value:is_clear_log}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用流量自动重置
        $('#reset_traffic').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var reset_traffic = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'reset_traffic', value:reset_traffic}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 启用、禁用流量异常自动封号
        $('#is_traffic_ban').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_traffic_ban = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_traffic_ban', value:is_traffic_ban}, function (ret) {
                    console.log(ret);
                });
            }
        });

        // 流量异常阈值
        function setTrafficBanValue() {
            var traffic_ban_value = $("#traffic_ban_value").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_ban_value', value:traffic_ban_value}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置账号封号时长
        function setTrafficBanTime() {
            var traffic_ban_time = $("#traffic_ban_time").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_ban_time', value:traffic_ban_time}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置最小积分
        $("#min_rand_score").change(function () {
            var min_rand_score = $(this).val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'min_rand_score', value:min_rand_score}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 设置最大积分
        $("#max_rand_score").change(function () {
            var max_rand_score = $(this).val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'max_rand_score', value:max_rand_score}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 设置最小端口
        $("#min_port").change(function () {
            var min_port = $(this).val();

            if (parseInt(min_port) < 1000) {
                layer.msg('最小端口不能小于1000', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'min_port', value:min_port}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 设置最大端口
        $("#max_port").change(function () {
            var min_port = $("#min_port").val();
            var max_port = $(this).val();

            // 最大端口必须大于最小端口
            if (parseInt(max_port) <= parseInt(min_port)) {
                layer.msg('必须大于最小端口', {time:1000});
                return ;
            }

            if (parseInt(max_port) > 65535) {
                layer.msg('最大端口不能大于65535', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'max_port', value:max_port}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 设置注册时默认有效期
        function setDefaultDays() {
            var default_days = $("#default_days").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'default_days', value:default_days}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置注册时默认流量
        function setDefaultTraffic() {
            var default_traffic = $("#default_traffic").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'default_traffic', value:default_traffic}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置可生成邀请码数量
        function setInviteNum() {
            var invite_num = $("#invite_num").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'invite_num', value:invite_num}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置重置密码次数
        function setResetPasswordTimes() {
            var reset_password_times = $("#reset_password_times").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'reset_password_times', value:reset_password_times}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置激活账号次数
        function setActiveTimes() {
            var active_times = $("#active_times").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'active_times', value:active_times}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置节点订阅随机展示节点数
        function setSubscribeMax() {
            var subscribe_max = $("#subscribe_max").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'subscribe_max', value:subscribe_max}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置流量警告阈值
        function setTrafficWarningPercent() {
            var traffic_warning_percent = $("#traffic_warning_percent").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_warning_percent', value:traffic_warning_percent}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置账号过期提醒阈值
        function setExpireDays() {
            var expire_days = $("#expire_days").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'expire_days', value:expire_days}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置网站名称
        function setWebsiteName() {
            var website_name = $("#website_name").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'website_name', value:website_name}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置网站地址
        function setWebsiteUrl() {
            var website_url = $("#website_url").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'website_url', value:website_url}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 登录加积分的时间间隔
        function setLoginAddScoreRange() {
            var login_add_score_range = $("#login_add_score_range").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'login_add_score_range', value:login_add_score_range}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置根据推广链接注册送流量
        function setReferralTraffic() {
            var referral_traffic = $("#referral_traffic").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_traffic', value:referral_traffic}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
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
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }

        // 设置返利满多少元才可以提现
        function setReferralMoney() {
            var referral_money = $("#referral_money").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_money', value:referral_money}, function (ret) {
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1000}, function() {
                        window.location.reload();
                    });
                }
            });
        }
    </script>
@endsection