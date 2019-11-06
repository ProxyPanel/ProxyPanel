@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">工单列表</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary btn-animate btn-animate-side" onclick="addTicket()">
                        <span><i class="icon wb-plus" aria-hidden="true"></i> {{trans('home.ticket_table_new_button')}}</span>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-inline mb-20">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" autocomplete="off" onkeydown="if(event.keyCode==13){do_search();}">
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
                        <th> 标题</th>
                        <th> 状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($ticketList->isEmpty())
                        <tr>
                            <td colspan="4">暂无数据</td>
                        </tr>
                    @else
                        @foreach($ticketList as $ticket)
                            <tr>
                                <td> {{$ticket->id}} </td>
                                <td>
                                    @if(!$ticket->user)
                                        【账号已删除】
                                    @else
                                        <a href="/admin/userList?id={{$ticket->user->id}}" target="_blank">{{$ticket->user->username}}</a>
                                </td>
                                @endif
                                <td>
                                    <a href="/admin/userList?id={{$ticket->user->id}}" target="_blank">{{$ticket->title}}</a>
                                </td>
                                <td style="text-align: center;">
                                    @if ($ticket->status == 0)
                                        <span class="badge badge-lg badge-info"> 待处理 </span>
                                    @elseif ($ticket->status == 1)
                                        <span class="badge badge-lg badge-success"> 已回复 </span>
                                    @else
                                        <span class="badge badge-lg badge-default"> 已关闭 </span>
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
                        共 <code>{{$ticketList->total()}}</code> 个工单
                    </div>
                    <div class="col-md-8 col-sm-8">
                        <nav class="Page navigation float-right">{{ $ticketList->links() }}</nav>
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
        // 发起工单
        function addTicket() {
            window.location.href = '/ticket/addTicket';
        }

        // 回复工单
        function reply(id) {
            window.location.href = '/ticket/replyTicket?id=' + id;
        }

        // 搜索
        function doSearch() {
            const username = $("#username").val();
            window.location.href = '/ticket/ticketList?username=' + username;
        }

        // 重置
        function doReset() {
            window.location.href = '/ticket/ticketList';
        }
    </script>
@endsection
