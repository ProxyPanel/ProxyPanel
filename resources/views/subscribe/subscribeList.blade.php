@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/theme/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">订阅列表</h3>
            </div>
            <div class="panel-body">
                <div class="form-inline pb-20">
                    <div class="form-group">
                        <input type="text" class="form-control w-60" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="ID">
                        <input type="text" class="form-control w-150" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名">
                        <select name="status" id="status" class="form-control">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>账号状态</option>
                            <option value="-1" @if(Request::get('status') == '-1') selected hidden @endif>禁用</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>未激活</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>正常</option>
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
                        <th> 订阅码</th>
                        <th> 请求次数</th>
                        <th> 最后请求时间</th>
                        <th> 封禁时间</th>
                        <th> 封禁理由</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($subscribeList->isEmpty())
                        <tr>
                            <td colspan="8">暂无数据</td>
                        </tr>
                    @else
                        @foreach($subscribeList as $subscribe)
                            <tr>
                                <td> {{$subscribe->id}} </td>
                                <td>
                                    @if(empty($subscribe->user))
                                        【账号已删除】
                                    @else
                                        <a href="/admin/userList?id={{$subscribe->user->id}}" target="_blank">{{$subscribe->user->username}}</a>
                                    @endif
                                </td>
                                <td> {{$subscribe->code}} </td>
                                <td>
                                    <a href="/admin/userSubscribeLog?user_id={{$subscribe->user->id}}" target="_blank">{{$subscribe->times}}</a>
                                </td>
                                <td> {{$subscribe->updated_at}} </td>
                                <td> {{$subscribe->ban_time > 0 ? date('Y-m-d H:i', $subscribe->ban_time): ''}} </td>
                                <td> {{$subscribe->ban_desc}} </td>
                                <td>
                                    @if($subscribe->status == 0)
                                        <button class="btn btn-sm btn-outline-success" onclick="setSubscribeStatus('{{$subscribe->id}}', 1)">启用</button>
                                    @endif
                                    @if($subscribe->status == 1)
                                        <button class="btn btn-sm btn-outline-danger" onclick="setSubscribeStatus('{{$subscribe->id}}', 0)">禁用</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4 col-sm-4">
                        共 <code>{{$subscribeList->total()}}</code> 条记录
                    </div>
                    <div class="col-md-8 col-sm-8">
                            <nav class="Page navigation float-right">{{ $subscribeList->links() }}</nav>
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
            var user_id = $("#user_id").val();
            var username = $("#username").val();
            var status = $("#status option:checked").val();

            window.location.href = '/subscribe/subscribeList' + '?user_id=' + user_id + '&username=' + username + '&status=' + status;
        }

        // 重置
        function doReset() {
            window.location.href = '/subscribe/subscribeList';
        }

        // 启用禁用用户的订阅
        function setSubscribeStatus(id, status) {
            $.post("/subscribe/setSubscribeStatus", {
                _token: '{{csrf_token()}}',
                id: id,
                status: status
            }, function (ret) {
                swal.fire({title: ret.message, timer: 1000, showConfirmButton: false,})
                    .then(() => {
                        window.location.reload();
                    })

            });
        }
    </script>
@endsection