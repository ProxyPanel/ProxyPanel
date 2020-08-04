@extends('admin.layouts')

@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">流量日志</h2>
            </div>
            <div class="panel-body">
                <div class="form-inline pb-20">
                    <div class="form-group">
                        <input type="text" class="form-control" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="用户ID">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口">
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="nodeId" id="nodeId">
                            <option value="" @if(Request::get('nodeId') == '') selected @endif>选择节点</option>
                            @foreach($nodeList as $node)
                                <option value="{{$node->id}}" @if(Request::get('nodeId') == $node->id) selected @endif>{{$node->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="doSearch()">搜索</button>
                        <button class="btn btn-danger" onclick="doReset()">重置</button>
                    </div>
                </div>
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
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
                    @if($list->isEmpty())
                        <tr>
                            <td colspan="8">暂无数据</td>
                        </tr>
                    @else
                        @foreach($list as $vo)
                            <tr>
                                <td> {{$vo->id}} </td>
                                <td>
                                    @if(empty($vo->user))
                                        【账号已删除】
                                    @else
                                        <a href="/admin/userList?id={{$vo->user->id}}" target="_blank"> {{$vo->user->username}} </a>
                                    @endif
                                </td>
                                <td> {{$vo->node ? $vo->node->name : '【节点已删除】'}} </td>
                                <td> {{$vo->rate}} </td>
                                <td> {{$vo->u}} </td>
                                <td> {{$vo->d}} </td>
                                <td><span class="badge badge-danger"> {{$vo->traffic}} </span></td>
                                <td> {{$vo->log_time}} </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 {{$list->total()}} 条记录，合计 {{$totalTraffic}}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $list->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script type="text/javascript">
        // 搜索
        function doSearch() {
            var port = $("#port").val();
            var user_id = $("#user_id").val();
            var username = $("#username").val();
            var nodeId = $("#nodeId option:selected").val();

            window.location.href = '/admin/trafficLog' + '?port=' + port + '&user_id=' + user_id + '&username=' + username + '&nodeId=' + nodeId;
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/trafficLog';
        }
    </script>
@endsection