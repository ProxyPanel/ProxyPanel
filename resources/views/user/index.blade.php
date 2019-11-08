@extends('user.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/fonts/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/aspieprogress/asPieProgress.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row" data-plugin="matchHeight" data-by-row="true">
            @if (Session::has('successMsg'))
                <div class="col-md-12 alert alert-success" role="alert">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    {{Session::get('successMsg')}}
                </div>
            @endif
            {{--            @if(!empty($info['t']))--}}
            {{--                <div class="col-12 alert alert-info text-center" role="alert">--}}
            {{--                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>--}}
            {{--                    <button class="site-tour-trigger btn btn-outline-info text-center">点我激活 面板介绍 功能</button>--}}
            {{--                </div>--}}
            {{--            @endif--}}


            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-pure">
                            <i class="wb-heart red-500"></i>
                        </button>
                        <span class="font-weight-400">{{trans('home.account_status')}}</span>
                        @if(\App\Components\Helpers::systemConfig()['is_checkin'])
                            <a class="btn btn-md btn-round btn-info float-right" href="javascript:checkIn();"><i class="wb-star yellow-400 mr-5"></i>
                                {{trans('home.sign_in')}}
                            </a>
                        @endif
                        <div class="content-text text-center mb-0">
                            @if($info['enable'])
                                <i class="wb-check green-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.enabled')}}</span>
                                <p class="font-weight-300 m-0 green-500">{{trans('home.normal')}}</p>
                            @elseif($info['remainDays'] == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('home.reason_expired')}}</p>
                            @elseif($info['unusedTransfer'] == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.disabled')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('home.reason_traffic_exhausted')}}</p>
                            @elseif($isTrafficWarning)
                                <i class="wb-alert orange-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.limited')}}</span>
                                <p class="font-weight-300 m-0 orange-500">{{trans('home.reason_overused')}}</p>
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
                                    {{$info['unusedTransfer']}}
                                </div>
                                @if( \App\Components\Helpers::systemConfig()['reset_traffic'] && $info['resetDays'] != 0 && $info['remainDays']>$info['resetDays'])
                                    <div class="text-center font-weight-300 blue-grey-500">
                                        {{trans('home.account_reset_notice', ['reset_day' => $info['resetDays']])}}
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-5 col-md-12 col-sm-5">
                                <div class="w-only-xs-p50 w-only-sm-p75 w-only-md-p50" data-plugin="pieProgress" data-valuemax="100"
                                     data-barcolor="#96A3FA" data-size="100" data-barsize="10"
                                     data-goal="{{$info['unusedPercent'] * 100}}" aria-valuenow="{{$info['unusedPercent'] * 100}}" role="progressbar">
                                    <span class="pie-progress-number blue-grey-700 font-size-20">{{$info['unusedPercent'] * 100}}%</span>
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
                            @if($info['remainDays'] != 0)
                                <span class="font-size-40 font-weight-100">{{$info['remainDays']}} 天</span>
                                <p class="blue-grey-500 font-weight-300 m-0">{{$info['expire_time']}}</p>
                            @else
                                <span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
                                <br/>
                                <a href="{{url('services')}}" class="btn btn-danger">{{trans('home.service_buy_button')}}</a>
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
            <div class="col-xl-8 col-lg-7 col-md-6">
                <div class="row">
                    <div class="col-xl-4 col-lg-6">
                        <div class="card card-shadow">
                            <div class="card-block text-center p-20">
                                <i class="font-size-40 wb-wrench"></i>
                                <h4 class="card-title">客户端</h4>
                                <p class="card-text">下载 & 教程 </p>
                                <a href="{{url('help#answer-2')}}" class="btn btn-primary mb-10">前往</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6">
                        <div class="card card-shadow text-center">
                            <div class="card-block">
                                @if(\App\Components\Helpers::systemConfig()['is_push_bear'] && \App\Components\Helpers::systemConfig()['push_bear_qrcode'])
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>微信公告推送</h4>
                                    <p class="card-text" id="subscribe_qrcode"></p>
                                @else
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>交流群</h4>
                                @endif
                                @if($is_paying_user)
                                    <p class="card-link btn btn-outline btn-primary">
                                        <i class="wb-lock mr-5"></i>购买套餐后解锁 用户交流群信息</p>
                                @else
                                    <a class="card-link btn btn-pill-left btn-info" href="#" target="_blank" rel="noopener">
                                        <i class="fa fa-qq"></i> QQ群</a>
                                    <a class="card-link btn btn-pill-right btn-success" href="#" target="_blank" rel="noopener">TG群
                                        <i class="fa fa-paper-plane"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6">
                        {{--                        <div class="card card-shadow">--}}
                        {{--                            <div class="card-block p-20">--}}
                        {{--                                <h4 class="card-title"><i class="wb-info-circle blue-600 mr-10"></i>账号信息</h4>--}}
                        {{--                                <!--  TO Do--}}
                        {{--                                 添加 权限 信息--}}
                        {{--                                 添加 账号 总流量 信息--}}
                        {{--                                 -->--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    <div class="col-xl-6 col-lg-12">
                        <div class="panel panel-info panel-line">
                            <div class="panel-heading">
                                <h2 class="panel-title"><i class="wb-volume-high mr-10"></i>{{trans('home.announcement')}}
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
                                    <div data-role="content">
                                        @if(!$noticeList -> isEmpty())
                                            @foreach($noticeList as $notice)
                                                <h3 class="text-center">{!!$notice->title!!}</h3>
                                                {!! $notice->content !!}
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
                        <div class="panel panel-primary panel-line">
                            <div class="panel-heading">
                                <h1 class="panel-title"><i class="wb-pie-chart mr-10"></i>流量使用</h1>
                                <div class="panel-actions">
                                    <ul class="nav nav-pills" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#daily" aria-controls="daily" role="tab" aria-expanded="true" aria-selected="false">天</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#monthly" aria-controls="monthly" role="tab" aria-selected="true">月</a>
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
    <script src="/assets/custom/Plugin/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/aspieprogress/jquery-asPieProgress.js"></script>
    <script src="/assets/global/js/Plugin/aspieprogress.js"></script>
    <script src="/assets/global/vendor/chart-js/Chart.min.js"></script>

    @if(empty($info['t']))
        <script src="/assets/tour.js" type="text/javascript"></script>
    @endif
    <script type="text/javascript">
        // 签到
        function checkIn() {
            $.post('/checkIn', function (ret) {
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
                labels: [{!! $dayHours !!}],
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
                    data: [{!! $trafficHourly !!}],
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
                labels: [{!! $monthDays !!}],
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
                    data: [{!! $trafficDaily !!}],
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
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '{{trans('home.traffic_log_30days')}}'
                        }
                    }]
                }
            }
        });

        // 生成消息通道订阅二维码
        @if(\App\Components\Helpers::systemConfig()['push_bear_qrcode'])
        $('#subscribe_qrcode').qrcode({
            render: "canvas",
            text: "{{\App\Components\Helpers::systemConfig()['push_bear_qrcode']}}",
            width: 90,
            height: 90
        });
        @endif
    </script>
@endsection
