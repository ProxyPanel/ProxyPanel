@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">用户封禁记录</h3>
            </div>
            <div class="panel-body">
                <div class="form-inline pb-20">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
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
                        <th> 用户名</th>
                        <th> 时长</th>
                        <th> 理由</th>
                        <th> 封禁时间</th>
                        <th> 最后连接时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($list->isEmpty())
                        <tr>
                            <td colspan="6">暂无数据</td>
                        </tr>
                    @else
                        @foreach($list as $vo)
                            <tr>
                                <td>
                                    <a href="{{url('admin/userList?username=' . $vo->id)}}" target="_blank" rel="noopener"> {{$vo->id}}</a>
                                </td>
                                <td> {{empty($vo->user) ? '【账号已删除】' : $vo->user->username}} </td>
                                <td> {{$vo->minutes}}分钟</td>
                                <td> {{$vo->desc}} </td>
                                <td> {{$vo->created_at}} </td>
                                <td> {{date("Y-m-d H:i:s", $vo->user->t)}} </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 {{$list->total()}} 条记录
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
            var username = $("#username").val();

            window.location.href = '/admin/userBanLogList?username=' + username;
        }

        // 重置
        function doReset() {
            window.location.href = '/admin/userBanLogList';
        }
    </script>
@endsection
