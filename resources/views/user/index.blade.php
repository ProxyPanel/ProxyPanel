@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/aspieprogress/asPieProgress.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row" data-plugin="matchHeight" data-by-row="true">
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')" class="col-md-12"/>
            @endif
            <div class="col-xxl-3 col-xl-4 col-lg-5 col-md-6 col-12">
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-pure">
                            <i class="wb-heart red-500"></i>
                        </button>
                        <span class="font-weight-400">{{trans('user.account.status')}}</span>
                        @if(sysConfig('is_checkin'))
                            <button class="btn btn-md btn-round btn-info float-right" onclick="checkIn()">
                                <i class="wb-star yellow-400 mr-5"></i>
                                {{trans('user.home.attendance.attribute')}}
                            </button>
                        @endif
                        <div class="content-text text-center mb-0">
                            @if(!$paying_user)
                                <p class="ml-15 mt-15 text-left">{{trans('user.more')}}<code>{{trans('validation.attributes.time')}}</code></p>
                                <p class="text-center">{{trans('user.more')}}<code>{{trans('user.attribute.data')}}</code></p>
                                <p class="mb-15 mr-15 text-right">{{trans('user.more')}}<code>{{trans('user.attribute.node')}}</code></p>
                                <a href="{{route('shop')}}" class="btn btn-block btn-danger">{{trans('user.purchase_promotion')}}</a>
                            @elseif(Auth::user()->enable)
                                <i class="wb-check green-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('user.status.enabled')}}</span>
                                <p class="font-weight-300 m-0 green-500">{{trans('user.account.reason.normal')}}</p>
                            @elseif($remainDays == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('user.status.expired')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('user.account.reason.expired')}}</p>
                            @elseif($unusedTraffic == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('user.status.disabled')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('user.account.reason.traffic_exhausted')}}</p>
                            @elseif(Auth::user()->isTrafficWarning() || $banedTime)
                                <i class="wb-alert orange-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('user.status.limited')}}</span>
                                <p class="font-weight-300 m-0 orange-500">{!!trans('user.account.reason.overused', ['data'=>sysConfig('traffic_ban_value')])!!}</p>
                            @else
                                <i class="wb-help red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('user.status.disabled')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('user.account.reason.unknown')}}</p>
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
                                <span class="font-weight-400">{{trans('user.account.remain')}}</span>
                                <div class="text-center font-weight-100 font-size-40">
                                    {{$unusedTraffic}}
                                    <br>
                                    <h4>{{trans('user.account.level')}}：<code class="font-size-20">{{Auth::user()->level}}</code></h4>
                                </div>
                                <div class="text-center font-weight-300 blue-grey-500 mb-10">
                                    @if($paying_user && $resetDays && $remainDays > $resetDays && sysConfig('reset_traffic'))
                                        {{trans('user.account.reset', ['days' => $resetDays])}}
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-12 col-sm-5">
                                <div class="w-only-xs-p50 w-only-sm-p75 w-only-md-p50" data-plugin="pieProgress"
                                     data-valuemax="100"
                                     data-barcolor="#96A3FA" data-size="100" data-barsize="10"
                                     data-goal="{{$unusedPercent}}" aria-valuenow="{{$unusedPercent}}"
                                     role="progressbar">
                                    <span class="pie-progress-number blue-grey-700 font-size-20">
                                        {{$unusedPercent}}%</span>
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
                        <span class="font-weight-400">{{trans('user.account.time')}}</span>
                        <div class="content-text text-center mb-0">
                            @if($remainDays !== -1)
                                <span class="font-size-40 font-weight-100">{{$remainDays.' '.trans('validation.attributes.day')}}</span>
                                <p class="blue-grey-500 font-weight-300 m-0">{{$expireTime}}</p>
                            @else
                                <span class="font-size-40 font-weight-100">{{trans('user.status.expired')}}</span>
                                <br/>
                                <a href="{{route('shop')}}" class="btn btn-danger">{{trans('user.shop.buy')}}</a>
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
                            <span class="font-weight-400 mb-10">{{trans('user.account.last_login')}}</span>
                            <ul class="list-group list-group-dividered px-20 mb-0">
                                <li class="list-group-item px-0">
                                    <i class="icon wb-time"></i>
                                    {{trans('validation.attributes.time')}}：{{date_format($userLoginLog->created_at,'Y/m/d H:i')}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-code"></i>
                                    {{trans('user.attribute.ip')}}：{{$userLoginLog->ip}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-cloud"></i>
                                    {{trans('user.attribute.isp')}}：{{$userLoginLog->isp}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-map"></i>
                                    {{trans('user.attribute.address')}}：{{$userLoginLog->area ?: $userLoginLog->country.' '.$userLoginLog->province.' '.$userLoginLog->city}}
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-xxl-9 col-xl-8 col-lg-7 col-md-6 col-12">
                <div class="row" data-plugin="matchHeight" data-by-row="true">
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow h-full">
                            <div class="card-block text-center">
                                <h3 class="card-header-transparent"><i class="icon wb-link-intact"></i>{{trans('user.subscribe.link')}}</h3>
                                @if($subscribe_status)
                                    <div class="card-body">
                                        @if(count($subType)>1)
                                            <div class="form-group row">
                                                <label class="col-md-auto col-form-label" for="subType">{{trans('common.customize')}}</label>
                                                <div class="col">
                                                    <select class="form-control" id="subType" name="subType" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                                        <option value="" hidden>{{trans('common.all')}}</option>
                                                        @if(in_array('ss',$subType))
                                                            <option value="1">{{trans('user.subscribe.ss_only')}}</option>
                                                        @endif
                                                        @if(in_array('v2',$subType))
                                                            <option value="2">{{trans('user.subscribe.v2ray_only')}}</option>
                                                        @endif
                                                        @if(in_array('trojan',$subType))
                                                            <option value="3">{{trans('user.subscribe.trojan_only')}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="form-group row">
                                            <label class="col-md-auto col-form-label" for="client">{{trans('user.client').trans('common.customize')}}</label>
                                            <div class="col">
                                                <select class="form-control" id="client" name="client" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                                    <option value="" hidden>{{trans('common.default')}}</option>
                                                    <option value="quantumult">Quantumult</option>
                                                    <option value="quantumult%20x">QuantumultX</option>
                                                    <option value="clash">Clash</option>
                                                    <option value="surfboard">Surfboard</option>
                                                    <option value="surge">Surge</option>
                                                    <option value="shadowrocket">Shadowrocket</option>
                                                    <option value="v2rayn">v2rayN</option>
                                                    {{--                                                    <option value="shadowsocks">SS路由器</option>--}}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer-transparent btn-group">
                                        <button class="btn btn-outline-danger" onclick="exchangeSubscribe();">
                                            <i class="icon wb-refresh" aria-hidden="true"></i>
                                            {{trans('common.replace')}}</button>
                                        <button class="btn btn-outline-info mt-clipboard" data-clipboard-action="copy">
                                            <i class="icon wb-copy" aria-hidden="true"></i>
                                            {{trans('common.copy.attribute')}}</button>
                                    </div>
                                @else
                                    <x-alert type="danger" :message="trans('user.subscribe.baned')"/>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow h-full">
                            <div class="card-block text-center">
                                <i class="font-size-40 wb-wrench"></i>
                                <h4 class="card-title">{{trans('user.client')}}</h4>
                                <p class="card-text">{{trans('common.download').' & '.trans('user.tutorials')}}</p>
                                <a href="{{route('help')}}#answer-2" class="btn btn-primary mb-10">{{trans('common.goto')}}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow text-center h-full">
                            <div class="card-block">
                                @if(sysConfig('is_push_bear') && sysConfig('push_bear_qrcode'))
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>{{trans('user.home.wechat_push')}}
                                    </h4>
                                    <div id="qrcode" class="mb-10"></div>
                                @else
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>{{trans('user.home.chat_group')}}</h4>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" data-plugin="matchHeight" data-by-row="true">
                    <div class="col-xxl-6 mb-30">
                        <div class="panel panel-info panel-line h-full">
                            <div class="panel-heading">
                                <h2 class="panel-title">
                                    <i class="wb-volume-high mr-10"></i>
                                    {{trans('user.home.announcement')}}
                                </h2>
                                <div class="panel-actions pagination-no-border pagination-sm">
                                    {{$announcements->links()}}
                                </div>
                            </div>
                            <div class="panel-body" data-show-on-hover="false" data-direction="vertical"
                                 data-skin="scrollable-shadow" data-plugin="scrollable">
                                <div data-role="container">
                                    <div class="pb-10" data-role="content">
                                        @forelse($announcements as $announcement)
                                            <h2 class="text-center">{!!$announcement->title!!}</h2>
                                            <p class="text-right"><small>{{trans('common.updated_at')}} <code>{{$announcement->updated_at}}</code></small></p>
                                            {!! $announcement->content !!}
                                        @empty
                                            <p class="text-center font-size-40">{{trans('user.home.empty_announcement')}}</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="panel panel-primary panel-line h-full">
                            <div class="panel-heading">
                                <h1 class="panel-title"><i class="wb-pie-chart mr-10"></i>{{trans('user.home.traffic_logs')}}
                                </h1>
                                <div class="panel-actions">
                                    <ul class="nav nav-pills" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#daily" aria-controls="daily" role="tab" aria-expanded="true"
                                               aria-selected="false">{{trans('validation.attributes.day')}}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#monthly" aria-controls="monthly" role="tab"
                                               aria-selected="true">{{trans('validation.attributes.month')}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <x-alert type="danger" :message="trans('user.traffic_logs.tips')"/>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="daily" role="tabpanel">
                                        <canvas id="dailyChart" aria-label="{{trans('user.traffic_logs.24hours')}}" role="img"></canvas>
                                    </div>
                                    <div class="tab-pane" id="monthly" role="tabpanel">
                                        <canvas id="monthlyChart" aria-label="{{trans('user.traffic_logs.30days')}}" role="img"></canvas>
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
@section('javascript')
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
    <script src="/assets/global/vendor/aspieprogress/jquery-asPieProgress.min.js"></script>
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js"></script>
    <script src="/assets/global/vendor/chart-js/Chart.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/aspieprogress.js"></script>
    <script src="/assets/global/js/Plugin/matchheight.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    @if(sysConfig('is_push_bear') && sysConfig('push_bear_qrcode'))
        <script src="/assets/custom/easy.qrcode.min.js"></script>
        <script>
            // Options
            const options = {
                text: @json(sysConfig('push_bear_qrcode')),
                width: 150,
                height: 150,
                backgroundImage: '{{asset('/assets/images/wechat.png')}}',
                autoColor: true,
            };

            // Create QRCode Object
            new QRCode(document.getElementById('qrcode'), options);
        </script>
    @endif
    <script>
        // 更换订阅地址
        function exchangeSubscribe() {
            swal.fire({
                title: '{{trans('common.warning')}}',
                text: '{{trans('user.subscribe.exchange_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('common.close')}}',
                confirmButtonText: '{{trans('common.confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post('{{route('changeSub')}}', {_token: '{{csrf_token()}}'}, function(ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                        } else {
                            swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                        }
                    });
                }
            });
        }

        const clipboard = new ClipboardJS('.mt-clipboard', {
            text: function(trigger) {
                let base = @json($subUrl);
                const client = $('#client').val();
                const subType = $('#subType').val();
                if (subType && client) {
                    base += '?target=' + client + '&type=' + subType;
                } else if (subType) {
                    base += '?type=' + subType;
                } else if (client) {
                    base += '?target=' + client;
                }
                return base;
            },
        });
        clipboard.on('success', function() {
            swal.fire({
                title: '{{trans('common.copy.success')}}',
                icon: 'success',
                timer: 1300,
                showConfirmButton: false,
            });
        });
        clipboard.on('error', function() {
            swal.fire({
                title: '{{trans('common.copy.failed')}}',
                icon: 'error',
                timer: 1500,
                showConfirmButton: false,
            });
        });

        // 签到
        function checkIn() {
            $.post('{{route('checkIn')}}', {_token: '{{csrf_token()}}'}, function(ret) {
                if (ret.status === 'success') {
                    swal.fire(ret.title, ret.message, 'success');
                } else {
                    swal.fire(ret.title, ret.message, 'error');
                }
            });
        }

        const dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {{$dayHours}},
                datasets: [
                    {
                        fill: true,
                        backgroundColor: 'rgba(98, 168, 234, .1)',
                        borderColor: Config.colors('primary', 600),
                        pointRadius: 4,
                        borderDashOffset: 2,
                        pointBorderColor: '#fff',
                        pointBackgroundColor: Config.colors('primary', 600),
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: Config.colors('primary', 600),
                        data: {{$trafficHourly}},
                    }],
            },
            options: {
                legend: {
                    display: false,
                },
                responsive: true,
                scales: {
                    xAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: '{{trans('validation.attributes.hour')}}',
                            },
                        }],
                    yAxes: [
                        {
                            display: true,
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(tick) {
                                    return tick.toString() + ' GB';
                                },
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '{{trans('user.traffic_logs.24hours')}}',
                            },
                        }],
                },
            },
        });

        const monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {{$monthDays}},
                datasets: [
                    {
                        fill: true,
                        backgroundColor: 'rgba(98, 168, 234, .1)',
                        borderColor: Config.colors('primary', 600),
                        pointRadius: 4,
                        borderDashOffset: 2,
                        pointBorderColor: '#fff',
                        pointBackgroundColor: Config.colors('primary', 600),
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: Config.colors('primary', 600),
                        data: {{$trafficDaily}},
                    }],
            },
            options: {
                legend: {
                    display: false,
                },
                responsive: true,
                scales: {
                    xAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: '{{trans('validation.attributes.day')}}',
                            },
                        }],
                    yAxes: [
                        {
                            display: true,
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(tick) {
                                    return tick.toString() + ' GB';
                                },
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '{{trans('user.traffic_logs.30days')}}',
                            },
                        }],
                },
            },
        });

        @if($banedTime)
        // 每秒更新计时器
        const countDownDate = new Date("{{$banedTime}}").getTime();
        const x = setInterval(function() {
            const distance = countDownDate - new Date().getTime();
            const hours = Math.floor(distance % 86400000 / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);
            document.getElementById('countdown').innerHTML = hours + '{{trans('validation.attributes.hour')}} ' + minutes
                + '{{trans('validation.attributes.minute')}} ' + seconds + '{{trans('validation.attributes.second')}}';
            if (distance <= 0) {
                clearInterval(x);
                document.getElementById('countdown').remove();
            }
        }, 1000);
        @endif
    </script>
@endsection
