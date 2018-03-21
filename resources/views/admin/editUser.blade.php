@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="tab-pane active">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="{{url('admin/editUser')}}" method="post" class="form-horizontal" onsubmit="return do_submit();">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- BEGIN SAMPLE FORM PORTLET-->
                                    <div class="portlet light bordered">
                                        <div class="portlet-title"  style="width:100%">
                                            <div class="caption" style="width:100%">
                                                <div class="row">
                                                    <span class="caption-subject font-dark bold uppercase col-md-4">账号信息</span>
                                                    <div class="text-right col-md-8" style="">
                                                        <button type="button" class="btn btn-sm btn-danger btn-outline" onclick="switchToUser()">切换身份</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <div class="form-group">
                                                <label for="username" class="col-md-3 control-label">用户名</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="username" value="{{$user->username}}" id="username" placeholder="不填则不变" autofocus required>
                                                    <input type="hidden" name="id" value="{{$user->id}}">
                                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="password" class="col-md-3 control-label">密码</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="password" value="" id="password" placeholder="不填则不变">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="usage" class="col-md-3 control-label">用途</label>
                                                <div class="col-md-8">
                                                    <div class="mt-radio-inline">
                                                        <label class="mt-radio">
                                                            <input type="radio" name="usage" value="1" {{$user->usage == 1 ? 'checked' : ''}}> 手机
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="usage" value="2" {{$user->usage == 2 ? 'checked' : ''}}> 电脑
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="usage" value="3" {{$user->usage == 3 ? 'checked' : ''}}> 路由器
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="usage" value="4" {{$user->usage == 4 ? 'checked' : ''}}> 其他
                                                            <span></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="pay_way" class="col-md-3 control-label">付费方式</label>
                                                <div class="col-md-8">
                                                    <div class="mt-radio-inline">
                                                        <label class="mt-radio">
                                                            <input type="radio" name="pay_way" value="0" {{$user->pay_way == 0 ? 'checked' : ''}}> 免费
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="pay_way" value="1" {{$user->pay_way == 1 ? 'checked' : ''}}> 月付
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="pay_way" value="2" {{$user->pay_way == 2 ? 'checked' : ''}}> 半年付
                                                            <span></span>
                                                        </label>
                                                        <label class="mt-radio">
                                                            <input type="radio" name="pay_way" value="3" {{$user->pay_way == 3 ? 'checked' : ''}}> 年付
                                                            <span></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="balance" class="col-md-3 control-label">级别</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="level" id="level">
                                                        @if(!$level_list->isEmpty())
                                                            @foreach($level_list as $level)
                                                                <option value="{{$level['level']}}" {{$user->level == $level['level'] ? 'selected' : ''}}>{{$level['level_name']}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="balance" class="col-md-3 control-label">余额</label>
                                                <div class="col-md-5">
                                                    <p class="form-control-static"> {{$user->balance}} </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <div style="float:right;">
                                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#handle_user_balance">充值</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--
                                            <div class="form-group">
                                                <label for="score" class="col-md-3 control-label">积分</label>
                                                <div class="col-md-5">
                                                    <p class="form-control-static"> {{$user->score}} </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <div style="float:right;">
                                                        <button type="button" class="btn btn-sm btn-danger">操作</button>
                                                    </div>
                                                </div>
                                            </div>
                                            -->
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">有效期</label>
                                                <div class="col-md-8">
                                                    <div class="input-group input-large input-daterange">
                                                        <input type="text" class="form-control" name="enable_time" value="{{$user->enable_time}}" id="enable_time">
                                                        <span class="input-group-addon"> 至 </span>
                                                        <input type="text" class="form-control" name="expire_time" value="{{$user->expire_time}}" id="expire_time">
                                                    </div>
                                                    <span class="help-block"> 留空默认为一年 </span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="status" class="col-md-3 control-label">状态</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="status" id="status">
                                                        <option value="1" @if($user->status == '1') selected @endif>正常</option>
                                                        <option value="0" @if($user->status == '0') selected @endif>未激活</option>
                                                        <option value="-1" @if($user->status == '-1') selected @endif>禁用</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <label for="status" class="col-md-3 control-label">标签</label>
                                                <div class="col-md-8">
                                                    <select id="labels" class="form-control select2-multiple" name="labels[]" multiple>
                                                        @foreach($label_list as $label)
                                                            <option value="{{$label->id}}" @if(in_array($label->id, $user->labels)) selected @endif>{{$label->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <label for="gender" class="col-md-3 control-label">性别</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="gender" id="gender">
                                                        <option value="1" @if($user->gender == '1') selected @endif>男</option>
                                                        <option value="0" @if($user->gender == '0') selected @endif>女</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="wechat" class="col-md-3 control-label">微信</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="wechat" value="{{$user->wechat}}" id="wechat" placeholder="">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="qq" class="col-md-3 control-label">QQ</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="qq" value="{{$user->qq}}" id="qq" placeholder="">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="is_admin" class="col-md-3 control-label">管理员</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="is_admin" id="is_admin">
                                                        <option value="0" {{$user->is_admin == 0 ? 'selected' : ''}}>否</option>
                                                        <option value="1" {{$user->is_admin == 1 ? 'selected' : ''}}>是</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="remark" class="col-md-3 control-label">备注</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" rows="3" name="remark" id="remark">{{$user->remark}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END SAMPLE FORM PORTLET-->
                                </div>
                                <div class="col-md-6">
                                    <!-- BEGIN SAMPLE FORM PORTLET-->
                                    <div class="portlet light bordered">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <span class="caption-subject font-dark bold">SSR(R)信息</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <div class="form-group">
                                                <label for="port" class="col-md-3 control-label">端口</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="port" value="{{$user->port}}" id="port" />
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-success" type="button" onclick="makePort()"> {{$user->port ? '更换' : '生成'}} </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="passwd" class="col-md-3 control-label">密码</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="passwd" value="{{$user->passwd}}" id="passwd" />
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-success" type="button" onclick="makePasswd()"> 生成 </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="method" class="col-md-3 control-label">加密方式</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="method" id="method">
                                                        @foreach ($method_list as $method)
                                                            <option value="{{$method->name}}" @if($method->name == $user->method) selected @endif>{{$method->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="transfer_enable" class="col-md-3 control-label">可用流量</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="transfer_enable" value="{{$user->transfer_enable}}" id="transfer_enable" placeholder="" required>
                                                        <span class="input-group-addon">GiB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="enable" class="col-md-3 control-label">状态</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="enable" value="{{$user->enable}}" id="enable">
                                                        <option value="1" {{$user->enable ? 'selected' : ''}}>启用</option>
                                                        <option value="0" {{$user->enable ? '' : 'selected'}}>禁用</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <label for="protocol" class="col-md-3 control-label">协议</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="protocol" id="protocol">
                                                        @foreach ($protocol_list as $protocol)
                                                            <option value="{{$protocol->name}}" @if($protocol->name == $user->protocol) selected @endif>{{$protocol->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="obfs" class="col-md-3 control-label">混淆</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="obfs" id="obfs">
                                                        @foreach ($obfs_list as $obfs)
                                                            <option value="{{$obfs->name}}" @if($obfs->name == $user->obfs) selected @endif>{{$obfs->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="protocol_param" class="col-md-3 control-label">协议参数</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" name="protocol_param" value="{{$user->protocol_param}}" id="protocol_param" placeholder="节点单端口时，请务必留空">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="obfs_param" class="col-md-3 control-label">混淆参数</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" rows="5" name="obfs_param" id="obfs_param" placeholder="节点单端口时，请务必留空">{{$user->obfs_param}}</textarea>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <label for="speed_limit_per_con" class="col-md-3 control-label">单连接限速</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="speed_limit_per_con" value="{{$user->speed_limit_per_con}}" id="speed_limit_per_con" placeholder="" required>
                                                        <span class="input-group-addon">KB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="speed_limit_per_user" class="col-md-3 control-label">单用户限速</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="speed_limit_per_user" value="{{$user->speed_limit_per_user}}" id="speed_limit_per_user" placeholder="" required>
                                                        <span class="input-group-addon">KB</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END SAMPLE FORM PORTLET-->
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn green">提 交</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>
            </div>

            <!-- 余额充值 -->
            <div id="handle_user_balance" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog" style="width:300px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">充值</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger" style="display: none;" id="msg"></div>
                            <!-- BEGIN FORM-->
                            <form action="#" method="post" class="form-horizontal">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label for="amount" class="col-md-4 control-label"> 充值金额 </label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="amount" id="amount" placeholder="填入负值则会扣余额" onkeydown="if(event.keyCode==13){return false;}">
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- END FORM-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                            <button type="button" class="btn red btn-outline" onclick="return handleUserBalance();">充值</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 用户标签选择器
        $('#labels').select2({
            placeholder: '设置后则可见相同标签的节点',
            allowClear: true
        });

        // 切换用户身份
        function switchToUser() {
            $.ajax({
                'url': "{{url("/admin/switchToUser")}}",
                'data': {
                    'user_id': '{{$user->id}}',
                    '_token': '{{csrf_token()}}'
                },
                'dataType': "json",
                'type': "POST",
                success: function (ret) {
                    layer.msg(ret.message, {time: 1000}, function () {
                        if (ret.status == 'success') {
                            window.location.href = "/user";
                        }
                    });
                }
            });
        }

        // 有效期
        $('.input-daterange input').each(function() {
            $(this).datepicker({
                language: 'zh-CN',
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
        });

        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var id = '{{Request::get('id')}}';
            var username = $('#username').val();
            var password = $('#password').val();
            var usage = $("input:radio[name='usage']:checked").val();
            var pay_way = $("input:radio[name='pay_way']:checked").val();
            var balance = $('#balance').val();
            var score = $('#score').val();
            var status = $('#status').val();
            var labels = $('#labels').val();
            var enable_time = $('#enable_time').val();
            var expire_time = $('#expire_time').val();
            var gender = $('#gender').val();
            var wechat = $('#wechat').val();
            var qq = $('#qq').val();
            var is_admin = $('#is_admin').val();
            var remark = $('#remark').val();
            var level = $("#level option:selected").val();
            var port = $('#port').val();
            var passwd = $('#passwd').val();
            var method = $('#method').val();
            var transfer_enable = $('#transfer_enable').val();
            var enable = $('#enable').val();
            var protocol = $('#protocol').val();
            var protocol_param = $('#protocol_param').val();
            var obfs = $('#obfs').val();
            var obfs_param = $('#obfs_param').val();
            var speed_limit_per_con = $('#speed_limit_per_con').val();
            var speed_limit_per_user = $('#speed_limit_per_user').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/editUser')}}",
                async: false,
                data: {_token:_token, id:id, username: username, password:password, usage:usage, pay_way:pay_way, balance:balance, score:score, status:status, labels:labels, enable_time:enable_time, expire_time:expire_time, gender:gender, wechat:wechat, qq:qq, is_admin:is_admin, remark:remark, level:level, port:port, passwd:passwd, method:method, transfer_enable:transfer_enable, enable:enable, protocol:protocol, protocol_param:protocol_param, obfs:obfs, obfs_param:obfs_param, speed_limit_per_con:speed_limit_per_con, speed_limit_per_user:speed_limit_per_user},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/userList?page=') . Request::get('page')}}';
                        }
                    });
                }
            });

            return false;
        }

        // 生成随机端口
        function makePort() {
            $.get("{{url('admin/makePort')}}",  function(ret) {
                $("#port").val(ret);
            });
        }

        // 生成随机密码
        function makePasswd() {
            $.get("{{url('admin/makePasswd')}}",  function(ret) {
                $("#passwd").val(ret);
            });
        }

        // 余额充值
        function handleUserBalance() {
            var amount = $("#amount").val();
            var reg = /^(\-?)\d+(\.\d+)?$/; //只可以是正负数字

            if (amount == '' || amount == 0 || !reg.test(amount)) {
                $("#msg").show().html("请输入充值金额");
                $("#name").focus();
                return false;
            }

            $.ajax({
                url:'{{url('admin/handleUserBalance')}}',
                type:"POST",
                data:{_token:'{{csrf_token()}}', user_id:'{{Request::get('id')}}', amount:amount},
                beforeSend:function(){
                    $("#msg").show().html("充值中...");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    } else {
                        layer.msg(ret.message, {time:1000}, function() {
                            if (ret.status == 'success') {
                                $("#handle_user_balance").modal("hide");
                                window.location.reload();
                            }
                        });
                    }
                },
                error:function(){
                    $("#msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }
    </script>
@endsection
