@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">订阅列表</h1>
            </div>
            <div class="panel-body row">
                <form class="form-row col-12">
                    <div class="form-group col-xxl-1 col-lg-2 col-md-3">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="ID"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-6">
                        <input type="text" class="form-control" name="ip" value="{{Request::query('ip')}}" placeholder="IP"/>
                    </div>
                    <div class="form-group col-xxl-3 col-lg-5">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="start" value="{{Request::query('start')}}" placeholder="开始区间" autocomplete="off"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="end" value="{{Request::query('end')}}" placeholder="结束区间" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-2 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.subscribe.log', $subscribe->user->id)}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <div class="col-sm-12 col-xl-2">
                    <ul class="list-group list-group-gap">
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-user-circle" aria-hidden="true"></i> 用 户： <span class="float-right">{{ $subscribe->user->nickname ?? '用户已删除' }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-envelope" aria-hidden="true"></i> 账 号： <span class="float-right">{{ $subscribe->user->username ?? '用户已删除' }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-heart" aria-hidden="true"></i> 状 态： <span class="float-right">{!! $subscribe->status ? '<i class="green-600 icon wb-check" aria-hidden="true"></i>' : '<i
                                class="red-600 icon wb-close" aria-hidden="true"></i>' !!}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-bell" aria-hidden="true"></i> 请求次数： <code class="float-right">{{ $subscribe->times }}</code>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-time" aria-hidden="true"></i> 最后请求： <span class="float-right">{{ $subscribe->updated_at }}</span>
                        </li>
                        @if($subscribe->ban_time)
                            <li class="list-group-item bg-blue-grey-100">
                                <i class="icon wb-power" aria-hidden="true"></i>封禁截至： <span class="float-right">{{ date('Y-m-d H:i', $subscribe->ban_time )}}</span>
                            </li>
                            <li class="list-group-item bg-blue-grey-100">
                                <i class="icon wb-lock" aria-hidden="true"></i>封禁理由： <span class="float-right">{{ $subscribe->ban_desc }}</span>
                            </li>
                        @endif
                        @can('admin.subscribe.set')
                            <button class="list-group-item btn btn-block @if($subscribe->status) btn-danger @else btn-success @endif"
                                    onclick="setSubscribeStatus('{{route('admin.subscribe.set', $subscribe)}}')">
                                @if($subscribe->status == 0)
                                    <i class="icon wb-unlock" aria-hidden="true"></i> 启  用
                                @else
                                    <i class="icon wb-unlock" aria-hidden="true"></i> 禁  用
                                @endif
                            </button>
                        @endcan
                    </ul>
                </div>
                <div class="col-sm-12 col-xl-10">
                    <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                        <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> 请求IP</th>
                            <th> 归属地</th>
                            <th> 请求时间</th>
                            <th> 访问</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($subscribeLog as $subscribe)
                            <tr>
                                <td>{{$subscribe->id}}</td>
                                <td>
                                    @if ($subscribe->request_ip)
                                        <a href="https://www.ipip.net/ip/{{$subscribe->request_ip}}.html" target="_blank">{{$subscribe->request_ip}}</a>
                                    @endif
                                </td>
                                <td>{{$subscribe->ipInfo}}</td>
                                <td>{{$subscribe->request_time}}</td>
                                <td>{{trim($subscribe->request_header)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$subscribeLog->total()}}</code> 条记录
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$subscribeLog->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        $('.input-daterange').datepicker({
            format: 'yyyy-mm-dd',
        });

        @can('admin.subscribe.set')
        // 启用禁用用户的订阅
        function setSubscribeStatus(url) {
            $.post(url, {_token: '{{csrf_token()}}'}, function(ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => {
                        window.location.reload();
                    });
                } else {
                    swal.fire({title: ret.message, icon: 'error', timer: 1000, showConfirmButton: false}).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
        @endcan
    </script>
@endsection
