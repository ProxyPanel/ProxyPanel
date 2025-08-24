@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/aspieprogress/asPieProgress.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row" data-plugin="matchHeight" data-by-row="true">
            @if (Session::has('successMsg'))
                <x-alert class="col-md-12" :message="Session::pull('successMsg')" />
            @endif
            <div class="col-xxl-3 col-xl-4 col-lg-5 col-md-6 col-12">
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <button class="btn btn-floating btn-sm btn-pure" type="button">
                            <i class="wb-heart red-500" aria-hidden="true"></i>
                        </button>
                        <span class="font-weight-400">{{ trans('user.account.status') }}</span>
                        @if (sysConfig('checkin_interval'))
                            <button class="btn btn-md btn-round btn-info float-right" onclick="checkIn()">
                                <i class="wb-star yellow-400 mr-5" aria-hidden="true"></i>
                                {{ trans('user.home.attendance.attribute') }}
                            </button>
                        @endif
                        <div class="content-text text-center mb-0">
                            @if (!$paying_user)
                                <p class="ml-15 mt-15 text-left">{{ trans('common.more') }}
                                    <code>{{ ucfirst(trans('validation.attributes.time')) }}</code>
                                </p>
                                <p class="text-center">{{ trans('common.more') }}
                                    <code>{{ trans('user.attribute.data') }}</code>
                                </p>
                                <p class="mb-15 mr-15 text-right">{{ trans('common.more') }}
                                    <code>{{ trans('user.attribute.node') }}</code>
                                </p>
                                <a class="btn btn-block btn-danger" href="{{ route('shop.index') }}">{{ trans('user.purchase.promotion') }}</a>
                            @elseif(auth()->user()->enable)
                                <i class="wb-check green-400 font-size-40 mr-10" aria-hidden="true"></i>
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.normal') }}</span>
                                <p class="font-weight-300 m-0 green-500">{{ trans('user.account.reason.normal') }}</p>
                            @elseif($remainDays < 0)
                                <i class="wb-close red-400 font-size-40 mr-10" aria-hidden="true"></i>
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.expire') }}</span>
                                <p class="font-weight-300 m-0 red-500">{{ trans('user.account.reason.expired') }}</p>
                            @elseif($user['unused_traffic'] === 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.disabled') }}</span>
                                <p class="font-weight-300 m-0 red-500">{{ trans('user.account.reason.traffic_exhausted') }}</p>
                            @elseif($user['ban_time'])
                                <i class="wb-alert orange-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.limited') }}</span>
                                <p class="font-weight-300 m-0 orange-500">{!! trans('user.account.reason.overused', ['data' => sysConfig('traffic_abuse_limit')]) !!}</p>
                            @else
                                <i class="wb-help red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.disabled') }}</span>
                                <p class="font-weight-300 m-0 red-500">{{ trans('user.account.reason.unknown') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <div class="row">
                            <div class="col-lg-7 col-md-12 col-sm-7">
                                <button class="btn btn-floating btn-sm btn-pure" type="button">
                                    <i class="wb-stats-bars cyan-500"></i>
                                </button>
                                <span class="font-weight-400">{{ trans('user.account.remain') }}</span>
                                <div class="text-center font-weight-100 font-size-40">
                                    @if ($user['unused_traffic'] === 0)
                                        {{ trans('common.status.run_out') }}
                                    @else
                                        {{ formatBytes($user['unused_traffic']) }}
                                    @endif
                                    <br />
                                    <h4>{{ trans('user.account.level') }}:
                                        <code class="font-size-20">{{ Auth::user()->level }}</code>
                                    </h4>
                                </div>
                                <div class="text-center font-weight-300 blue-grey-500 mb-10">
                                    @if (isset($resetDays) && $resetDays >= 0)
                                        {!! trans_choice('user.account.reset', $resetDays, ['days' => $resetDays]) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-12 col-sm-5">
                                <div class="w-only-xs-p50 w-only-sm-p75 w-only-md-p50" data-plugin="pieProgress" data-valuemax="100" data-barcolor="#96A3FA"
                                     data-size="100" data-barsize="10" data-goal="{{ $unusedPercent }}" role="progressbar" aria-valuenow="{{ $unusedPercent }}">
                                    <span class="pie-progress-number blue-grey-700 font-size-20">{{ $unusedPercent }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <button class="btn btn-floating btn-sm btn-pure" type="button">
                            <i class="wb-calendar green-500"></i>
                        </button>
                        <span class="font-weight-400">{{ trans('user.account.time') }}</span>
                        <div class="content-text text-center mb-0">
                            @if ($remainDays >= 0)
                                <span class="font-size-40 font-weight-100">{{ $remainDays . ' ' . trans_choice('common.days.attribute', 1) }}</span>
                                <p class="blue-grey-500 font-weight-300 m-0">{{ $user['expiration_date'] }}</p>
                            @else
                                <span class="font-size-40 font-weight-100">{{ trans('common.status.expire') }}</span>
                                <br />
                                <a class="btn btn-danger" href="{{ route('shop.index') }}">{{ trans('user.shop.buy') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($userLoginLog)
                    <div class="card card-shadow">
                        <div class="card-block p-20">
                            <button class="btn btn-floating btn-sm btn-pure" type="button">
                                <i class="wb-globe purple-500"></i>
                            </button>
                            <span class="font-weight-400 mb-10">{{ trans('user.account.last_login') }}</span>
                            <ul class="list-group list-group-dividered px-20 mb-0">
                                <li class="list-group-item px-0">
                                    <i class="icon wb-time"></i>{{ ucfirst(trans('validation.attributes.time')) }}:
                                    {{ date_format($userLoginLog->created_at, 'Y/m/d H:i') }}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-code"></i>{{ trans('user.attribute.ip') }}: {{ $userLoginLog->ip }}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-cloud"></i>{{ trans('user.attribute.isp') }}: {{ $userLoginLog->isp }}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-map"></i>{{ trans('user.attribute.address') }}:
                                    {{ $userLoginLog->country . ' ' . $userLoginLog->province . ' ' . $userLoginLog->city . ' ' . $userLoginLog->area }}
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
                                <h3 class="card-header-transparent">
                                    <i class="icon wb-link-intact"></i>{{ trans('user.subscribe.link') }}
                                </h3>
                                @if ($subscribe['status'])
                                    <div class="card-body">
                                        @if (count($subType) > 1)
                                            <div class="form-group row">
                                                <label class="col-md-auto col-form-label" for="subType">{{ trans('common.customize') }}</label>
                                                <div class="col">
                                                    <select class="form-control" id="subType" name="subType" data-plugin="selectpicker"
                                                            data-style="btn-outline btn-primary" title="{{ trans('common.all') }}">
                                                        @if (in_array('ss', $subType, true))
                                                            <option value="0">{{ trans('user.subscribe.ss_only') }}</option>
                                                        @endif
                                                        @if (in_array('ssr', $subType, true))
                                                            <option value="1">{{ trans('user.subscribe.ssr_only') }}</option>
                                                        @endif
                                                        @if (in_array('v2', $subType, true))
                                                            <option value="2">{{ trans('user.subscribe.v2ray_only') }}</option>
                                                        @endif
                                                        @if (in_array('trojan', $subType, true))
                                                            <option value="3">{{ trans('user.subscribe.trojan_only') }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="form-group row">
                                            <label class="col-md-auto col-form-label" for="client">{{ trans('user.clients') }}</label>
                                            <div class="col">
                                                <select class="form-control" id="client" name="client" data-plugin="selectpicker"
                                                        data-style="btn-primary btn-outline" title="{{ trans('common.default') }}">
                                                    <option value="quantumult">Quantumult</option>
                                                    <option value="quantumult%20x">QuantumultX</option>
                                                    <option value="clash">Clash</option>
                                                    <option value="surfboard">Surfboard</option>
                                                    <option value="surge">Surge</option>
                                                    <option value="shadowrocket">Shadowrocket</option>
                                                    <option value="v2rayn">v2rayN</option>
                                                    {{-- <option value="shadowsocks">SS路由器</option> --}}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer-transparent btn-group">
                                        <button class="btn btn-outline-danger" onclick="exchangeSubscribe();">
                                            <i class="icon wb-refresh" aria-hidden="true"></i>
                                            {{ trans('common.change') }}</button>
                                        <button class="btn btn-outline-info mt-clipboard">
                                            <i class="icon wb-copy" aria-hidden="true"></i>
                                            {{ trans('common.copy.attribute') }}</button>
                                    </div>
                                @else
                                    <x-alert type="danger" :message="__($subscribe['ban_desc'])" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow h-full">
                            <div class="card-block text-center">
                                <i class="font-size-40 wb-wrench"></i>
                                <h4 class="card-title">{{ trans('user.clients') }}</h4>
                                <p class="card-text">{{ trans('common.download') . ' & ' . trans('user.tutorials') }}</p>
                                <a class="btn btn-primary mb-10" href="{{ route('knowledge.index') }}">{{ trans('common.goto') }}</a>
                            </div>
                        </div>
                    </div>
                    @if (config('common.contact.telegram') || config('common.contact.qq'))
                        <div class="col-xl-4 mb-30">
                            <div class="card card-shadow text-center h-full">
                                <div class="card-block">
                                    <h4 class="card-title">
                                        <i class="wb-bell mr-10 yellow-600"></i>{{ trans('user.home.chat_group') }}
                                    </h4>
                                    @if ($paying_user)
                                        <div class="btn-group">
                                            @if (config('common.contact.qq'))
                                                <a class="card-link btn btn-sm btn-pill-left btn-info" href="{{ config('common.contact.qq') }}" target="_blank"
                                                   rel="noopener">
                                                    <i class="fa-brands fa-qq"></i> QQ{{ trans('user.home.chat_group') }}
                                                </a>
                                            @endif
                                            @if (config('common.contact.telegram'))
                                                <a class="card-link btn btn-sm btn-pill-right btn-success" href="{{ config('common.contact.telegram') }}"
                                                   target="_blank" rel="noopener">
                                                    TG{{ trans('user.home.chat_group') }}
                                                    <i class="fa-brands fa-telegram"></i></a>
                                            @endif
                                        </div>
                                    @else
                                        <p class="card-link btn btn-sm btn-primary">
                                            <i class="wb-lock mr-5"></i>{{ trans('user.purchase.to_unlock') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row" data-plugin="matchHeight" data-by-row="true">
                    <div class="col-xxl-6 mb-30">
                        <div class="panel panel-info panel-line h-full">
                            <div class="panel-heading">
                                <h2 class="panel-title">
                                    <i class="wb-volume-high mr-10"></i>
                                    {{ trans('user.home.announcement') }}
                                </h2>
                                <div class="panel-actions pagination-no-border pagination-sm">
                                    {{ $announcements->links() }}
                                </div>
                            </div>
                            <div class="panel-body" data-show-on-hover="false" data-direction="vertical" data-skin="scrollable-shadow"
                                 data-plugin="scrollable">
                                <div data-role="container">
                                    <div class="pb-10" data-role="content">
                                        @forelse($announcements as $announcement)
                                            <h2 class="text-center">{!! $announcement->title !!}</h2>
                                            <p class="text-right"><small>{{ trans('common.updated_at') }}
                                                    <code>{{ $announcement->updated_at }}</code></small></p>
                                            {!! $announcement->content !!}
                                        @empty
                                            <p class="text-center font-size-40">{{ trans('user.home.empty_announcement') }}</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="panel panel-primary panel-line h-full">
                            <div class="panel-heading">
                                <h1 class="panel-title">
                                    <i class="wb-pie-chart mr-10"></i>{{ trans('user.home.traffic_logs') }}
                                </h1>
                                <div class="panel-actions">
                                    <ul class="nav nav-pills" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#daily" role="tab" aria-controls="daily"
                                               aria-expanded="true" aria-selected="false">{{ trans_choice('common.days.attribute', 1) }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#monthly" role="tab" aria-controls="monthly"
                                               aria-selected="true">{{ ucfirst(trans('validation.attributes.month')) }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <x-alert type="danger" :message="trans('user.traffic_logs.tips')" />
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="daily" role="tabpanel">
                                        <canvas id="dailyChart" role="img" aria-label="{{ trans('user.traffic_logs.hourly') }}"></canvas>
                                    </div>
                                    <div class="tab-pane" id="monthly" role="tabpanel">
                                        <canvas id="monthlyChart" role="img" aria-label="{{ trans('user.traffic_logs.daily') }}"></canvas>
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
    <script src="/assets/global/vendor/aspieprogress/jquery-asPieProgress.min.js"></script>
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js"></script>
    <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/aspieprogress.js"></script>
    <script src="/assets/global/js/Plugin/matchheight.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        function exchangeSubscribe() { // 更换订阅地址
            showConfirm({
                title: '{{ trans('common.warning') }}',
                html: `{!! trans('user.subscribe.exchange_warning') !!}`,
                icon: "warning",
                onConfirm: function() {
                    ajaxPost('{{ route('changeSub') }}');
                }
            });
        }

        $(".mt-clipboard").on("click", function() {
            let base = @json($user['sub_url']);
            const client = $("#client").val();
            const subType = $("#subType").val();
            const params = [];

            if (client) {
                params.push("target=" + client);
            }

            if (subType) {
                params.push("type=" + subType);
            }

            if (params.length > 0) {
                base += "?" + params.join("&");
            }

            copyToClipboard(base);
        });

        @if (sysConfig('checkin_interval'))
            function checkIn() { // 签到
                ajaxPost('{{ route('checkIn') }}');
            }
        @endif

        function common_options(tail) {
            return {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                return this.getLabelForValue(value) + " " + tail;
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            callback: function(value) {
                                return this.getLabelForValue(value) + " GB";
                            }
                        },
                        grid: {
                            display: false
                        },
                        min: 0
                    }
                },
                plugins: {
                    legend: false,
                    tooltip: {
                        mode: "index",
                        intersect: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label + " " + tail;
                            },
                            label: function(context) {
                                return context.parsed.y + " GB";
                            }
                        }
                    }
                }
            };
        }

        function datasets(label, data) {
            return {
                labels: label,
                datasets: [{
                    backgroundColor: "rgba(184, 215, 255)",
                    borderColor: "rgba(184, 215, 255)",
                    data: data,
                    tension: 0.4
                }]
            };
        }

        new Chart(document.getElementById("dailyChart"), {
            type: "line",
            data: datasets(@json($dayHours), @json($trafficHourly)),
            options: common_options(@json(trans_choice('common.hour', 2)))
        });

        new Chart(document.getElementById("monthlyChart"), {
            type: "line",
            data: datasets(@json($monthDays), @json($trafficDaily)),
            options: common_options(@json(trans_choice('common.days.attribute', 2)))
        });

        @if ($user['ban_time']) // 封禁倒计时
            const banedTime = new Date("{{ $user['ban_time'] }}").getTime();
            countDown(banedTime, "banedTime", true);
            setInterval(function() {
                countDown(banedTime, "banedTime", true);
            }, 1000);
        @endif

        @if (isset($resetDays) && $resetDays === 0) // 重置日倒计时
            const resetTime = new Date("{{ date('Y-m-d 00:00', strtotime('tomorrow')) }}").getTime();
            countDown(resetTime, "restTime");
            setInterval(function() {
                countDown(resetTime, "restTime");
            }, 60000);
        @endif

        function countDown(endTime, id, seconds = false) { // 计时器主题逻辑
            const distance = endTime - new Date().getTime();
            const hour = Math.floor(distance % 86400000 / 3600000);
            const minute = Math.floor((distance % 3600000) / 60000);
            let string = "";
            if (hour) {
                string += hour + " " + @json(trans_choice('common.hour', 1));
            }
            if (minute) {
                string += " " + minute + " " + @json(ucfirst(trans('validation.attributes.minute')));
            }
            if (seconds) {
                string += " " + Math.floor((distance % 60000) / 1000) + " " + @json(ucfirst(trans('validation.attributes.second')));
            }
            document.getElementById(id).innerHTML = string;

            if (distance <= 0 && distance.abs > 1000) {
                window.location.reload();
            }
        }
    </script>
@endsection
