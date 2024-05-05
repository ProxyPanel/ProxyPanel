@extends('admin.layouts')
@section('css')
    <link href="/assets/global/fonts/material-design/material-design.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row" data-by-row="true">
            @can('admin.user.index')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalUserCount}}</span>
                                @if ($todayRegister)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todayRegister}}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['enable' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-info">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.available_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$enableUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['paying' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-info">
                                <i class="icon md-money-box"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.paid_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$payingUserCount}}</span>
                                @if ($payingNewUserCount)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                        <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$payingNewUserCount}}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['active' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-success">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.active_days_users', ['days' => sysConfig('expire_days')]) }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$activeUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['unActive' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-warning">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.inactive_days_users', ['days' => sysConfig('expire_days')]) }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$inactiveUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['online' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-success">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.online_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$onlineUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['expireWarning' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-danger">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.expiring_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$expireWarningUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['largeTraffic' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-warning">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.overuse_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$largeTrafficUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['flowAbnormal' => 1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-danger">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.abnormal_users') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$flowAbnormalUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('admin.node.index')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.node.index')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-cloud"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.nodes') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$nodeCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.node.index', ['status'=>0])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-info">
                                <i class="icon md-cloud-off"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.maintaining_nodes') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$abnormalNodeCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('admin.log.traffic')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.log.traffic')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-time-countdown"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.days_traffic_consumed', ['days' => now()->diffInDays((new DateTime())->modify(config('tasks.clean.node_daily_logs')))]) }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{ $totalTrafficUsage }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{ route('admin.log.traffic') }}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-time-countdown"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.current_month_traffic_consumed') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{ $monthlyTrafficUsage }}</span>
                                @if( $dailyTrafficUsage !== 0 )
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{ $dailyTrafficUsage }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('admin.order')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.order')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-ticket-star"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.orders') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalOrder}}</span>
                                @if($todayOrder)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todayOrder}}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.order')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-info">
                                <i class="icon md-ticket-star"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.online_orders') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalOnlinePayOrder}}</span>
                                @if($todayOnlinePayOrder)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todayOnlinePayOrder}}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.order', ['status' => [1, 2]])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-success">
                                <i class="icon md-ticket-star"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.succeed_orders') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalSuccessOrder}}</span>
                                @if($todaySuccessOrder)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todaySuccessOrder}}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('admin.log.credit')
                <div class="col-xl-3 col-md-6 info-panel">
                    <div class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-money"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.credit') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalCredit}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
            @can('admin.aff.rebate')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.aff.rebate')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-warning">
                                <i class="icon md-money"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.withdrawing_commissions') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalWaitRefAmount}}</span>
                                @if($todayWaitRefAmount)
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todayWaitRefAmount}}
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('admin.aff.index')
                <div class="col-xl-3 col-md-6 info-panel">
                    <div class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-dark">
                                <i class="icon md-money"></i>
                            </button>
                            <span class="ml-15 font-weight-400">{{ trans('admin.dashboard.withdrawn_commissions') }}</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalRefAmount}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js"></script>
    <script src="/assets/global/js/Plugin/matchheight.js"></script>
    <script>
      $(function() {
        $('.card').matchHeight();
      });
    </script>
@endsection
