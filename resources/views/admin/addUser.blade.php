@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">用户添加</h2>
            </div>
            <div class="panel-body">
                <form action="/admin/addUser" method="post" class="form-horizontal" onsubmit="return do_submit();">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="example-wrap">
                                <h4 class="example-title">账号信息</h4>
                                <div class="form-group row">
                                    <label for="username" class="col-md-3 col-form-label">用户名</label>
                                    <input type="text" class="form-control col-md-4" name="username" id="username" placeholder="" autocomplete="off" autofocus required/>
                                </div>
                                <div class="form-group row">
                                    <label for="password" class="col-md-3 col-form-label">密码</label>
                                    <input type="text" class="form-control col-md-4" name="password" value="" id="password" placeholder="留空则自动生成随机密码" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">用途</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="1" checked/>
                                                <label>手机</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="2"/>
                                                <label>电脑</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="3"/>
                                                <label>路由器</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="checkbox-custom checkbox-primary">
                                                <input type="checkbox" name="usage" value="4"/>
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
                                                <input type="radio" name="pay_way" value="0" checked/>
                                                <label>免费</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="1"/>
                                                <label>月付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="2"/>
                                                <label>季付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="3" checked/>
                                                <label>半年付</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="pay_way" value="4"/>
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
                                                <option value="{{$level->level}}">{{$level->level_name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">有效期</label>
                                    <div class="input-group col-md-6 input-daterange" data-plugin="datepicker">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="start" id="enable_time"/>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">至</span>
                                        </div>
                                        <input type="text" class="form-control" name="end" id="expire_time"/>
                                    </div>
                                    <span class="text-help offset-md-3"> 留空默认为一年 </span>
                                </div>
                                <div class="form-group row">
                                    <label for="status" class="col-md-3 col-form-label">账户状态</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="1" checked/>
                                                <label>正常</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="0"/>
                                                <label>未激活</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="status" value="-1"/>
                                                <label>禁用</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="labels" class="col-md-3 col-form-label">标签</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="labels" name="labels" placeholder="设置后则可见相同标签的节点" multiple>
                                        @foreach($label_list as $label)
                                            <option value="{{$label->id}}" {{in_array($label->id, $initial_labels) ? 'selected' : ''}}>{{$label->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="gender" class="col-md-3 col-form-label">性别</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="gender" value="1" checked/>
                                                <label>男</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="gender" value="0"/>
                                                <label>女</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-group row">
                                    <label for="wechat" class="col-md-3 col-form-label">微信</label>
                                    <input type="text" class="col-md-4 form-control" name="wechat" id="wechat" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label for="qq" class="col-md-3 col-form-label">QQ</label>
                                    <input type="text" class="col-md-4 form-control" name="qq" id="qq" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label for="remark" class="col-md-3 col-form-label">备注</label>
                                    <textarea class="col-md-7 form-control" rows="3" name="remark" id="remark"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                                <h4 class="example-title">代理信息</h4>
                                <div class="form-group row">
                                    <label for="port" class="col-md-3 col-form-label">端口</label>
                                    <div class="col-md-4">
                                        @if(\App\Components\Helpers::systemConfig()['is_rand_port'])
                                            <div class="input-group">
                                                <input class="form-control" type="text" name="port" value="{{$last_port}}" id="port" autocomplete="off"/>
                                                <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePort()"> <i class="icon wb-refresh"></i> </button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="port" value="{{$last_port}}" id="port" autocomplete="off" aria-required="true" aria-invalid="true" aria-describedby="number-error" required/>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="passwd" class="col-md-3 col-form-label">密码</label>
                                    <div class="input-group col-md-4">
                                        <input class="form-control" type="text" name="passwd" id="passwd" placeholder="留空则自动生成随机密码" autocomplete="off"/>
                                        <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePasswd()"> <i class="icon wb-refresh"></i> </button></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="method" class="col-md-3 col-form-label">加密方式</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="method" id="method">
                                        @foreach ($method_list as $method)
                                            <option value="{{$method->name}}" @if($method->is_default) selected @endif>{{$method->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="transfer_enable" class="col-md-3 col-form-label">可用流量</label>
                                    <div class="input-group col-md-3">
                                        <input type="text" class="form-control" name="transfer_enable" value="1024" id="transfer_enable" autocomplete="off" required>
                                        <span class="input-group-text">GB</span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="enable" class="col-md-3 col-form-label">代理状态</label>
                                    <ul class="col-md-9 list-unstyled list-inline">
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" value="1" checked/>
                                                <label>启用</label>
                                            </div>
                                        </li>
                                        <li class="list-inline-item">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" name="enable" value="0"/>
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
                                            <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>{{$protocol->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="obfs" class="col-md-3 col-form-label">混淆</label>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="obfs" id="obfs">
                                        @foreach ($obfs_list as $obfs)
                                            <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>{{$obfs->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group row">
                                    <label for="protocol_param" class="col-md-3 col-form-label">协议参数</label>
                                    <input type="text" class="col-md-4 form-control" name="protocol_param" id="protocol_param" placeholder="节点单端口时无效" autocomplete="off"/>
                                </div>
                                <div class="form-group row">
                                    <label for="obfs_param" class="col-md-3 col-form-label">混淆参数</label>
                                    <textarea class="col-md-7 form-control" rows="3" name="obfs_param" id="obfs_param" placeholder="不填则取节点自定义混淆参数"></textarea>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="speed_limit_per_con" class="col-md-3 col-form-label">单连接限速</label>
                                    <div class="input-group col-md-4">
                                        <input type="text" class="form-control" name="speed_limit_per_con" value="10737418240" id="speed_limit_per_con" autocomplete="off"/>
                                        <span class="input-group-text">Byte</span>
                                    </div>
                                    <span class="text-help offset-md-1"> 为 0 时不限速 </span>
                                </div>
                                <div class="form-group row">
                                    <label for="speed_limit_per_user" class="col-md-3 col-form-label">单用户限速</label>
                                    <div class="input-group col-md-4">
                                        <input type="text" class="form-control" name="speed_limit_per_user" value="10737418240" id="speed_limit_per_user" autocomplete="off"/>
                                        <span class="input-group-text">Byte</span>
                                    </div>
                                    <span class="text-help offset-md-1">为 0 时不限速 </span>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label for="vmess_id" class="col-md-3 col-form-label">VMess UUID</label>
                                    <div class="input-group col-md-6">
                                        <input class="form-control" type="text" name="vmess_id" value="{{createGuid()}}" id="vmess_id" autocomplete="off"/>
                                        <span class="input-group-append"><button class="btn btn-success" type="button" onclick="makeVmessId()"> <i class="icon wb-refresh"></i> </button></span>
                                    </div>
                                    <span class="text-help offset-md-3"> V2Ray的账户ID </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 form-actions text-right">
                            <button type="submit" class="btn btn-success">提 交</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>

    <script type="text/javascript">
        $('.input-daterange>input').datepicker({
            format: "yyyy-mm-dd",
            startDate: "2017-01-01"
        });

        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var username = $('#username').val();
            var password = $('#password').val();
            var pay_way = $("input:radio[name='pay_way']:checked").val();
            var status = $("input:radio[name='status']:checked").val();
            var labels = $('#labels').val();
            var enable_time = $('#enable_time').val();
            var expire_time = $('#expire_time').val();
            var gender = $("input:radio[name='gender']:checked").val();
            var wechat = $('#wechat').val();
            var qq = $('#qq').val();
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
                url: "/admin/addUser",
                async: false,
                data: {
                    _token: _token,
                    username: username,
                    password: password,
                    usage: usage,
                    pay_way: pay_way,
                    status: status,
                    labels: labels,
                    enable_time: enable_time,
                    expire_time: expire_time,
                    gender: gender,
                    wechat: wechat,
                    qq: qq,
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
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/userList')
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
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
    </script>
@endsection