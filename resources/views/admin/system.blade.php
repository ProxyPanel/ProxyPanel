@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
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
                                            <a href="#tab_6" data-toggle="tab"> 自动化任务 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_7" data-toggle="tab"> 有赞云支付设置 </a>
                                        </li>
                                        <li>
                                            <a href="#tab_8" data-toggle="tab"> LOGO、客服、统计设置 </a>
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
                                                                <span class="help-block"> 生成重置密码、有赞云支付必备，示例：https://www.ssrpanel.com </span>
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
                                                            <label for="is_invite_register" class="col-md-3 control-label">邀请注册</label>
                                                            <div class="col-md-9">
                                                                <select id="is_invite_register" class="form-control select2" name="is_invite_register">
                                                                    <option value="0" @if($is_invite_register == '0') selected @endif>关闭</option>
                                                                    <option value="1" @if($is_invite_register == '1') selected @endif>可选</option>
                                                                    <option value="2" @if($is_invite_register == '2') selected @endif>必须</option>
                                                                </select>
                                                                <span class="help-block"> 启用后必须使用邀请码进行注册 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_active_register" class="col-md-3 control-label">激活账号</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_active_register) checked @endif id="is_active_register" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后用户需要通过邮件来激活账号 </span>
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
                                                            <label for="is_free_code" class="col-md-3 control-label">免费邀请码</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_free_code) checked @endif id="is_free_code" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 关闭后免费邀请码不可见 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_forbid_china" class="col-md-3 control-label">阻止大陆访问</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_forbid_china) checked @endif id="is_forbid_china" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 开启后大陆IP禁止访问 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="is_forbid_oversea" class="col-md-3 control-label">阻止海外访问</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_forbid_oversea) checked @endif id="is_forbid_oversea" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 开启后海外IP(含港澳台)禁止访问 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_forbid_robot" class="col-md-3 control-label">阻止机器人访问</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_forbid_robot) checked @endif id="is_forbid_robot" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 如果是机器人、爬虫、代理访问网站则会抛出403错误 </span>
                                                            </div>
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
                                                                <span class="help-block"> 注册、添加用户时随机生成端口 </span>
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
                                                                <span class="help-block"> 用户注册时默认账户有效期，为0即当天到期 </span>
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
                                                            <label for="register_ip_limit" class="col-md-3 control-label">同IP注册限制</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="register_ip_limit" value="{{$register_ip_limit}}" id="register_ip_limit" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setRegisterIpLimit()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 同IP在24小时内允许注册数量，为0时不限制 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="subscribe_domain" class="col-md-3 control-label">节点订阅地址</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="subscribe_domain" value="{{$subscribe_domain}}" id="subscribe_domain" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setSubscribeDomain()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> （推荐）防止面板域名被投毒后无法正常订阅，需带http://或https:// </span>
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
                                                            <label for="initial_labels_for_user" class="col-md-3 control-label">用户初始标签</label>
                                                            <div class="col-md-9">
                                                                <select id="initial_labels_for_user" class="form-control select2-multiple" name="initial_labels_for_user" multiple="multiple">
                                                                    @foreach($label_list as $label)
                                                                        <option value="{{$label->id}}"
                                                                            @if (in_array($label->id, explode(',', $initial_labels_for_user)))
                                                                            selected
                                                                            @endif
                                                                        >{{$label->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span class="help-block"> 注册用户时的初始标签 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="goods_purchase_limit_strategy" class="col-md-3 control-label">商品限购</label>
                                                            <div class="col-md-9">
                                                                <select id="goods_purchase_limit_strategy" class="form-control select2" name="goods_purchase_limit_strategy">
                                                                    <option value="none"
                                                                            @if ($goods_purchase_limit_strategy == 'none')
                                                                            selected
                                                                            @endif
                                                                    >不限制</option>
                                                                    <option value="package"
                                                                            @if ($goods_purchase_limit_strategy == 'package')
                                                                            selected
                                                                            @endif
                                                                    >仅限套餐</option>
                                                                    <option value="free"
                                                                            @if ($goods_purchase_limit_strategy == 'free')
                                                                            selected
                                                                            @endif
                                                                    >仅限免费商品</option>
                                                                    <option value="package&free"
                                                                            @if ($goods_purchase_limit_strategy == 'package&free')
                                                                            selected
                                                                            @endif
                                                                    >限套餐和免费商品</option>
                                                                    <option value="all"
                                                                            @if ($goods_purchase_limit_strategy == 'all')
                                                                            selected
                                                                            @endif
                                                                    >限全部商品</option>
                                                                </select>
                                                                <span class="help-block"> 是否限制用户重复购买商品，限制后用户不可重复购买已购买的、尚在有效期的商品 </span>
                                                            </div>
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
                                                                <span class="help-block"> 登录时将根据积分范围随机得到积分 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="login_add_score_range" class="col-md-3 control-label">时间间隔</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="login_add_score_range" value="{{$login_add_score_range}}" id="login_add_score_range" />
                                                                    <span class="input-group-addon">分钟</span>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setLoginAddScoreRange()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 间隔多久登录才会加积分 </span>
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
                                                                <span class="help-block"> 关闭后用户不可见，但是不影响其正常邀请返利 </span>
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
                                                                <span class="help-block"> 根据推广链接、邀请码注册则赠送相应的流量 </span>
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
                                                            <label for="expire_warning" class="col-md-3 control-label">用户过期警告</label>
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
                                                            <label for="traffic_warning" class="col-md-3 control-label">用户流量警告</label>
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
                                                            <label for="is_node_crash_warning" class="col-md-3 control-label">节点宕机提醒</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_node_crash_warning) checked @endif id="is_node_crash_warning" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后如果节点宕机则发出提醒邮件 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="crash_warning_email" class="col-md-3 control-label">管理员收信地址</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="crash_warning_email" value="{{$crash_warning_email}}" id="crash_warning_email" placeholder="master@ssrpanel.com" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setCrashWarningEmail()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 填写此值则节点宕机、工单回复会自动提醒 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_namesilo" class="col-md-3 control-label">Namesilo</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_namesilo) checked @endif id="is_namesilo" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 添加、编辑节点的绑定域名时自动更新域名DNS记录值为节点IP（<a href="https://www.namesilo.com/account_api.php?rid=326ec20pa" target="_blank">创建API KEY</a>） </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="namesilo_key" class="col-md-3 control-label">Namesilo API KEY</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="namesilo_key" value="{{$namesilo_key}}" id="namesilo_key" placeholder="填入Namesilo上申请的API KEY" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setNamesiloKey()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 域名必须是<a href="https://www.namesilo.com/?rid=326ec20pa" target="_blank">www.namesilo.com</a>上购买的 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_server_chan" class="col-md-3 control-label">ServerChan</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_server_chan) checked @endif id="is_server_chan" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 推送节点宕机提醒、用户流量异常警告（<a href="http://sc.ftqq.com" target="_blank">绑定微信</a>） </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="server_chan_key" class="col-md-3 control-label">SCKEY</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="server_chan_key" value="{{$server_chan_key}}" id="server_chan_key" placeholder="请到ServerChan申请" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setServerChanKey()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 启用ServerChan，请务必填入本值（<a href="http://sc.ftqq.com" target="_blank">申请SCKEY</a>） </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_push_bear" class="col-md-3 control-label">PushBear</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_push_bear) checked @endif id="is_push_bear" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 使用PushBear推送微信消息给用户（<a href="https://pushbear.ftqq.com/admin/#/signin" target="_blank">创建消息通道</a>） </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="push_bear_send_key" class="col-md-3 control-label">PushBear SendKey</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="push_bear_send_key" value="{{$push_bear_send_key}}" id="push_bear_send_key" placeholder="创建消息通道后即可获取" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setPushBearSendKey()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 启用PushBear，请务必填入本值 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="push_bear_qrcode" class="col-md-3 control-label">PushBear订阅二维码</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="push_bear_qrcode" value="{{$push_bear_qrcode}}" id="push_bear_qrcode" placeholder="填入创建好的消息通道的二维码URL" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setPushBearQrCode()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 创建消息通道后，在二维码上点击右键“复制图片地址”并粘贴至此处 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6"></div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_tcp_check" class="col-md-3 control-label">TCP阻断检测</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_tcp_check) checked @endif id="is_tcp_check" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 每小时自动检测节点是否被TCP阻断并提醒 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="tcp_check_warning_times" class="col-md-3 control-label">阻断检测提醒</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="tcp_check_warning_times" value="{{$tcp_check_warning_times}}" id="tcp_check_warning_times" placeholder="" />
                                                                    <span class="input-group-addon">次</span>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setTcpCheckWarningTimes()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 提醒几次后自动下线节点，为0时不限制，不超过12 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_6">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_clear_log" class="col-md-3 control-label">自动清除日志</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_clear_log) checked @endif id="is_clear_log" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> （推荐）启用后自动清除无用日志 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="reset_traffic" class="col-md-3 control-label">流量自动重置</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($reset_traffic) checked @endif id="reset_traffic" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 用户会按其购买套餐的日期自动重置可用流量 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_subscribe_ban" class="col-md-3 control-label">订阅异常自动封禁</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_subscribe_ban) checked @endif id="is_subscribe_ban" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 启用后用户订阅链接请求超过设定阈值则自动封禁 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="subscribe_ban_times" class="col-md-3 control-label">订阅请求阈值</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="subscribe_ban_times" value="{{$subscribe_ban_times}}" id="subscribe_ban_times" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setSubscribeBanTimes()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 24小时内订阅链接请求次数限制 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_traffic_ban" class="col-md-3 control-label">异常自动封号</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_traffic_ban) checked @endif id="is_traffic_ban" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 1小时内流量超过异常阈值则自动封号（仅禁用代理） </span>
                                                            </div>
                                                        </div>
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
                                                                <span class="help-block"> 1小时内超过该值，则触发自动封号 </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
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
                                                                <span class="help-block"> 触发流量异常导致用户被封禁的时长，到期后自动解封 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="auto_release_port" class="col-md-3 control-label">端口自动释放</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($auto_release_port) checked @endif id="auto_release_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 被封禁和过期一个月的用户端口自动释放 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="is_ban_status" class="col-md-3 control-label">过期自动封禁</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_ban_status) checked @endif id="is_ban_status" data-on-color="danger" data-off-color="danger" data-on-text="封禁整个账号" data-off-text="仅封禁代理">
                                                                <span class="help-block"> (慎重)封禁整个账号会重置账号的所有数据且会导致用户无法登录 </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_7">
                                            <form action="#" method="post" class="form-horizontal">
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info" style="text-align: center;">
                                                                请在<a href="https://console.youzanyun.com/login" target="_blank">有赞云</a>后台设置应用的推送网址为：{{$website_url . '/api/yzy'}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="is_youzan" class="col-md-3 control-label">本功能</label>
                                                            <div class="col-md-9">
                                                                <input type="checkbox" class="make-switch" @if($is_youzan) checked @endif id="is_youzan" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                                                <span class="help-block"> 请先到<a href="https://console.youzanyun.com/dashboard">有赞云</a>申请client_id和client_secret并绑定店铺 </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="kdt_id" class="col-md-3 control-label">kdt_id</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="kdt_id" value="{{$kdt_id}}" id="kdt_id" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setKdtId()">修改</button>
                                                                    </span>
                                                                </div>
                                                                <span class="help-block"> 即：授权店铺id </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6">
                                                            <label for="youzan_client_id" class="col-md-3 control-label">client_id</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="youzan_client_id" value="{{$youzan_client_id}}" id="youzan_client_id" />
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-success" type="button" onclick="setYouzanClientId()">修改</button>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="youzan_client_secret" class="col-md-3 control-label">client_secret</label>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" type="text" name="youzan_client_secret" value="{{$youzan_client_secret}}" id="youzan_client_secret" />
                                                                    <span class="input-group-btn">
                                                                    <button class="btn btn-success" type="button" onclick="setYouzanClientSecret()">修改</button>
                                                                </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_8">
                                            <form action="{{url('admin/setExtend')}}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form" id="setExtend">
                                                {{csrf_field()}}
                                                <div class="form-group">
                                                    <label class="control-label col-md-2">首页LOGO</label>
                                                    <div class="col-md-8">
                                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                                @if ($website_home_logo)
                                                                    <img src="{{$website_home_logo}}" alt="" />
                                                                @else
                                                                    <img src="/assets/images/noimage.png" alt="" />
                                                                @endif
                                                            </div>
                                                            <span class="help-block"> 推荐尺寸：300 X 90，透明背景 </span>
                                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                                            <div>
                                                                <span class="btn default btn-file">
                                                                    <span class="fileinput-new"> 选择 </span>
                                                                    <span class="fileinput-exists"> 更换 </span>
                                                                    <input type="file" name="website_home_logo" id="website_home_logo">
                                                                </span>
                                                                <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-2">站内LOGO</label>
                                                    <div class="col-md-8">
                                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                                @if ($website_logo)
                                                                    <img src="{{$website_logo}}" alt="" />
                                                                @else
                                                                    <img src="/assets/images/noimage.png" alt="" />
                                                                @endif
                                                            </div>
                                                            <span class="help-block"> 推荐尺寸：150 X 30，透明背景 </span>
                                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                                            <div>
                                                                <span class="btn default btn-file">
                                                                    <span class="fileinput-new"> 选择 </span>
                                                                    <span class="fileinput-exists"> 更换 </span>
                                                                    <input type="file" name="website_logo" id="website_logo">
                                                                </span>
                                                                <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="website_analytics" class="col-md-2 control-label">统计代码</label>
                                                    <div class="col-md-8">
                                                        <textarea class="form-control" rows="10" name="website_analytics" id="website_analytics">{{$website_analytics}}</textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="website_customer_service" class="col-md-2 control-label">客服代码</label>
                                                    <div class="col-md-8">
                                                        <textarea class="form-control" rows="10" name="website_customer_service" id="website_customer_service">{{$website_customer_service}}</textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-offset-2 col-md-10">
                                                        <button type="submit" class="btn blue">提交</button>
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
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 注册的默认标签
        $('#initial_labels_for_user').select2({
            theme: 'bootstrap',
            placeholder: '设置后则可见相同标签的节点',
            allowClear: true,
            width:'100%'
        }).change(function () {
            var initial_labels_for_user = $(this).val() ? $(this).val().join(',') : '';

            console.log(initial_labels_for_user);
            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'initial_labels_for_user', value:initial_labels_for_user}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 启用、禁用随机端口
        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_rand_port = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_rand_port', value:is_rand_port}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用屏蔽大陆访问
        $('#is_forbid_china').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_forbid_china = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_forbid_china', value:is_forbid_china}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用屏蔽海外访问
        $('#is_forbid_oversea').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_forbid_oversea = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_forbid_oversea', value:is_forbid_oversea}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用机器人访问
        $('#is_forbid_robot').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_forbid_robot = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_forbid_robot', value:is_forbid_robot}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用自定义端口
        $('#is_user_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_user_rand_port = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_user_rand_port', value:is_user_rand_port}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用登录加积分
        $('#login_add_score').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var login_add_score = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'login_add_score', value:login_add_score}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用注册
        $('#is_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_register = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_register', value:is_register}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、可选、禁用邀请注册
        $('#is_invite_register').change(function() {
            var is_invite_register = $(this).val();

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_invite_register', value:is_invite_register}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                    }
                });
            });
        });

        // 启用、禁用用户重置密码
        $('#is_reset_password').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_reset_password = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_reset_password', value:is_reset_password}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用验证码
        $('#is_captcha').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_captcha = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_captcha', value:is_captcha}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用免费邀请码
        $('#is_free_code').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_free_code = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_free_code', value:is_free_code}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用用户激活用户
        $('#is_active_register').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_active_register = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_active_register', value:is_active_register}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用用户到期自动邮件提醒
        $('#expire_warning').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var expire_warning = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'expire_warning', value:expire_warning}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用节点宕机发件提醒管理员
        $('#is_node_crash_warning').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_node_crash_warning = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_node_crash_warning', value:is_node_crash_warning}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用节点宕机发ServerChan微信消息提醒
        $('#is_server_chan').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_server_chan = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_server_chan', value:is_server_chan}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用Namesilo
        $('#is_namesilo').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_namesilo = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_namesilo', value:is_namesilo}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用PushBear
        $('#is_push_bear').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_push_bear = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_push_bear', value:is_push_bear}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用TCP阻断探测
        $('#is_tcp_check').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_tcp_check = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_tcp_check', value:is_tcp_check}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用订阅异常自动封禁
        $('#is_subscribe_ban').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_subscribe_ban = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_subscribe_ban', value:is_subscribe_ban}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用退关返利用户可见与否
        $('#referral_status').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var referral_status = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_status', value:referral_status}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用随机端口
        $('#traffic_warning').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var traffic_warning = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_warning', value:traffic_warning}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用随机端口
        $('#is_clear_log').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_clear_log = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_clear_log', value:is_clear_log}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用流量自动重置
        $('#reset_traffic').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var reset_traffic = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'reset_traffic', value:reset_traffic}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用流量异常自动封号
        $('#is_traffic_ban').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_traffic_ban = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_traffic_ban', value:is_traffic_ban}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用端口自动释放
        $('#auto_release_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var auto_release_port = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'auto_release_port', value:auto_release_port}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 过期封禁是否禁止账号
        $('#is_ban_status').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_ban_status = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_ban_status', value:is_ban_status}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 启用、禁用有赞云
        $('#is_youzan').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_youzan = state ? 1 : 0;

                $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'is_youzan', value:is_youzan}, function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'fail') {
                            window.location.reload();
                        }
                    });
                });
            }
        });

        // 流量异常阈值
        function setTrafficBanValue() {
            var traffic_ban_value = $("#traffic_ban_value").val();

            if (traffic_ban_value < 1) {
                layer.msg('不能小于1', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_ban_value', value:traffic_ban_value}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置用户封号时长
        function setTrafficBanTime() {
            var traffic_ban_time = $("#traffic_ban_time").val();

            if (traffic_ban_time < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_ban_time', value:traffic_ban_time}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置节点宕机警告收件地址
        function setCrashWarningEmail() {
            var crash_warning_email = $("#crash_warning_email").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'crash_warning_email', value:crash_warning_email}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置ServerChan的SCKEY
        function setServerChanKey() {
            var server_chan_key = $("#server_chan_key").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'server_chan_key', value:server_chan_key}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置Namesilo API KEY
        function setNamesiloKey() {
            var namesilo_key = $("#namesilo_key").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'namesilo_key', value:namesilo_key}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置PushBear的SendKey
        function setPushBearSendKey() {
            var push_bear_send_key = $("#push_bear_send_key").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'push_bear_send_key', value:push_bear_send_key}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置PushBear的消息通道二维码URL
        function setPushBearQrCode() {
            var push_bear_qrcode = $("#push_bear_qrcode").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'push_bear_qrcode', value:push_bear_qrcode}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置TCP阻断检测提醒次数
        function setTcpCheckWarningTimes() {
            var tcp_check_warning_times = $("#tcp_check_warning_times").val();

            if (tcp_check_warning_times < 0 || tcp_check_warning_times > 12) {
                layer.msg('只能在0-12之间', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'tcp_check_warning_times', value:tcp_check_warning_times}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置订阅封禁阈值
        function setSubscribeBanTimes() {
            var subscribe_ban_times = $("#subscribe_ban_times").val();

            if (subscribe_ban_times < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'subscribe_ban_times', value:subscribe_ban_times}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置有赞云的kdt_id
        function setKdtId() {
            var kdt_id = $("#kdt_id").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'kdt_id', value:kdt_id}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置有赞云的client_id
        function setYouzanClientId() {
            var youzan_client_id = $("#youzan_client_id").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'youzan_client_id', value:youzan_client_id}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置有赞云的client_secret
        function setYouzanClientSecret() {
            var youzan_client_secret = $("#youzan_client_secret").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'youzan_client_secret', value:youzan_client_secret}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置最小积分
        $("#min_rand_score").change(function () {
            var min_rand_score = $(this).val();
            var max_rand_score = $("#max_rand_score").val();

            if (parseInt(min_rand_score) < 0) {
                layer.msg('最小积分小于0', {time:1000});
                return ;
            }

            if (parseInt(min_rand_score) >= parseInt(max_rand_score)) {
                layer.msg('最小积分必须小于最大积分', {time:1000});
                return ;
            }

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
            var min_rand_score = $("#min_rand_score").val();
            var max_rand_score = $(this).val();

            if (parseInt(max_rand_score) > 99999) {
                layer.msg('最大积分不能大于99999', {time:1000});
                return ;
            }

            if (parseInt(min_rand_score) >= parseInt(max_rand_score)) {
                layer.msg('最大积分必须大于最小积分', {time:1000});
                return ;
            }

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

        $("#goods_purchase_limit_strategy").change(function() {
            var strategy = $(this).val();
            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'goods_purchase_limit_strategy', value: strategy}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        });

        // 设置注册时默认有效期
        function setDefaultDays() {
            var default_days = parseInt($("#default_days").val());

            if (default_days < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'default_days', value:default_days}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置注册时默认流量
        function setDefaultTraffic() {
            var default_traffic = parseInt($("#default_traffic").val());

            if (default_traffic < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'default_traffic', value:default_traffic}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置可生成邀请码数量
        function setInviteNum() {
            var invite_num = parseInt($("#invite_num").val());

            if (invite_num < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'invite_num', value:invite_num}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置重置密码次数
        function setResetPasswordTimes() {
            var reset_password_times = $("#reset_password_times").val();

            if (reset_password_times < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'reset_password_times', value:reset_password_times}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置激活用户次数
        function setActiveTimes() {
            var active_times = parseInt($("#active_times").val());

            if (active_times < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'active_times', value:active_times}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置节点订阅地址
        function setSubscribeDomain() {
            var subscribe_domain = $("#subscribe_domain").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'subscribe_domain', value:subscribe_domain}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置节点订阅随机展示节点数
        function setRegisterIpLimit() {
            var register_ip_limit = parseInt($("#register_ip_limit").val());

            if (register_ip_limit < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'register_ip_limit', value:register_ip_limit}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置节点订阅随机展示节点数
        function setSubscribeMax() {
            var subscribe_max = parseInt($("#subscribe_max").val());

            if (subscribe_max < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'subscribe_max', value:subscribe_max}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置流量警告阈值
        function setTrafficWarningPercent() {
            var traffic_warning_percent = $("#traffic_warning_percent").val();

            if (traffic_warning_percent < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'traffic_warning_percent', value:traffic_warning_percent}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置用户过期提醒阈值
        function setExpireDays() {
            var expire_days = parseInt($("#expire_days").val());

            if (expire_days < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'expire_days', value:expire_days}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置网站名称
        function setWebsiteName() {
            var website_name = $("#website_name").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'website_name', value:website_name}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置网站地址
        function setWebsiteUrl() {
            var website_url = $("#website_url").val();

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'website_url', value:website_url}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 登录加积分的时间间隔
        function setLoginAddScoreRange() {
            var login_add_score_range = parseInt($("#login_add_score_range").val());

            if (login_add_score_range < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'login_add_score_range', value:login_add_score_range}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置根据推广链接注册送流量
        function setReferralTraffic() {
            var referral_traffic = parseInt($("#referral_traffic").val());

            if (referral_traffic < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_traffic', value:referral_traffic}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置根据推广链接注册人每产生一笔消费，则推广人可以获得的返利比例
        function setReferralPercent() {
            var referral_percent = $("#referral_percent").val();

            if (referral_percent < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setReferralPercent')}}", {_token:'{{csrf_token()}}', value:referral_percent}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }

        // 设置返利满多少元才可以提现
        function setReferralMoney() {
            var referral_money = $("#referral_money").val();

            if (referral_money < 0) {
                layer.msg('不能小于0', {time:1000});
                return ;
            }

            $.post("{{url('admin/setConfig')}}", {_token:'{{csrf_token()}}', name:'referral_money', value:referral_money}, function (ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'fail') {
                        window.location.reload();
                    }
                });
            });
        }
    </script>
@endsection
