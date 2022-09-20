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
                            <span class="ml-15 font-weight-400">总用户</span>
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
                    <a href="{{route('admin.user.index', ['enable'=>1])}}" class="card card-shadow">
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
                    <a href="{{route('admin.user.index', ['paying'=>1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-info">
                                <i class="icon md-money-box"></i>
                            </button>
                            <span class="ml-15 font-weight-400">付费用户</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$payingUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['active'=>1])}}" class="card card-shadow">
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
                    <a href="{{route('admin.user.index', ['unActive'=>1])}}" class="card card-shadow">
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
                    <a href="{{route('admin.user.index', ['online'=>1])}}" class="card card-shadow">
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
                    <a href="{{route('admin.user.index', ['expireWarning'=>1])}}" class="card card-shadow">
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
                    <a href="{{route('admin.user.index', ['largeTraffic'=>1])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-warning">
                                <i class="icon md-account"></i>
                            </button>
                            <span class="ml-15 font-weight-400">流量大户（超过90%的用户）</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$largeTrafficUserCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.user.index', ['flowAbnormal'=>1])}}" class="card card-shadow">
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
            @endcan
            @can('admin.node.index')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.node.index')}}" class="card card-shadow">
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
                    <a href="{{route('admin.node.index', ['status'=>0])}}" class="card card-shadow">
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
            @endcan
            @can('admin.log.traffic')
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.log.traffic')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-time-countdown"></i>
                            </button>
                            <span class="ml-15 font-weight-400">记录的消耗流量</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$totalFlowCount}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 info-panel">
                    <a href="{{route('admin.log.traffic')}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-primary">
                                <i class="icon md-time-countdown"></i>
                            </button>
                            <span class="ml-15 font-weight-400">30日内消耗流量</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-40 font-weight-100">{{$flowCount}}</span>
                                @if($todayFlowCount !== '0B')
                                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                                    <i class="icon wb-triangle-up" aria-hidden="true"></i> {{$todayFlowCount}}
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
                            <span class="ml-15 font-weight-400">总订单数</span>
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
                            <span class="ml-15 font-weight-400">在线支付订单数</span>
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
                    <a href="{{route('admin.order', ['status'=>[1, 2]])}}" class="card card-shadow">
                        <div class="card-block bg-white">
                            <button type="button" class="btn btn-floating btn-sm btn-success">
                                <i class="icon md-ticket-star"></i>
                            </button>
                            <span class="ml-15 font-weight-400">支付成功订单数</span>
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
                            <span class="ml-15 font-weight-400">总余额</span>
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
                            <span class="ml-15 font-weight-400">待提现佣金</span>
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
                            <span class="ml-15 font-weight-400">已支出佣金</span>
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
