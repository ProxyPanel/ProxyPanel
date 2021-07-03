@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">在线IP监控
                    <small>2分钟内的实时数据</small>
                </h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-2">
                        <input type="number" class="form-control" name="id" value="{{Request::query('id')}}" placeholder="用户ID"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="用户账号"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input type="text" class="form-control" name="ip" value="{{Request::query('ip')}}" placeholder="IP"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-3">
                        <input type="number" class="form-control" name="port" value="{{Request::query('port')}}" placeholder="端口"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <select name="node_id" id="node_id" class="form-control" onchange="this.form.submit()">
                            <option value="" hidden>选择节点</option>
                            @foreach($nodes as $node)
                                <option value="{{$node->id}}">{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.log.online')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 节点</th>
                        <th> 用户</th>
                        <th> IP</th>
                        <th> 归属地</th>
                        <th> 时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($onlineIPLogs as $log)
                        <tr>
                            <td>{{$log->id}}</td>
                            <td>{{$log->type}}</td>
                            <td>{{$log->node->name ?? '【节点已删除】'}}</td>
                            <td>{{$log->user->username ?? '【用户已删除】'}}</td>
                            <td>
                                @if (strpos($log->ip, ',') !== false)
                                    @foreach (explode(',', $log->ip) as $ip)
                                        <a href="https://www.ipip.net/ip/{{$ip}}.html" target="_blank">{{$ip}}</a>
                                    @endforeach
                                @else
                                    <a href="https://www.ipip.net/ip/{{$log->ip}}.html" target="_blank">{{$log->ip}}</a>
                                @endif
                            </td>
                            <td>{{strpos($log->ip, ',') !== false? '':$log->ipInfo}}</td>
                            <td>{{date('Y-m-d H:i:s',$log->created_at)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$onlineIPLogs->total()}}</code> 个账号
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$onlineIPLogs->links()}}
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
    <script>
        $(document).ready(function() {
            $('#node_id').val({{Request::query('node_id')}});
        });
    </script>
@endsection
