@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">用户添加</h2>
			</div>
			<div class="panel-body">
				<form action="/admin/addUser" method="post" class="form-horizontal" onsubmit="return Submit()">
					<div class="form-row">
						<div class="col-lg-6">
							<h4 class="example-title">账号信息</h4>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="email">邮箱</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="email" id="email" autocomplete="off" autofocus required/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="password">密码</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="password" value="" id="password" placeholder="留空则自动生成随机密码" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="usage">用途</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="pay_way">付费方式</label>
								<div class="col-md-10 col-sm-8">
									<ul class="list-unstyled list-inline">
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
												<input type="radio" name="pay_way" value="3"/>
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
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="level">级别</label>
								<div class="col-xl-4 col-sm-8">
									<select class="form-control" name="level" id="level" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@if(!$level_list->isEmpty())
											@foreach($level_list as $level)
												<option value="{{$level->level}}">{{$level->level_name}}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="reset_time">重置日</label>
								<div class="col-xl-4 col-sm-4">
									<div class="input-group input-daterange" data-plugin="datepicker">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
										</div>
										<input type="text" class="form-control" name="reset_time" id="reset_time"/>
									</div>
									<span class="text-help"> 账号流量重置日期 </span>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label">有效期</label>
								<div class="col-xl-8 col-sm-8">
									<div class="input-group input-daterange" data-plugin="datepicker">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
										</div>
										<input type="text" class="form-control" name="start" id="enable_time"/>
										<div class="input-group-prepend">
											<span class="input-group-text">至</span>
										</div>
										<input type="text" class="form-control" name="end" id="expire_time"/>
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
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="labels">标签</label>
								<div class="col-xl-6 col-sm-8">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control show-tick" id="labels" name="labels" placeholder="设置后则可见相同标签的节点" multiple>
										@foreach($label_list as $label)
											<option value="{{$label->id}}">{{$label->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="wechat">微信</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="wechat" id="wechat" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="qq">QQ</label>
								<div class="col-xl-6 col-sm-8">
									<input type="number" class="form-control" name="qq" id="qq" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="remark">备注</label>
								<div class="col-xl-6 col-sm-8">
									<textarea class="form-control" rows="3" name="remark" id="remark"></textarea>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<h4 class="example-title">代理信息</h4>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="port">端口</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input type="number" class="form-control" name="port" value="{{$last_port}}" id="port"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePort()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="passwd">密码</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input type="text" class="form-control" name="passwd" id="passwd" placeholder="留空则自动生成随机密码" autocomplete="off"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makePasswd()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="method">加密方式</label>
								<div class="col-xl-5 col-sm-8">
									<select class="form-control" name="method" id="method" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@foreach ($method_list as $method)
											<option value="{{$method->name}}">{{$method->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="transfer_enable">可用流量</label>
								<div class="col-xl-5 col-sm-8">
									<div class="input-group">
										<input type="number" class="form-control" name="transfer_enable" value="1024" id="transfer_enable" required>
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
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="protocol">协议</label>
								<div class="col-xl-5 col-sm-8">
									<select class="form-control" name="protocol" id="protocol" data-plugin="selectpicker" data-style="btn-outline btn-primary">
										@foreach ($protocol_list as $protocol)
											<option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>{{$protocol->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="obfs">混淆</label>
								<div class="col-xl-5 col-sm-8">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="obfs" id="obfs">
										@foreach ($obfs_list as $obfs)
											<option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>{{$obfs->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="protocol_param">协议参数</label>
								<div class="col-xl-6 col-sm-8">
									<input type="text" class="form-control" name="protocol_param" id="protocol_param" placeholder="节点单端口时无效" autocomplete="off"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="obfs_param">混淆参数</label>
								<div class="col-xl-6 col-sm-8">
									<textarea class="form-control" rows="3" name="obfs_param" id="obfs_param" placeholder="不填则取节点自定义混淆参数"></textarea>
								</div>
							</div>
							<hr>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="speed_limit_per_con">单连接限速</label>
								<div class="col-xl-6 col-sm-8">
									<div class="input-group">
										<input type="number" class="form-control" name="speed_limit_per_con" id="speed_limit_per_con" value="10737418240" autocomplete="off"/>
										<span class="input-group-text">Byte</span>
									</div>
									<span class="text-help"> 为 0 时不限速 </span>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-md-2 col-sm-3 col-form-label" for="speed_limit_per_user">单用户限速</label>
								<div class="col-xl-6 col-sm-8">
									<div class="input-group">
										<input type="number" class="form-control" name="speed_limit_per_user" id="speed_limit_per_user" value="10737418240" autocomplete="off"/>
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
										<input type="text" class="form-control" name="vmess_id" id="vmess_id" value="{{createGuid()}}" autocomplete="off"/>
										<span class="input-group-append"><button class="btn btn-success" type="button" onclick="makeVmessId()"> <i class="icon wb-refresh"></i> </button></span>
									</div>
									<span class="text-help"> V2Ray的账户ID </span>
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
	<script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-datepicker.js" type="text/javascript"></script>

	<script type="text/javascript">
        $('.input-daterange>input').datepicker({
            format: "yyyy-mm-dd",
        });

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
                url: "/admin/addUser",
                async: false,
                data: {
                    _token: '{{csrf_token()}}',
                    email: $('#email').val(),
                    password: $('#password').val(),
                    usage: usage.substring(0, usage.length - 1),
                    pay_way: $("input:radio[name='pay_way']:checked").val(),
                    status: $("input:radio[name='status']:checked").val(),
                    labels: $('#labels').val(),
                    reset_time: $('#reset_time').val(),
                    enable_time: $('#enable_time').val(),
                    expire_time: $('#expire_time').val(),
                    wechat: $('#wechat').val(),
                    qq: $('#qq').val(),
                    remark: $('#remark').val(),
                    level: $("#level option:selected").val(),
                    port: $('#port').val(),
                    passwd: $('#passwd').val(),
                    method: $('#method').val(),
                    transfer_enable: $('#transfer_enable').val(),
                    enable: $("input:radio[name='enable']:checked").val(),
                    protocol: $('#protocol').val(),
                    protocol_param: $('#protocol_param').val(),
                    obfs: $('#obfs').val(),
                    obfs_param: $('#obfs_param').val(),
                    speed_limit_per_con: $('#speed_limit_per_con').val(),
                    speed_limit_per_user: $('#speed_limit_per_user').val(),
                    vmess_id: $('#vmess_id').val()
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
