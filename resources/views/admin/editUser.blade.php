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
					<div class="form-row">
						<div class="col-lg-6">
							<h4 class="example-title">账号信息</h4>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="username">用户名</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="username" value="{{$user->username}}" id="username" autocomplete="off" autofocus required/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="password">密码</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="password" value="" id="password" placeholder="不填则不变" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="usage">用途</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="pay_way">付费方式</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="level">级别</label>
								<div class="col-xl-4 col-sm-8">
									<select class="form-control" name="level" id="level" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@if(!$level_list->isEmpty())
											@foreach($level_list as $level)
												<option value="{{$level->level}}" {{$user->level == $level->level ? 'selected' : ''}}>{{$level->level_name}}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="balance">余额</label>
								<div class="col-xl-4 col-sm-8">
									<div class="input-group">
										<p class="form-control"> {{$user->balance}} </p>
										<span class="input-group-append"><button class="btn btn-danger" type="button" data-toggle="modal" data-target="#handle_user_balance">充值</button></span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label">有效期</label>
								<div class="col-xl-8 col-sm-8">
									<div class="input-group input-daterange" data-plugin="datepicker">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
										</div>
										<input type="text" class="form-control" name="start" id="enable_time" value="{{$user->enable_time}}"/>
										<div class="input-group-prepend">
											<span class="input-group-text">至</span>
										</div>
										<input type="text" class="form-control" name="end" id="expire_time" value="{{$user->expire_time}}"/>
									</div>
									<span class="text-help"> 留空默认为一年 </span>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="status">账户状态</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="is_admin">管理员</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="labels">标签</label>
								<div class="col-xl-6 col-sm-8">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control show-tick" id="labels" name="labels" placeholder="设置后则可见相同标签的节点" multiple>
										@foreach($label_list as $label)
											<option value="{{$label->id}}" @if(in_array($label->id, $user->labels)) selected @endif>{{$label->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="wechat">微信</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="wechat" value="{{$user->wechat}}" id="wechat" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="qq">QQ</label>
								<div class="col-xl-6 col-sm-8">
									<input type="number" class="form-control" name="qq" value="{{$user->qq}}" id="qq" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="remark">备注</label>
								<div class="col-xl-6 col-sm-8">
									<textarea class="form-control" rows="3" name="remark" id="remark">{{$user->remark}}</textarea>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="referral_uid">邀请人</label>
								<div class="col-xl-6 col-sm-8">
									<p class="form-control"> {{empty($user->referral) ? '无邀请人' : $user->referral->username}} </p>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-lg-6">
							<h4 class="example-title">代理信息</h4>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="port">端口</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input class="form-control" type="number" name="port" value="{{$user->port}}" id="port"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePort()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="passwd">密码</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input class="form-control" type="text" name="passwd" id="passwd" value="{{$user->passwd}}"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePasswd()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="method">加密方式</label>
								<div class="col-xl-5 col-sm-8">
									<select class="form-control" name="method" id="method" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@foreach ($method_list as $method)
											<option value="{{$method->name}}" @if($method->name == $user->method) selected @endif>{{$method->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="transfer_enable">可用流量</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input type="text" class="form-control" name="transfer_enable" value="{{$user->transfer_enable}}" id="transfer_enable" required>
										<span class="input-group-text">GB</span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="enable">代理状态</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="protocol">协议</label>
								<div class="col-xl-5 col-sm-8">
									<select class="form-control" name="protocol" id="protocol" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@foreach ($protocol_list as $protocol)
											<option value="{{$protocol->name}}" @if($protocol->name == $user->protocol) selected @endif>{{$protocol->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="obfs">混淆</label>
								<div class="col-xl-5 col-sm-8">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="obfs" id="obfs">
										@foreach ($obfs_list as $obfs)
											<option value="{{$obfs->name}}" @if($obfs->name == $user->obfs) selected @endif>{{$obfs->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="protocol_param">协议参数</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="protocol_param" id="protocol_param" value="{{$user->protocol_param}}" placeholder="节点单端口时无效"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="obfs_param">混淆参数</label>
								<div class="col-xl-6 col-sm-8">
									<textarea class="form-control" rows="3" name="obfs_param" id="obfs_param" placeholder="不填则取节点自定义混淆参数">{{$user->obfs_param}}</textarea>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="speed_limit_per_con">单连接限速</label>
								<div class="col-xl-6 col-sm-8">
									<div class="input-group">
										<input type="text" class="form-control" name="speed_limit_per_con" id="speed_limit_per_con" value="{{$user->speed_limit_per_con}}"/>
										<span class="input-group-text">Byte</span>
									</div>
									<span class="text-help"> 为 0 时不限速 </span>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="speed_limit_per_user">单用户限速</label>
								<div class="col-xl-6 col-sm-8">
									<div class="input-group">
										<input type="text" class="form-control" name="speed_limit_per_user" id="speed_limit_per_user" value="{{$user->speed_limit_per_user}}"/>
										<span class="input-group-text">Byte</span>
									</div>
									<span class="text-help">为 0 时不限速 </span>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="vmess_id">VMess UUID</label>
								<div class="col-xl-6 col-sm-8">
									<div class="input-group">
										<input class="form-control" type="text" name="vmess_id" id="vmess_id" value="{{$user->vmess_id}}"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makeVmessId()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
									<span class="text-help"> V2Ray的账户ID </span>
								</div>
							</div>
						</div>

						<div class="col-sm-12 form-actions">
							<button type="submit" class="btn btn-success float-right">提 交</button>
						</div>
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
						<label class="col-md-2 col-sm-3 col-form-label" for="amount"> 充值金额 </label>
						<input type="number" class="col-sm-4 form-control" name="amount" id="amount" placeholder="填入负值则会扣余额" onkeydown="if(event.keyCode==13){return false;}"/>
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
            // 用途
            let usage = '';
            $.each($("input:checkbox[name='usage']"), function () {
                if (this.checked) {
                    usage += $(this).val() + ',';
                }
            });
            $.ajax({
                type: "POST",
                url: "/admin/editUser/{{$user->id}}",
                async: false,
                data: {
                    _token: '{{csrf_token()}}',
                    username: $('#username').val(),
                    password: $('#password').val(),
                    usage: usage.substring(0, usage.length - 1),
                    pay_way: $("input:radio[name='pay_way']:checked").val(),
                    balance: $('#balance').val(),
                    status: $("input:radio[name='status']:checked").val(),
                    labels: $('#labels').val(),
                    enable_time: $('#enable_time').val(),
                    expire_time: $('#expire_time').val(),
                    wechat: $('#wechat').val(),
                    qq: $('#qq').val(),
                    is_admin: $("input:radio[name='is_admin']:checked").val(),
                    remark: $('#remark').val(),
                    level: $("#level option:selected").val(),
                    port: $('#port').val(),
                    passwd: $('#passwd').val(),
                    method: $('#method option:selected').val(),
                    transfer_enable: $('#transfer_enable').val(),
                    enable: $("input:radio[name='enable']:checked").val(),
                    protocol: $('#protocol option:selected').val(),
                    protocol_param: $('#protocol_param').val(),
                    obfs: $('#obfs option:selected').val(),
                    obfs_param: $('#obfs_param').val(),
                    speed_limit_per_con: $('#speed_limit_per_con').val(),
                    speed_limit_per_user: $('#speed_limit_per_user').val(),
                    vmess_id: $('#vmess_id').val()
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

            if (amount.trim() === '' || amount === 0 || !reg.test(amount)) {
                $("#msg").show().html("请输入充值金额");
                $("#name").focus();
                return false;
            }

            $.ajax({
                url: '/admin/handleUserBalance',
                type: "POST",
                data: {_token: '{{csrf_token()}}', user_id: '{{$user->id}}', amount: amount},
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
