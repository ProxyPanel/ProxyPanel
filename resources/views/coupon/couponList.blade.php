@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet" type="text/css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">卡券列表</h3>
                <div class="panel-actions btn-group">
                    <button class="btn btn-info" onclick="exportCoupon()"><i class="icon wb-code"></i>批量导出</button>
                    <button class="btn btn-primary" onclick="addCoupon()"><i class="icon wb-plus"></i>生成</button>
                </div>
            </div>
            <div class="panel-body">
				<div class="form-inline mb-20">
					<div class="form-group">
						<select class="form-control" name="type" id="type" onChange="do_search()">
							<option value="" @if(Request::get('type') == '') selected @endif>类型</option>
							<option value="1" @if(Request::get('type') == '1') selected @endif>现金券</option>
							<option value="2" @if(Request::get('type') == '2') selected @endif>折扣券</option>
							<option value="3" @if(Request::get('type') == '3') selected @endif>充值券</option>
						</select>
						<input type="text" class="form-control" name="sn" value="{{Request::get('sn')}}" id="sn" placeholder="券码" autocomplete="off" onkeydown="if(event.keyCode==13){do_search();}">
					</div>
					<div class="btn-group">
						<button class="btn btn-primary" onclick="doSearch()">搜索</button>
						<button class="btn btn-danger" onclick="doReset()">重置</button>
					</div>
				</div>
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 券码</th>
                        <th> LOGO</th>
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
                                <td> @if($coupon->logo)<<img src="{{$coupon->logo}}" alt="优惠码logo"/> @endif </td>
                                <td>
                                    @if($coupon->type == '1')
                                        抵用券
                                    @elseif($coupon->type == '2')
                                        折扣券
                                    @else
                                        充值券
                                    @endif
                                </td>
                                <td> {{$coupon->usage == '1' ? '一次性' : '可重复'}} </td>
                                <td>
                                    @if($coupon->type == '1' || $coupon->type == '3')
                                        {{$coupon->amount}}元
                                    @else
                                        {{$coupon->discount}}折
                                    @endif
                                </td>
                                <td> {{date('Y-m-d', $coupon->available_start)}} ~ {{date('Y-m-d', $coupon->available_end)}} </td>
                                <td>
                                    @if ($coupon->usage == 1)
                                        @if($coupon->status == '1')
                                            <span class="badge badge-lg badge-default"> 已使用 </span>
                                        @elseif ($coupon->status == '2')
                                            <span class="badge badge-lg badge-default"> 已失效 </span>
                                        @else
                                            <span class="badge badge-lg badge-success"> 未使用 </span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->status != '1')
                                        <button class="btn btn-danger" onclick="delCoupon('{{$coupon->id}}')"><i class="icon wb-close"></i></button>
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
                    <div class="col-md-4 col-sm-4">
                        共 {{$couponList->total()}} 张优惠券
                    </div>
                    <div class="col-md-8 col-sm-8">
                        <div class="Page navigation float-right">
                            {{ $couponList->links() }}
                        </div>
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

        // 添加卡券
        function addCoupon() {
            window.location.href = '/coupon/addCoupon';
        }

        // 删除卡券
        function delCoupon(id) {
            swal.fire({
                title: '确定删除该卡券吗？',
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
