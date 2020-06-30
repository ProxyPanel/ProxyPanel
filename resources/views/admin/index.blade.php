@extends('admin.layouts')
@section('css')
	<link href="/assets/global/fonts/material-design/material-design.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="row" data-by-row="true">
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">总用户</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?enable=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-info">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">有效用户</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$enableUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?active=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-success">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">{{$expireDays}}日内活跃用户</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$activeUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?unActive=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-warning">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">{{$expireDays}}日以上不活跃用户</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$unActiveUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?online=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-success">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">当前在线</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$onlineUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?expireWarning=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-danger">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">临近到期</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$expireWarningUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?largeTraffic=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-warning">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">流量大户（超过100G的用户）</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$largeTrafficUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userList?flowAbnormal=1" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-danger">
							<i class="icon md-account"></i>
						</button>
						<span class="ml-15 font-weight-400">1小时内流量异常</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$flowAbnormalUserCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/node" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-cloud"></i>
						</button>
						<span class="ml-15 font-weight-400">节点</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$nodeCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/node?status=0" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-info">
							<i class="icon md-cloud-off"></i>
						</button>
						<span class="ml-15 font-weight-400">维护中的节点</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$unnormalNodeCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/trafficLog" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-time-countdown"></i>
						</button>
						<span class="ml-15 font-weight-400">总消耗流量</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalFlowCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/trafficLog" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-time-countdown"></i>
						</button>
						<span class="ml-15 font-weight-400">30日内消耗流量</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$flowCount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/orderList" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-ticket-star"></i>
						</button>
						<span class="ml-15 font-weight-400">总订单数</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalOrder}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/orderList" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-info">
							<i class="icon md-ticket-star"></i>
						</button>
						<span class="ml-15 font-weight-400">在线支付订单数</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalOnlinePayOrder}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/orderList?status=2" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-success">
							<i class="icon md-ticket-star"></i>
						</button>
						<span class="ml-15 font-weight-400">支付成功订单数</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalSuccessOrder}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/orderList?status=2&range_time={{date('Y-m-d 00:00:00') . ',' . date('Y-m-d 23:59:59')}}"
						class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-success">
							<i class="icon md-ticket-star"></i>
						</button>
						<span class="ml-15 font-weight-400">今天成功订单数</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$todaySuccessOrder}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<div class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-primary">
							<i class="icon md-money"></i>
						</button>
						<span class="ml-15 font-weight-400">总余额</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalCredit}}</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<a href="/admin/userRebateList" class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-warning">
							<i class="icon md-money"></i>
						</button>
						<span class="ml-15 font-weight-400">待提现佣金</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalWaitRefAmount}}</span>
						</div>
					</div>
				</a>
			</div>
			<div class="col-xl-3 col-md-6 info-panel">
				<div class="card card-shadow">
					<div class="card-block bg-white">
						<button type="button" class="btn btn-floating btn-sm btn-dark">
							<i class="icon md-money"></i>
						</button>
						<span class="ml-15 font-weight-400">已支出佣金</span>
						<div class="content-text text-center mb-0">
							<span class="font-size-40 font-weight-100">{{$totalRefAmount}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/matchheight.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(function () {
			$('.card').matchHeight();
		});
	</script>
@endsection
