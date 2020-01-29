@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title">卡券列表</h1>
				<div class="panel-actions btn-group">
					<button class="btn btn-info" onclick="exportCoupon()"><i class="icon wb-code"></i>批量导出</button>
					<a href="/coupon/addCoupon" class="btn btn-primary"><i class="icon wb-plus"></i>生成</a>
				</div>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-3 col-sm-4">
						<input type="text" class="form-control" name="sn" id="sn" value="{{Request::get('sn')}}" placeholder="券码" autocomplete="off"/>
					</div>
					<div class="form-group col-lg-3 col-sm-4">
						<select class="form-control" name="type" id="type" onChange="Search()">
							<option value="" @if(Request::get('type') == '') selected hidden @endif>类型</option>
							<option value="1" @if(Request::get('type') == '1') selected hidden @endif>现金券</option>
							<option value="2" @if(Request::get('type') == '2') selected hidden @endif>折扣券</option>
							<option value="3" @if(Request::get('type') == '3') selected hidden @endif>充值券</option>
						</select>
					</div>
					<div class="form-group col-lg-3 col-sm-4">
						<select class="form-control" name="status" id="status" onChange="Search()">
							<option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
							<option value="0" @if(Request::get('status') == '0') selected hidden @endif>未使用/生效中</option>
							<option value="1" @if(Request::get('status') == '1') selected hidden @endif>已使用</option>
							<option value="2" @if(Request::get('status') == '2') selected hidden @endif>已失效</option>
						</select>
					</div>
					<div class="form-group col-lg-3 col-sm-4 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜 索</button>
						<a href="/coupon/couponList" class="btn btn-danger">重 置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 名称</th>
						<th> 券码</th>
						<th> 图片</th>
						<th> 类型</th>
						<th> 用途</th>
						<th> 优惠</th>
						<th> 有效期</th>
						<th> 状态</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@if($couponList->isEmpty())
						<tr>
							<td colspan="10">暂无数据</td>
						</tr>
					@else
						@foreach($couponList as $coupon)
							<tr>
								<td> {{$coupon->id}} </td>
								<td> {{$coupon->name}} </td>
								<td> {{$coupon->sn}} </td>
								<td> @if($coupon->logo) <img src="{{$coupon->logo}}" alt="优惠码logo"/> @endif </td>
								<td>
									@if($coupon->type == '1')
										抵用券
									@elseif($coupon->type == '2')
										折扣券
									@else
										充值券
									@endif
								</td>
								<td> {{$coupon->usage == '1' ? '一次性' : '重复使用'}} </td>
								<td>
									@if($coupon->type == 2)
										{{$coupon->discount}}折
									@else
										{{$coupon->amount}}元
									@endif
								</td>
								<td> {{date('Y-m-d', $coupon->available_start)}} ~ {{date('Y-m-d', $coupon->available_end)}} </td>
								<td>
									@if($coupon->usage == 1)
										@if($coupon->status == '1')
											<span class="badge badge-lg badge-default"> 已使用 </span>
										@elseif ($coupon->status == '2')
											<span class="badge badge-lg badge-default"> 已失效 </span>
										@else
											<span class="badge badge-lg badge-success"> 未使用 </span>
										@endif
									@elseif ($coupon->usage == 2)
										@if ($coupon->status == '2')
											<span class="badge badge-lg badge-default"> 已失效 </span>
										@else
											<span class="badge badge-lg badge-success"> 生效中 </span>
										@endif
									@endif
								</td>
								<td>
									@if($coupon->status != '1')
										<button class="btn btn-danger" onclick="delCoupon('{{$coupon->id}}','{{$coupon->name}}')"><i class="icon wb-close"></i></button>
									@endif
								</td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$couponList->total()}}</code> 张优惠券
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$couponList->links()}}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
	<script type="text/javascript">
        //回车检测
        $(document).on("keypress", "input", function (e) {
            if (e.which === 13) {
                Search();
                return false;
            }
        });

        // 搜索
        function Search() {
            window.location.href = '/coupon/couponList' + '?sn=' + $("#sn").val() + '&type=' + $("#type").val() + '&status=' + $("#status").val();
        }

        // 批量导出卡券
        function exportCoupon() {
            swal.fire({
                title: '卡券导出',
                text: '确定导出所有卡券吗？',
                type: 'question',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    window.location.href = '/coupon/exportCoupon';
                }
            });
        }

        // 删除卡券
        function delCoupon(id, name) {
            swal.fire({
                title: '确定删除卡券 【' + name + '】 吗？',
                type: 'question',
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/coupon/delCoupon", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"})
                        }
                    });
                }
            })
        }
	</script>
@endsection
