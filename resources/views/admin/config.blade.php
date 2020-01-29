@extends('admin.layouts')
@section('css')
	<script src="//at.alicdn.com/t/font_682457_e6aq10jsbq0yhkt9.js" type="text/javascript"></script>
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title"><i class="icon wb-settings" aria-hidden="true"></i>通用配置</h1>
			</div>
			<div class="panel-body container-fluid">
				<div class="nav-tabs-vertical" data-plugin="tabs">
					<ul class="nav nav-tabs mr-25" role="tablist">
						<li class="nav-item" role="presentation">
							<a class="nav-link active" data-toggle="tab" href="#method" aria-controls="method" role="tab">加密</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#protocol" aria-controls="protocol" role="tab">协议</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#obfs" aria-controls="obfs" role="tab">混淆</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#level" aria-controls="level" role="tab">等级</a>
						</li>
						<li class="nav-item" role="presentation">
							<a class="nav-link" data-toggle="tab" href="#country" aria-controls="country" role="tab">国家地区</a>
						</li>
					</ul>
					<div class="tab-content py-15">
						<div class="tab-pane active" id="method" role="tabpanel">
							<button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal"> 新增
								<i class="icon wb-plus"></i></button>
							<table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
								<thead class="thead-default">
								<tr>
									<th> 名称</th>
									<th> 操作</th>
								</tr>
								</thead>
								<tbody>
								@if($method_list->isEmpty())
									<tr>
										<td colspan="2">暂无数据</td>
									</tr>
								@else
									@foreach($method_list as $method)
										<tr>
											<td> {{$method->name}}</td>
											<td>
												@if($method->is_default)
													<span class='badge badge-lg badge-default'>默认</span>
												@else
													<div class="btn-group">
														<button class="btn btn-primary" onclick="setDefault('{{$method->id}}')">默认</button>
														<button class="btn btn-danger" onclick="delConfig('{{$method->id}}','{{$method->name}}')">
															<i class="icon wb-trash"></i>
														</button>
													</div>
												@endif
											</td>
										</tr>
									@endforeach
								@endif
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="protocol" role="tabpanel">
							<button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal"> 新增
								<i class="icon wb-plus"></i></button>
							<table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
								<thead class="thead-default">
								<tr>
									<th> 名称</th>
									<th> 操作</th>
								</tr>
								</thead>
								<tbody>
								@if($protocol_list->isEmpty())
									<tr>
										<td colspan="2">暂无数据</td>
									</tr>
								@else
									@foreach($protocol_list as $protocol)
										<tr>
											<td> {{$protocol->name}}</td>
											<td>
												@if($protocol->is_default)
													<span class="badge badge-lg badge-default">默认</span>
												@else
													<div class="btn-group">
														<button class="btn btn-primary" onclick="setDefault('{{$protocol->id}}')">默认</button>
														<button class="btn btn-danger" onclick="delConfig('{{$protocol->id}}','{{$protocol->name}}')">
															<i class="icon wb-trash"></i>
														</button>
													</div>
												@endif
											</td>
										</tr>
									@endforeach
								@endif
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="obfs" role="tabpanel">
							<button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_config_modal"> 新增
								<i class="icon wb-plus"></i></button>
							<table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
								<thead class="thead-default">
								<tr>
									<th> 名称</th>
									<th> 操作</th>
								</tr>
								</thead>
								<tbody>
								@if($obfs_list->isEmpty())
									<tr>
										<td colspan="2">暂无数据</td>
									</tr>
								@else
									@foreach($obfs_list as $obfs)
										<tr>
											<td> {{$obfs->name}}</td>
											<td>
												@if($obfs->is_default)
													<span class="badge badge-lg badge-default">默认</span>
												@else
													<button class="btn btn-primary" onclick="setDefault('{{$obfs->id}}')">默认</button>
													<button class="btn btn-danger" onclick="delConfig('{{$obfs->id}}','{{$obfs->name}}')">
														<i class="icon wb-trash"></i>
													</button>
												@endif
											</td>
										</tr>
									@endforeach
								@endif
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="level" role="tabpanel">
							<button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_level_modal"> 新增
								<i class="icon wb-plus"></i></button>
							<table class="text-md-center" data-toggle="table" data-height="700" data-virtual-scroll="true" data-mobile-responsive="true">
								<thead class="thead-default">
								<tr>
									<th> 等级</th>
									<th> 名称</th>
									<th> 操作</th>
								</tr>
								</thead>
								<tbody>
								@if($level_list->isEmpty())
									<tr>
										<td colspan="3">暂无数据</td>
									</tr>
								@else
									@foreach($level_list as $level)
										<tr>
											<td>
												<input type="text" class="form-control" name="level" id="level_{{$level->id}}" value="{{$level->level}}"/>
											</td>
											<td>
												<input type="text" class="form-control" name="level_name" id="level_name_{{$level->id}}" value="{{$level->level_name}}"/>
											</td>
											<td>
												<div class="btn-group">
													<button type="button" class="btn btn-primary" onclick="updateLevel('{{$level->id}}')">修改</button>
													<button type="button" class="btn btn-danger" onclick="delLevel('{{$level->id}}','{{$level->level_name}}')">
														<i class="icon wb-trash"></i>
													</button>
												</div>
											</td>
										</tr>
									@endforeach
								@endif
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="country" role="tabpanel">
							<button class="btn btn-primary float-right mb-10" data-toggle="modal" data-target="#add_country_modal"> 新增
								<i class="icon wb-plus"></i></button>
							<table class="text-md-center" data-toggle="table" data-height="600" data-virtual-scroll="true" data-mobile-responsive="true">
								<thead class="thead-default">
								<tr>
									<th> 图标</th>
									<th> 国家/地区名称</th>
									<th> 代码</th>
									<th> 操作</th>
								</tr>
								</thead>
								<tbody>
								@if($country_list->isEmpty())
									<tr>
										<td colspan="4">暂无数据</td>
									</tr>
								@else
									@foreach($country_list as $country)
										<tr>
											<td>
												<svg class="w-40 h-40 text-center" aria-hidden="true">
													<use xlink:href="@if($country->code)#icon-{{$country->code}}@endif"></use>
												</svg>
											</td>
											<td>
												<input type="text" class="form-control" name="country_name" id="country_name_{{$country->id}}" value="{{$country->name}}"/>
											</td>
											<td>
												<input type="text" class="form-control" name="country_code" id="country_code_{{$country->id}}" value="{{$country->code}}"/>
											</td>
											<td>
												<div class="btn-group">
													<button type="button" class="btn btn-primary" onclick="updateCountry('{{$country->id}}')">修改</button>
													<button type="button" class="btn btn-danger" onclick="delCountry('{{$country->id}}','{{$country->name}}')"><i class="icon wb-trash"></i></button>
												</div>
											</td>
										</tr>
									@endforeach
								@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="add_config_modal" aria-hidden="true" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">新增配置</h4>
				</div>
				<form action="#" method="post" class="modal-body">
					<div class="alert alert-danger" style="display: none;" id="msg"></div>
					<div class="row">
						<div class="col-md-6 form-group">
							<select class="form-control" name="type" id="type" placeholder="类型">
								<option value="1" selected>加密方式</option>
								<option value="2">协议</option>
								<option value="3">混淆</option>
							</select>
						</div>
						<div class="col-md-6 form-group">
							<input type="text" class="form-control" name="name" id="name" placeholder="名称">
						</div>
					</div>
				</form>
				<div class="modal-footer">
					<button class="btn btn-danger" data-dismiss="modal">关 闭</button>
					<button class="btn btn-primary" onclick="addConfig()">提 交</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="add_level_modal" aria-hidden="true" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">新增配置</h4>
				</div>
				<form action="#" method="post" class="modal-body">
					<div class="alert alert-danger" style="display: none;" id="level_msg"></div>
					<div class="row">
						<div class="col-md-6 form-group">
							<input type="text" class="form-control" name="level" id="add_level" placeholder="等级">
						</div>
						<div class="col-md-6 form-group">
							<input type="text" class="form-control" name="level_name" id="add_level_name" placeholder="等级名称">
						</div>
					</div>
				</form>
				<div class="modal-footer">
					<button data-dismiss="modal" class="btn btn-danger">关 闭</button>
					<button class="btn btn-primary" onclick="addLevel('4')">提 交</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="add_country_modal" aria-hidden="true" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">新增国家/地区</h4>
				</div>
				<form action="#" method="post" class="modal-body">
					<div class="alert alert-danger" style="display: none;" id="country_msg"></div>
					<div class="row">
						<div class="col-md-6 form-group">
							<input type="text" class="form-control" name="country_name" id="add_country_name" placeholder=" 国家/地区名称">
						</div>
						<div class="col-md-6 form-group">
							<input type="text" class="form-control" name="country_code" id="add_country_code" placeholder="国家代码">
						</div>
					</div>
				</form>
				<div class="modal-footer">
					<button data-dismiss="modal" class="btn btn-danger">关 闭</button>
					<button class="btn btn-primary" onclick="addCountry('5')">提 交</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/tabs.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>

	<script type="text/javascript">
        // 添加等级
        function addLevel() {
            const level = $('#add_level').val();
            const level_name = $('#add_level_name').val();

            if (level.trim() === '') {
                $("#level_msg").show().html("等级不能为空");
                $("#level").focus();
                return false;
            }

            if (level_name.trim() === '') {
                $("#level_msg").show().html("等级名称不能为空");
                $("#level_name").focus();
                return false;
            }

            $.ajax({
                url: '/admin/addLevel',
                type: "POST",
                data: {_token: '{{csrf_token()}}', level: level, level_name: level_name},
                beforeSend: function () {
                    $("#level_msg").show().html("正在添加");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#level_msg").show().html(ret.message);
                        return false;
                    }
                    $("#add_level_modal").modal("hide");
                    window.location.href = '/admin/config';
                },
                error: function () {
                    $("#level_msg").show().html("请求错误，请重试");
                },
                complete: function () {
                    swal.fire({title: '添加成功', type: "success", timer: 1000, showConfirmButton: false}).then(() => window.location.reload())
                }
            });
        }

        // 更新等级
        function updateLevel(id) {
            $.ajax({
                type: "POST",
                url: "/admin/updateLevel",
                async: false,
                data: {_token: '{{csrf_token()}}', id: id, level: $('#level_' + id).val(), level_name: $('#level_name_' + id).val()},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/config')
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
        }

        // 删除等级
        function delLevel(id, name) {
            swal.fire({
                title: '确定删除等级 【' + name + '】 ？',
                type: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delLevel", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.href = '/admin/config')
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            })
        }

        // 添加国家/地区
        function addCountry() {
            const country_name = $('#add_country_name').val();
            const country_code = $('#add_country_code').val();

            if (country_name.trim() === '') {
                $("#country_msg").show().html("国家/地区名称不能为空");
                $("#add_country_name").focus();
                return false;
            }

            if (country_code.trim() === '') {
                $("#country_msg").show().html("国家/地区代码不能为空");
                $("#add_country_code").focus();
                return false;
            }

            $.ajax({
                url: '/admin/addCountry',
                type: "POST",
                data: {_token: '{{csrf_token()}}', country_name: country_name, country_code: country_code},
                beforeSend: function () {
                    $("#country_msg").show().html("正在添加");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#country_msg").show().html(ret.message);
                        return false;
                    }
                    $("#add_country_modal").modal("hide");
                    window.location.href = '/admin/config';
                },
                error: function () {
                    $("#country_msg").show().html("请求错误，请重试");
                },
                complete: function () {
                    swal.fire({title: '添加成功', type: "success", timer: 1000, showConfirmButton: false}).then(() => window.location.reload())
                }
            });
        }

        // 更新国家/地区
        function updateCountry(id) {
            const country_name = $('#country_name_' + id).val();
            const country_code = $('#country_code_' + id).val();

            $.ajax({
                type: "POST",
                url: "/admin/updateCountry",
                async: false,
                data: {_token: '{{csrf_token()}}', id: id, country_name: country_name, country_code: country_code},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/config')
                    } else {
                        swal.fire({title: ret.message, type: "error"});
                    }
                }
            });
        }

        // 删除国家/地区
        function delCountry(id, name) {
            swal.fire({
                title: '确定删除 【' + name + '】 信息？',
                type: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delCountry", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.href = '/admin/config')
                        } else {
                            swal.fire({title: ret.message, type: "error"});
                        }
                    });
                }
            })
        }

        // 添加配置
        function addConfig() {
            const name = $("#name").val();
            const type = $("#type").val();

            if (name.trim() === '') {
                $("#msg").show().html("名称不能为空");
                $("#name").focus();
                return false;
            }

            $.ajax({
                url: '/admin/config',
                type: "POST",
                data: {_token: '{{csrf_token()}}', name: name, type: type},
                beforeSend: function () {
                    $("#msg").show().html("正在添加");
                },
                success: function (ret) {
                    if (ret.status === 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    }

                    $("#add_config_modal").modal("hide");
                },
                error: function () {
                    $("#msg").show().html("请求错误，请重试");
                },
                complete: function () {
                    swal.fire({title: '添加成功', type: "success", timer: 1000, showConfirmButton: false}).then(() => window.location.reload())
                }
            });
        }

        // 删除配置
        function delConfig(id, name) {
            swal.fire({
                title: '确定删除配置 【' + name + '】 ？',
                type: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delConfig", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.href = '/admin/config')
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            })
        }

        // 置为默认
        function setDefault(id) {
            $.ajax({
                type: "POST",
                url: "/admin/setDefaultConfig",
                async: false,
                data: {_token: '{{csrf_token()}}', id: id},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/config')
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
        }
	</script>
@endsection
