@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">回调日志
					<small>(在线支付)</small>
				</h2>
			</div>
			<div class="panel-body">
				<div class="form-row">
					<div class="form-group col-lg-3 col-sm-6">
						<input type="number" class="form-control" name="out_trade_no" id="out_trade_no" value="{{Request::get('out_trade_no')}}" placeholder="本地订单号" autocomplete="off"/>
					</div>
					<div class="form-group col-lg-3 col-sm-6">
						<input type="number" class="form-control" name="trade_no" id="trade_no" value="{{Request::get('trade_no')}}" placeholder="外部订单号" autocomplete="off"/>
					</div>
					<div class="form-group col-lg-2 col-sm-4">
						<select class="form-control" name="type" id="type" onChange="Search()">
							<option value="" @if(Request::get('type') == '') selected hidden @endif>支付方式</option>
							<option value="2" @if(Request::get('type') == '2') selected hidden @endif>码支付</option>
							<option value="3" @if(Request::get('type') == '3') selected hidden @endif>易支付</option>
							<option value="4" @if(Request::get('type') == '4') selected hidden @endif>支付宝国际</option>
							<option value="5" @if(Request::get('type') == '5') selected hidden @endif>当面付</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-4">
						<select class="form-control" name="trade_status" id="trade_status" onChange="Search()">
							<option value="" @if(Request::get('trade_status') == '') selected hidden @endif>交易状态</option>
							<option value="1" @if(Request::get('trade_status') == '1') selected hidden @endif>成功</option>
							<option value="0" @if(Request::get('trade_status') == '0') selected hidden @endif>失败</option>
						</select>
					</div>
					<div class="form-group col-lg-2 col-sm-4 btn-group">
						<button class="btn btn-primary" onclick="Search()">搜索</button>
						<a href="/payment/callbackList" class="btn btn-danger">重置</a>
					</div>
				</div>
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 支付方式</th>
						<th> 平台订单号</th>
						<th> 本地订单号</th>
						<th> 交易金额</th>
						<th> 状态</th>
					</tr>
					</thead>
					<tbody>
					@if($list->isEmpty())
						<tr>
							<td colspan="6">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td> {{$vo->type_label}} </td>
								<td> {{$vo->trade_no}} </td>
								<td>
									<a href="/admin/orderList?order_sn={{$vo->out_trade_no}}" target="_blank"> {{$vo->out_trade_no}} </a>
								</td>
								<td> {{$vo->amount}}元</td>
								<td> {!! $vo->trade_status_label !!} </td>
								<td> {{$vo->created_at}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$list->total()}}</code> 个账号
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$list->links()}}
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
            const trade_no = $("#trade_no").val();
            const out_trade_no = $("#out_trade_no").val();
            const type = $("#type option:selected").val();
            const trade_status = $("#trade_status option:selected").val();
            window.location.href = '/payment/callbackList?out_trade_no=' + out_trade_no + '&trade_no=' + trade_no + '&type=' + type + '&trade_status=' + trade_status;
        }
	</script>
@endsection
