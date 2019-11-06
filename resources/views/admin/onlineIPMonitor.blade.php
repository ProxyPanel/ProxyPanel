@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">在线IP监控
                    <small>实时</small>
                </h3>
            </div>
            <div class="panel-body">
                <div class="form-inline pb-20">
                    <div class="form-group">
                        <input type="text" class="form-control w-60" name="id" value="{{Request::get('id')}}" id="id" placeholder="ID">
                        <input type="text" class="form-control w-100" name="ip" value="{{Request::get('ip')}}" id="ip" placeholder="IP">
                        <input type="text" class="form-control w-150" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
                        <input type="text" class="form-control w-60" name="port" value="{{Request::get('port')}}" id="port" placeholder="端口">
                        <select name="nodeId" id="nodeId" class="form-control">
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
                        <th> 时间</th>
                        <th> 类型</th>
                        <th> 节点</th>
                        <th> 用户</th>
                        <th> IP</th>
                        <th> 归属地</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($list->isEmpty())
                        <tr>
                            <td colspan="7">暂无数据</td>
                        </tr>
                    @else
                        @foreach($list as $vo)
                            <tr>
                                <td>{{$vo->id}}</td>
                                <td>{{date("Y-m-d H:i", $vo->created_at)}}</td>
                                <td>{{$vo->type}}</td>
                                <td>{{$vo->node ? $vo->node->name : '【节点已删除】'}}</td>
                                <td>{{$vo->user ? $vo->user->username : '【用户已删除】'}}</td>
                                <td><a href="https://www.ipip.net/ip/{{$vo->ip}}.html" target="_blank">{{$vo->ip}}</a>
                                </td>
                                <td>{{$vo->ipInfo}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 {{$list->total()}} 个账号
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
    <script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 搜索
        function doSearch() {
            var id = $("#id").val();
            var ip = $("#ip").val();
            var username = $("#username").val();
            var port = $("#port").val();
            var nodeId = $("#nodeId option:selected").val();

            window.location.href = '/admin/onlineIPMonitor?id=' + id + '&ip=' + ip + '&username=' + username + '&port=' + port + '&nodeId=' + nodeId;
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/onlineIPMonitor';
        }
    </script>
@endsection