@extends('user.layouts')
@section('css')
	<link href="/assets/global/fonts/font-awesome/font-awesome.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/aspieprogress/asPieProgress.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="row" data-plugin="matchHeight" data-by-row="true">
			@if (Session::has('successMsg'))
				<div class="col-md-12 alert alert-success" role="alert">
					<button class="close" data-dismiss="alert" aria-label="Close"><span
								aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span>
					</button>
					{{Session::get('successMsg')}}
				</div>
			@endif
			<div class="col-xxl-3 col-xl-4 col-lg-5 col-md-6 col-12">
				<div class="card card-shadow">
					<div class="card-block p-20">
						<button type="button" class="btn btn-floating btn-sm btn-pure">
							<i class="wb-heart red-500"></i>
						</button>
						<span class="font-weight-400">{{trans('home.account_status')}}</span>
						@if(\App\Components\Helpers::systemConfig()['is_checkin'])
							<a class="btn btn-md btn-round btn-info float-right" href="javascript:checkIn();">
								<i class="wb-star yellow-400 mr-5"></i>
								{{trans('home.sign_in')}}
							</a>
						@endif
						<div class="content-text text-center mb-0">
							@if($not_paying_user)
								<p class="ml-15 mt-15 text-left">更长使用<code>时间</code></p>
								<p class="text-center">更多<code>流量</code></p>
								<p class="mb-15 mr-15 text-right">更多优质<code>线路</code></p>
								<a href="/services" class="btn btn-block btn-danger">快 来 购 买 服 务 吧！</a>
							@elseif(Auth::getUser()->enable)
								<i class="wb-check green-400 font-size-40 mr-10"></i>
								<span class="font-size-40 font-weight-100">{{trans('home.enabled')}}</span>
								<p class="font-weight-300 m-0 green-500">{{trans('home.normal')}}</p>
							@elseif($remainDays == 0)
								<i class="wb-close red-400 font-size-40 mr-10"></i>
								<span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
								<p class="font-weight-300 m-0 red-500">{{trans('home.reason_expired')}}</p>
							@elseif($unusedTransfer == 0)
								<i class="wb-close red-400 font-size-40 mr-10"></i>
								<span class="font-size-40 font-weight-100">{{trans('home.disabled')}}</span>
								<p class="font-weight-300 m-0 red-500">{{trans('home.reason_traffic_exhausted')}}</p>
							@elseif($isTrafficWarning || $banedTime != 0)
								<i class="wb-alert orange-400 font-size-40 mr-10"></i>
								<span class="font-size-40 font-weight-100">{{trans('home.limited')}}</span>
								<p class="font-weight-300 m-0 orange-500">{!!trans('home.reason_overused', ['data'=>\App\Components\Helpers::systemConfig()['traffic_ban_value']])!!}</p>
							@else
								<i class="wb-help red-400 font-size-40 mr-10"></i>
								<span class="font-size-40 font-weight-100">{{trans('home.disabled')}}</span>
								<p class="font-weight-300 m-0 red-500">{{trans('home.reason_unknown')}}</p>
							@endif
						</div>
					</div>
				</div>
				<div class="card card-shadow">
					<div class="card-block p-20">
						<div class="row">
							<div class="col-lg-7 col-md-12 col-sm-7">
								<button type="button" class="btn btn-floating btn-sm btn-pure">
									<i class="wb-stats-bars cyan-500"></i>
								</button>
								<span class="font-weight-400">{{trans('home.account_bandwidth_usage')}}</span>
								<div class="text-center font-weight-100 font-size-40">
									{{flowAutoShow($unusedTransfer)}}
									<br>
									<h4>账号等级：<code class="font-size-20">{{Auth::getUser()->level}}</code></h4>
								</div>
								<div class="text-center font-weight-300 blue-grey-500 mb-10">
									@if(!$not_paying_user && \App\Components\Helpers::systemConfig()['reset_traffic'] && $resetDays != 0 && $remainDays>$resetDays)
										{{trans('home.account_reset_notice', ['reset_day' => $resetDays])}}
									@endif
								</div>
							</div>
							<div class="col-lg-5 col-md-12 col-sm-5">
								<div class="w-only-xs-p50 w-only-sm-p75 w-only-md-p50" data-plugin="pieProgress"
										data-valuemax="100"
										data-barcolor="#96A3FA" data-size="100" data-barsize="10"
										data-goal="{{$unusedPercent * 100}}" aria-valuenow="{{$unusedPercent * 100}}"
										role="progressbar">
									<span class="pie-progress-number blue-grey-700 font-size-20">{{$unusedPercent * 100}}%</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card card-shadow">
					<div class="card-block p-20">
						<button type="button" class="btn btn-floating btn-sm btn-pure">
							<i class="wb-calendar green-500"></i>
						</button>
						<span class="font-weight-400">{{trans('home.account_expire')}}</span>
						<div class="content-text text-center mb-0">
							@if($remainDays != -1)
								<span class="font-size-40 font-weight-100">{{$remainDays}} {{trans('home.day')}}</span>
								<p class="blue-grey-500 font-weight-300 m-0">{{$expireTime}}</p>
							@else
								<span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
								<br/>
								<a href="/services" class="btn btn-danger">{{trans('home.service_buy_button')}}</a>
							@endif
						</div>
					</div>
				</div>
				@if($userLoginLog)
					<div class="card card-shadow">
						<div class="card-block p-20">
							<button type="button" class="btn btn-floating btn-sm btn-pure">
								<i class="wb-globe purple-500"></i>
							</button>
							<span class="font-weight-400 mb-10">{{trans('home.account_last_login')}}</span>
							<ul class="list-group list-group-dividered px-20 mb-0">
								<li class="list-group-item px-0">
									<i class="icon wb-time"></i>时间：{{date_format($userLoginLog->created_at,'Y/m/d H:i')}}
								</li>
								<li class="list-group-item px-0"><i class="icon wb-code"></i>IP地址：{{$userLoginLog->ip}}
								</li>
								<li class="list-group-item px-0"><i class="icon wb-cloud"></i>运营商：{{$userLoginLog->isp}}
								</li>
								<li class="list-group-item px-0"><i class="icon wb-map"></i>地区：{{$userLoginLog->area}}
								</li>
							</ul>
						</div>
					</div>
				@endif
			</div>
			<div class="col-xxl-9 col-xl-8 col-lg-7 col-md-6 col-12">
				<div class="row" data-plugin="matchHeight" data-by-row="true">
					<div class="col-xl-4 col-lg-6 pb-30">
						<div class="card card-shadow h-full">
							<div class="card-block text-center p-20">
								<i class="font-size-40 wb-wrench"></i>
								<h4 class="card-title">客户端</h4>
								<p class="card-text">下载 & 教程 </p>
								<a href="/help#answer-2" class="btn btn-primary mb-10">前往</a>
							</div>
						</div>
					</div>
					<div class="col-xl-4 col-lg-6 pb-30">
						<div class="card card-shadow text-center h-full">
							<div class="card-block">
								@if(\App\Components\Helpers::systemConfig()['is_push_bear'] && \App\Components\Helpers::systemConfig()['push_bear_qrcode'])
									<h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>微信公告推送</h4>
									<img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->margin(2)->eyeColor(1, 0, 204, 153, 0, 153, 119)->style('round', 0.9)->size(175)->errorCorrection('H')->merge(url('assets/images/wechat.png'), .3, true)->generate(\App\Components\Helpers::systemConfig()['push_bear_qrcode']))!!}" alt="支付二维码">
								@else
									<h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>交流群</h4>
								@endif
							</div>
						</div>
					</div>
				</div>
				<div class="row" data-plugin="matchHeight" data-by-row="true">
					<div class="col-xxl-6 mb-35">
						<div class="panel panel-info panel-line h-full">
							<div class="panel-heading">
								<h2 class="panel-title"><i
											class="wb-volume-high mr-10"></i>{{trans('home.announcement')}}
								</h2>
								<div class="panel-actions">
									<nav>
										<ul class="pagination pagination-no-border">
											<li class="page-item">
												{{ $noticeList->links() }}
											</li>
										</ul>
									</nav>
								</div>
							</div>
							<div class="panel-body" data-show-on-hover="false" data-direction="vertical"
									data-skin="scrollable-shadow" data-plugin="scrollable">
								<div data-role="container">
									<div class="pb-10" data-role="content">
										@if(!$noticeList -> isEmpty())
											@foreach($noticeList as $notice)
												<h3 class="text-center">{!!$notice->title!!}</h3>
												{!! $notice->content !!}
												@if ($notice->updated_at)
													<small class="text-bottom">更新于 <code>{{$notice->updated_at}}</code></small>
												@endif
											@endforeach
										@else
											<p class="text-center font-size-40">暂无公告</p>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xxl-6">
						<div class="panel panel-primary panel-line h-full">
							<div class="panel-heading">
								<h1 class="panel-title"><i class="wb-pie-chart mr-10"></i>{{trans('home.traffic_log')}}
								</h1>
								<div class="panel-actions">
									<ul class="nav nav-pills" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-toggle="tab" href="#daily"
													aria-controls="daily" role="tab" aria-expanded="true"
													aria-selected="false">天</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#monthly"
													aria-controls="monthly" role="tab" aria-selected="true">月</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="alert alert-danger alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">×</span>
									<span class="sr-only">Close</span>
								</button>
								{{trans('home.traffic_log_tips')}}
							</div>
							<div class="panel-body">
								<div class="tab-content">
									<div class="tab-pane active" id="daily" role="tabpanel">
										<canvas id="dailyChart" aria-label="小时流量图" role="img"></canvas>
									</div>
									<div class="tab-pane" id="monthly" role="tabpanel">
										<canvas id="monthlyChart" aria-label="月流量图" role="img"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/global/vendor/aspieprogress/jquery-asPieProgress.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/chart-js/Chart.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/aspieprogress.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/matchheight.js" type="text/javascript"></script>
	<script type="text/javascript">
		// 签到
		function checkIn() {
			$.post('/checkIn', {_token: '{{csrf_token()}}'}, function (ret) {
				if (ret.status === 'success') {
					swal.fire('长者的微笑', ret.message, 'success');
				} else {
					swal.fire({
						title: ret.message,
						type: 'error',
					});
				}
			});
		}

		const dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
			type: 'line',
			data: {
				labels: {{$dayHours}},
				datasets: [{
					fill: true,
					backgroundColor: "rgba(98, 168, 234, .1)",
					borderColor: Config.colors("primary", 600),
					pointRadius: 4,
					borderDashOffset: 2,
					pointBorderColor: "#fff",
					pointBackgroundColor: Config.colors("primary", 600),
					pointHoverBackgroundColor: "#fff",
					pointHoverBorderColor: Config.colors("primary", 600),
					data: {{$trafficHourly}},
				}]
			},
			options: {
				legend: {
					display: false
				},
				responsive: true,
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: '小时'
						}
					}],
					yAxes: [{
						display: true,
						ticks: {
							beginAtZero: true,
							userCallback: function (tick) {
								return tick.toString() + ' GB';
							}
						},
						scaleLabel: {
							display: true,
							labelString: '{{trans('home.traffic_log_24hours')}}'
						}
					}]
				}
			}
		});

		const monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
			type: 'line',
			data: {
				labels: {{$monthDays}},
				datasets: [{
					fill: true,
					backgroundColor: "rgba(98, 168, 234, .1)",
					borderColor: Config.colors("primary", 600),
					pointRadius: 4,
					borderDashOffset: 2,
					pointBorderColor: "#fff",
					pointBackgroundColor: Config.colors("primary", 600),
					pointHoverBackgroundColor: "#fff",
					pointHoverBorderColor: Config.colors("primary", 600),
					data: {{$trafficDaily}},
				}]
			},
			options: {
				legend: {
					display: false
				},
				responsive: true,
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: '天'
						}
					}],
					yAxes: [{
						display: true,
						ticks: {
							beginAtZero: true,
							userCallback: function (tick) {
								return tick.toString() + ' GB';
							}
						},
						scaleLabel: {
							display: true,
							labelString: '{{trans('home.traffic_log_30days')}}'
						}
					}]
				}
			}
		});

		@if($banedTime != 0)
		// 每秒更新计时器
		const countDownDate = new Date("{{$banedTime}}").getTime();
		const x = setInterval(function () {
			const distance = countDownDate - new Date().getTime();
			const hours = Math.floor(distance % 86400000 / 3600000);
			const minutes = Math.floor((distance % 3600000) / 60000);
			const seconds = Math.floor((distance % 60000) / 1000);
			document.getElementById("countdown").innerHTML = hours + "时 " + minutes + "分 " + seconds + "秒";
			if (distance <= 0) {
				clearInterval(x);
				document.getElementById("countdown").remove();
			}
		}, 1000);
		@endif
	</script>
@endsection
