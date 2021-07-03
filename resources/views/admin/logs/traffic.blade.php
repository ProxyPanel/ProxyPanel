@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">流量日志</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="user_id" value="{{Request::query('user_id')}}" placeholder="用户ID"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="用户账号"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="用户端口"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-8">
                        <select class="form-control" name="node_id" id="node_id" onchange="this.form.submit()">
                            <option value="" hidden>选择节点</option>
                            @foreach($nodes as $node)
                                <option value="{{$node->id}}">{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="start" value="{{Request::query('start')}}" autocomplete="off"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="end" value="{{Request::query('end')}}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.log.traffic')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户</th>
                        <th> 节点</th>
                        <th> 流量比例</th>
                        <th> 上传流量</th>
                        <th> 下载流量</th>
                        <th> 总流量</th>
                        <th> 记录时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dataFlowLogs as $log)
                        <tr>
                            <td> {{$log->id}} </td>
                            <td>
                                @if(empty($log->user))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id' => $log->user->id])}}" target="_blank"> {{$log->user->username}} </a>
                                    @else
                                        {{$log->user->username}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$log->node->name ?? '【节点已删除】'}} </td>
                            <td> {{$log->rate}} </td>
                            <td> {{$log->u}} </td>
                            <td> {{$log->d}} </td>
                            <td><span class="badge badge-danger"> {{$log->traffic}} </span></td>
                            <td> {{$log->log_time}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-6">
                        共 <code>{{$dataFlowLogs->total()}} 条记录</code>，合计 <code>{{$totalTraffic}}</code>
                    </div>
                    <div class="col-sm-6">
                        <nav class="Page navigation float-right">
                            {{$dataFlowLogs->links()}}
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

        $(document).ready(function() {
            $('#node_id').val({{Request::query('node_id')}});
        });
    </script>
@endsection
