@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/font-awesome/font-awesome.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/aspieprogress/asPieProgress.min.css" type="text/css" rel="stylesheet">
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
                        <span class="font-weight-400">{{trans('home.account_status')}}</span>
                        @if(sysConfig('is_checkin'))
                            <button class="btn btn-md btn-round btn-info float-right" onclick="checkIn()">
                                <i class="wb-star yellow-400 mr-5"></i>
                                {{trans('home.sign_in')}}
                            </button>
                        @endif
                        <div class="content-text text-center mb-0">
                            @if(!$paying_user)
                                <p class="ml-15 mt-15 text-left">更长使用<code>时间</code></p>
                                <p class="text-center">更多<code>流量</code></p>
                                <p class="mb-15 mr-15 text-right">更多优质<code>线路</code></p>
                                <a href="{{route('shop')}}" class="btn btn-block btn-danger">快 来 购 买 服 务 吧！</a>
                            @elseif(Auth::user()->enable)
                                <i class="wb-check green-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.enabled')}}</span>
                                <p class="font-weight-300 m-0 green-500">{{trans('home.normal')}}</p>
                            @elseif($remainDays == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('home.reason_expired')}}</p>
                            @elseif($unusedTraffic == 0)
                                <i class="wb-close red-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.disabled')}}</span>
                                <p class="font-weight-300 m-0 red-500">{{trans('home.reason_traffic_exhausted')}}</p>
                            @elseif(Auth::user()->isTrafficWarning() || $banedTime)
                                <i class="wb-alert orange-400 font-size-40 mr-10"></i>
                                <span class="font-size-40 font-weight-100">{{trans('home.limited')}}</span>
                                <p class="font-weight-300 m-0 orange-500">{!!trans('home.reason_overused', ['data'=>sysConfig('traffic_ban_value')])!!}</p>
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
                                    {{$unusedTraffic}}
                                    <br>
                                    <h4>账号等级：<code class="font-size-20">{{Auth::user()->level}}</code></h4>
                                </div>
                                <div class="text-center font-weight-300 blue-grey-500 mb-10">
                                    @if($paying_user && sysConfig('reset_traffic') && $resetDays && $remainDays > $resetDays)
                                        {{trans('home.account_reset_notice', ['reset_day' => $resetDays])}}
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
                        <span class="font-weight-400">{{trans('home.account_expire')}}</span>
                        <div class="content-text text-center mb-0">
                            @if($remainDays !== -1)
                                <span class="font-size-40 font-weight-100">{{$remainDays}} {{trans('home.day')}}</span>
                                <p class="blue-grey-500 font-weight-300 m-0">{{$expireTime}}</p>
                            @else
                                <span class="font-size-40 font-weight-100">{{trans('home.expired')}}</span>
                                <br/>
                                <a href="{{route('shop')}}" class="btn btn-danger">{{trans('home.service_buy_button')}}</a>
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
                                    <i class="icon wb-time"></i>
                                    时间：{{date_format($userLoginLog->created_at,'Y/m/d H:i')}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-code"></i>
                                    IP地址：{{$userLoginLog->ip}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-cloud"></i>
                                    运营商：{{$userLoginLog->isp}}
                                </li>
                                <li class="list-group-item px-0">
                                    <i class="icon wb-map"></i>
                                    地区：{{$userLoginLog->area ?: $userLoginLog->country.' '.$userLoginLog->province.' '.$userLoginLog->city}}
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
                                <h3 class="card-header-transparent"><i class="icon wb-link-intact"></i> 订 阅 链 接 </h3>
                                @if($subscribe_status)
                                    <div class="card-body">
                                        @if(count($subType)>1)
                                            <div class="form-group row">
                                                <label class="col-md-auto col-form-label" for="subType">自定义订阅</label>
                                                <div class="col">
                                                    <select class="form-control" id="subType" name="subType" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                                        <option value="" hidden>全 部</option>
                                                        @if(in_array('ss',$subType))
                                                            <option value="1">只订阅SS/SSR</option>
                                                        @endif
                                                        @if(in_array('v2',$subType))
                                                            <option value="2">只订阅V2Ray</option>
                                                        @endif
                                                        @if(in_array('trojan',$subType))
                                                            <option value="3">只订阅Trojan</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="form-group row">
                                            <label class="col-md-auto col-form-label" for="client">客户端 DIY</label>
                                            <div class="col">
                                                <select class="form-control" id="client" name="client" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                                    <option value="" hidden>默 认</option>
                                                    <option value="quantumult">圈 - Quantumult</option>
                                                    <option value="quantumult x">圈X - QuantumultX</option>
                                                    <option value="clash">Clash</option>
                                                    <option value="surfboard">Surfboard</option>
                                                    <option value="surge">Surge</option>
                                                    <option value="shadowrocket">小火箭 - Shadowrocket</option>
                                                    <option value="shadowsocks">SS路由器</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer-transparent btn-group">
                                        <button class="btn btn-outline-danger" onclick="exchangeSubscribe();">
                                            <i class="icon wb-refresh" aria-hidden="true"></i>
                                            {{trans('home.exchange_subscribe')}}</button>
                                        <button class="btn btn-outline-info mt-clipboard" data-clipboard-action="copy">
                                            <i class="icon wb-copy" aria-hidden="true"></i>
                                            {{trans('home.copy_subscribe_address')}}</button>
                                    </div>
                                @else
                                    <x-alert type="danger" :message="trans('home.subscribe_baned')"/>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow h-full">
                            <div class="card-block text-center">
                                <i class="font-size-40 wb-wrench"></i>
                                <h4 class="card-title">客户端</h4>
                                <p class="card-text">下载 & 教程 </p>
                                <a href="{{route('help')}}#answer-2" class="btn btn-primary mb-10">前往</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <div class="card card-shadow text-center h-full">
                            <div class="card-block">
                                @if(sysConfig('is_push_bear') && sysConfig('push_bear_qrcode'))
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>微信公告推送</h4>
                                    <div id="qrcode" class="mb-10"></div>
                                @else
                                    <h4 class="card-title"><i class="wb-bell mr-10 yellow-600"></i>交流群</h4>
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
                                    {{trans('home.announcement')}}
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
                                            <p class="text-right"><small>更新于 <code>{{$announcement->updated_at}}</code></small></p>
                                            {!! $announcement->content !!}
                                        @empty
                                            <p class="text-center font-size-40">暂无公告</p>
                                        @endforelse
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
                                            <a class="nav-link active" data-toggle="tab" href="#daily" aria-controls="daily" role="tab" aria-expanded="true"
                                               aria-selected="false">天</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#monthly" aria-controls="monthly" role="tab" aria-selected="true">月</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <x-alert type="danger" :message="trans('home.traffic_log_tips')"/>
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
@section('javascript')
    <script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/aspieprogress/jquery-asPieProgress.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/chart-js/Chart.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/aspieprogress.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/matchheight.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
    @if(sysConfig('is_push_bear') && sysConfig('push_bear_qrcode'))
        <script src="/assets/custom/easy.qrcode.min.js"></script>
        <script type="text/javascript">
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
    <script type="text/javascript">
      // 更换订阅地址
      function exchangeSubscribe() {
        swal.fire({
          title: '警告',
          text: '更换订阅地址将导致:\n1.旧地址立即失效\n2.连接密码被更改',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
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
          title: '复制成功',
          icon: 'success',
          timer: 1300,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '复制失败，请手动复制',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });

      // 签到
      function checkIn() {
        $.post('{{route('checkIn')}}', {_token: '{{csrf_token()}}'}, function(ret) {
          if (ret.status === 'success') {
            swal.fire('长者的微笑', ret.message, 'success');
          } else {
            swal.fire({
              title: ret.message,
              icon: 'error',
            });
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
                  labelString: '小时',
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
                  labelString: '{{trans('home.traffic_log_24hours')}}',
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
                  labelString: '天',
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
                  labelString: '{{trans('home.traffic_log_30days')}}',
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
        document.getElementById('countdown').innerHTML = hours + '时 ' + minutes + '分 ' + seconds + '秒';
        if (distance <= 0) {
          clearInterval(x);
          document.getElementById('countdown').remove();
        }
      }, 1000);
        @endif
    </script>
@endsection
