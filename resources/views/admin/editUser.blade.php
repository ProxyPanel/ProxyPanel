@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">用户信息编辑</h2>
                <div class="panel-actions">
                    <button type="button" class="btn btn-sm btn-danger" onclick="switchToUser()">切换身份</button>
                </div>
            </div>
            <div class="panel-body">
                <form action="/admin/editUser/{{$user->id}}" method="post" class="form-horizontal" onsubmit="return Submit()">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="example-wrap">
                                <h4 class="example-title">账号信息</h4>
                                <div class="form-group row">
                                    <label for="username" class="col-md-3 col-form-label">用户名</label>
                                    <input type="email" class="form-control col-md-4" name="username" value="{{$user->username}}" id="username" autocomplete="off" autofocus required/>
                                </div>
                                <div class="form-group row">
                                    <label for="password" class="col-md-3 col-form-label">密码</label>
                                    <input type="password" class="form-control col-md-4" name="password" value="" id="password" placeholder="不填则不变" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">用途</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="1" {{in_array(1, $user->usage) ? 'checked' : ''}} />
                                                <label>手机</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="2" {{in_array(2, $user->usage) ? 'checked' : ''}} />
                                                <label>电脑</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="3" {{in_array(3, $user->usage) ? 'checked' : ''}} />
                                                <label>路由器</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="4" {{in_array(4, $user->usage) ? 'checked' : ''}} />
                                                <label>平板</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-group row">
                                    <label for="pay_way" class="col-md-3 col-form-label">付费方式</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="0" {{$user->pay_way == 0 ? 'checked' : ''}} />
                                                <label>免费</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="1" {{$user->pay_way == 1 ? 'checked' : ''}} />
                                                <label>月付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="2" {{$user->pay_way == 2 ? 'checked' : ''}} />
                                                <label>季付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="3" {{$user->pay_way == 3 ? 'checked' : ''}} />
                                                <label>半年付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="4" {{$user->pay_way == 4 ? 'checked' : ''}} />
                                                <label>年付</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-group row">
                                    <label for="level" class="col-md-3 col-form-label">级别</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="level" id="level">
                                        @if(!$level_list->isEmpty())
                                            @foreach($level_list as $level)
                                                <option value="{{$level->level}}" {{$user->level == $level->level ? 'selected' : ''}}>{{$level->level_name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="balance" class="col-md-3 col-form-label">余额</label>
                                    <div class="input-group col-md-3">
                                        <p class="form-control"> {{$user->balance}} </p>
                                        <span class="input-group-append"><button class="btn btn-danger" type="button" data-toggle="modal" data-target="#handle_user_balance">充值</button></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">有效期</label>
                                    <div class="col-md-6 input-group input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                                        </div>
                                        <input type="text" class="form-control" value="{{$user->enable_time}}" name="start" id="enable_time"/>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">至</span>
                                        </div>
                                        <input type="text" class="form-control" value="{{$user->expire_time}}" name="end" id="expire_time"/>
                                    </div>
                                    <span class="text-help offset-md-3"> 留空默认为一年 </span>
                                </div>
                                <div class="form-group row">
                                    <label for="status" class="col-md-3 col-form-label">账户状态</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="1" {{$user->status == '1' ? 'checked' : ''}} />
                                                <label>正常</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="0" {{$user->status == '0' ? 'checked' : ''}} />
                                                <label>未激活</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="-1" {{$user->status == '-1' ? 'checked' : ''}} />
                                                <label>禁用</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-group row">
                                    <label for="is_admin" class="col-md-3 col-form-label">管理员</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="is_admin" value="1" {{$user->is_admin == '1' ? 'checked' : ''}} />
                                                <label>是</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="is_admin" value="0" {{$user->is_admin == '0' ? 'checked' : ''}} />
                                                <label>否</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="labels" class="col-md-3 col-form-label">标签</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="labels" name="labels" placeholder="设置后则可见相同标签的节点" multiple>
                                        @foreach($label_list as $label)
                                            <option value="{{$label->id}}" @if(in_array($label->id, $user->labels)) selected @endif>{{$label->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="gender" class="col-md-3 col-form-label">性别</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="gender" value="1" {{$user->gender == '1' ? 'checked' : ''}} />
                                                <label>男</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="gender" value="0" {{$user->gender == '0' ? 'checked' : ''}} />
                                                <label>女</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-group row">
                                    <label for="wechat" class="col-md-3 col-form-label">微信</label>
                                    <input type="text" class="col-md-4 form-control" name="wechat" value="{{$user->wechat}}" id="wechat" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label for="qq" class="col-md-3 col-form-label">QQ</label>
                                    <input type="number" class="col-md-4 form-control" name="qq" value="{{$user->qq}}" id="qq" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label for="remark" class="col-md-3 col-form-label">备注</label>
                                    <textarea class="col-md-7 form-control" rows="3" name="remark" id="remark">{{$user->remark}}</textarea>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="referral_uid" class="col-md-3 col-form-label">邀请人</label>
                                    <p class="col-md-4 form-control"> {{empty($user->referral) ? '无邀请人' : $user->referral->username}} </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="example-wrap">
                                <h4 class="example-title">代理信息</h4>
                                <div class="form-group row">
                                    <label for="port" class="col-md-3 col-form-label">端口</label>
                                    <div class="input-group col-md-4">
                                        <input class="form-control" type="number" name="port" value="{{$user->port}}" id="port"/>
                                        <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePort()"> <i class="icon wb-refresh"></i> </button></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="passwd" class="col-md-3 col-form-label">密码</label>
                                    <div class="input-group col-md-4">
                                        <input class="form-control" type="text" name="passwd" value="{{$user->passwd}}" id="passwd"/>
                                        <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePasswd()"> <i class="icon wb-refresh"></i> </button></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="method" class="col-md-3 col-form-label">加密方式</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="method" id="method">
                                        @foreach ($method_list as $method)
                                            <option value="{{$method->name}}" @if($method->name == $user->method) selected @endif>{{$method->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="transfer_enable" class="col-md-3 col-form-label">可用流量</label>
                                    <div class="input-group col-md-3">
                                        <input type="number" class="form-control" name="transfer_enable" value="{{$user->transfer_enable}}" id="transfer_enable" required>
                                        <span class="input-group-text">GB</span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="enable" class="col-md-3 col-form-label">代理状态</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" value="1" {{$user->enable == '1' ? 'checked' : ''}} />
                                                <label>启用</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" value="0" {{$user->enable == '0' ? 'checked' : ''}} />
                                                <label>禁用</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="protocol" class="col-md-3 col-form-label">协议</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="protocol" id="protocol">
                                        @foreach ($protocol_list as $protocol)
                                            <option value="{{$protocol->name}}" @if($protocol->name == $user->protocol) selected @endif>{{$protocol->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="obfs" class="col-md-3 col-form-label">混淆</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="obfs" id="obfs">
                                        @foreach ($obfs_list as $obfs)
                                            <option value="{{$obfs->name}}" @if($obfs->name == $user->obfs) selected @endif>{{$obfs->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="protocol_param" class="col-md-3 col-form-label">协议参数</label>
                                    <input type="text" class="col-md-4 form-control" name="protocol_param" value="{{$user->protocol_param}}" id="protocol_param" placeholder="节点单端口时无效"/>
                                </div>
                                <div class="form-group row">
                                    <label for="obfs_param" class="col-md-3 col-form-label">混淆参数</label>
                                    <textarea class="col-md-7 form-control" rows="3" name="obfs_param" id="obfs_param" placeholder="不填则取节点自定义混淆参数">{{$user->obfs_param}}</textarea>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="speed_limit_per_con" class="col-md-3 col-form-label">单连接限速</label>
                                    <div class="input-group col-md-4">
                                        <input type="text" class="form-control" name="speed_limit_per_con" value="{{$user->speed_limit_per_con}}" id="speed_limit_per_con"/>
                                        <span class="input-group-text">Byte</span>
                                    </div>
                                    <span class="text-help offset-md-1"> 为 0 时不限速 </span>
                                </div>
                                <div class="form-group row">
                                    <label for="speed_limit_per_user" class="col-md-3 col-form-label">单用户限速</label>
                                    <div class="input-group col-md-4">
                                        <input type="text" class="form-control" name="speed_limit_per_user" value="{{$user->speed_limit_per_user}}" id="speed_limit_per_user"/>
                                        <span class="input-group-text">Byte</span>
                                    </div>
                                    <span class="text-help offset-md-1">为 0 时不限速 </span>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="vmess_id" class="col-md-3 col-form-label">VMess UUID</label>
                                    <div class="input-group col-md-6">
                                        <input class="form-control" type="text" name="vmess_id" value="{{$user->vmess_id}}" id="vmess_id"/>
                                        <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makeVmessId()"> <i class="icon wb-refresh"></i> </button></span>
                                    </div>
                                    <span class="text-help offset-md-3"> V2Ray的账户ID </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 form-actions">
                        <button type="submit" class="btn btn-success float-right">提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 余额充值 -->
    <div class="modal fade" id="handle_user_balance" aria-hidden="true" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">充值</h4>
                </div>
                <form action="#" method="post" class="modal-body">
                    <div class="alert alert-danger" style="display: none;" id="msg"></div>
                    <div class="form-group row">
                        <label for="amount" class="col-md-3 col-form-label"> 充值金额 </label>
                        <input type="number" class="col-md-4 form-control" name="amount" id="amount" placeholder="填入负值则会扣余额" onkeydown="if(event.keyCode==13){return false;}"/>
                    </div>
                </form>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-danger">关闭</button>
                    <button type="button" class="btn btn-primary" onclick="return handleUserBalance();">充值</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js" type="text/javascript"></script>

    <script type="text/javascript">
        $('.input-daterange>input').datepicker({
            format: "yyyy-mm-dd",
            startDate: "2017-01-01"
        });
        // 切换用户身份
        function switchToUser() {
            $.ajax({
                'url': "/admin/switchToUser",
                'data': {
                    'user_id': '{{$user->id}}',
                    '_token': '{{csrf_token()}}'
                },
                'dataType': "json",
                'type': "POST",
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = "/")
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
        }

        // ajax同步提交
        function Submit() {
            var _token = '{{csrf_token()}}';
            var username = $('#username').val();
            var password = $('#password').val();
            var pay_way = $("input:radio[name='pay_way']:checked").val();
            var balance = $('#balance').val();
            var status = $("input:radio[name='status']:checked").val();
            var labels = $('#labels').val();
            var enable_time = $('#enable_time').val();
            var expire_time = $('#expire_time').val();
            var gender = $("input:radio[name='gender']:checked").val();
            var wechat = $('#wechat').val();
            var qq = $('#qq').val();
            var is_admin = $("input:radio[name='is_admin']:checked").val();
            var remark = $('#remark').val();
            var level = $("#level option:selected").val();
            var port = $('#port').val();
            var passwd = $('#passwd').val();
            var method = $('#method').val();
            var transfer_enable = $('#transfer_enable').val();
            var enable = $("input:radio[name='enable']:checked").val();
            var protocol = $('#protocol').val();
            var protocol_param = $('#protocol_param').val();
            var obfs = $('#obfs').val();
            var obfs_param = $('#obfs_param').val();
            var speed_limit_per_con = $('#speed_limit_per_con').val();
            var speed_limit_per_user = $('#speed_limit_per_user').val();
            var vmess_id = $('#vmess_id').val();

            // 用途
            var usage = '';
            $.each($("input:checkbox[name='usage']"), function () {
                if (this.checked) {
                    usage += $(this).val() + ',';
                }
            });
            usage = usage.substring(0, usage.length - 1);
            $.ajax({
                type: "POST",
                url: "/admin/editUser/{{$user->id}}",
                async: false,
                data: {
                    _token: _token,
                    username: username,
                    password: password,
                    usage: usage,
                    pay_way: pay_way,
                    balance: balance,
                    status: status,
                    labels: labels,
                    enable_time: enable_time,
                    expire_time: expire_time,
                    gender: gender,
                    wechat: wechat,
                    qq: qq,
                    is_admin: is_admin,
                    remark: remark,
                    level: level,
                    port: port,
                    passwd: passwd,
                    method: method,
                    transfer_enable: transfer_enable,
                    enable: enable,
                    protocol: protocol,
                    protocol_param: protocol_param,
                    obfs: obfs,
                    obfs_param: obfs_param,
                    speed_limit_per_con: speed_limit_per_con,
                    speed_limit_per_user: speed_limit_per_user,
                    vmess_id: vmess_id
                },
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            title: '提示',
                            text: '更新成功，是否返回？',
                            type: 'question',
                            showCancelButton: true,
                            cancelButtonText: '{{trans('home.ticket_close')}}',
                            confirmButtonText: '{{trans('home.ticket_confirm')}}',
                        }).then((result) => {
                                if (result.value) {
                                    @if (Request::getQueryString())
                                        window.location.href = '/admin/userList' + '?{!! Request::getQueryString() !!}';
                                    @else
                                        window.location.href = '/admin/userList';
                                    @endif
                                }
                            }
                        )
                    } else {
                        swal.fire({title: ret.message, timer: 1000, showConfirmButton: false})
                    }
                }
            });
            return false;
        }

        // 生成随机端口
        function makePort() {
            $.get("/admin/makePort", function (ret) {
                $("#port").val(ret);
            });
        }

        // 生成随机VmessId
        function makeVmessId() {
            $.get("/makeVmessId", function (ret) {
                $("#vmess_id").val(ret);
            });
        }

        // 生成随机密码
        function makePasswd() {
            $.get("/makePasswd", function (ret) {
                $("#passwd").val(ret);
            });
        }

        // 余额充值
        function handleUserBalance() {
            const amount = $("#amount").val();
            const reg = /^(\-?)\d+(\.\d+)?$/; //只可以是正负数字

            if (amount.trim() === '' || amount == 0 || !reg.test(amount)) {
                $("#msg").show().html("请输入充值金额");
                $("#name").focus();
                return false;
            }

            $.ajax({
                url: '/admin/handleUserBalance',
                type: "POST",
                data: {_token: '{{csrf_token()}}', user_id: '{{Request::get('id')}}', amount: amount},
                beforeSend: function () {
                    $("#msg").show().html("充值中...");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    } else {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => {
                                    $("#handle_user_balance").modal("hide");
                                    window.location.reload();
                                })
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    }
                },
                error: function () {
                    $("#msg").show().html("请求错误，请重试");
                },
                complete: function () {
                }
            });
        }
    </script>
@endsection
