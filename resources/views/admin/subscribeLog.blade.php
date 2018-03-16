@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase">订阅请求日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="用户ID" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){doSearch();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 用户 </th>
                                    <th> 唯一识别码 </th>
                                    <th> 请求次数 </th>
                                    <th> 最后请求时间 </th>
                                    <th> 封禁时间 </th>
                                    <th> 封禁理由 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($subscribeList->isEmpty())
                                        <tr>
                                            <td colspan="8">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($subscribeList as $subscribe)
                                            <tr class="odd gradeX">
                                                <td> {{$subscribe->id}} </td>
                                                <td> {{$subscribe->user->username}} </td>
                                                <td> {{$subscribe->code}} </td>
                                                <td> {{$subscribe->times}} </td>
                                                <td> {{$subscribe->updated_at}} </td>
                                                <td> {{$subscribe->ban_time > 0 ? date('Y-m-d H:i:s') : ''}} </td>
                                                <td> {{$subscribe->ban_desc}} </td>
                                                <td>
                                                    @if($subscribe->status == 0)
                                                        <button type="button" class="btn btn-sm green btn-outline" onclick="setStatus('{{$subscribe->id}}', 1)">启用</button>
                                                    @endif
                                                    @if($subscribe->status == 1)
                                                        <button type="button" class="btn btn-sm red btn-outline" onclick="setStatus('{{$subscribe->id}}', 0)">禁用</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$subscribeList->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $subscribeList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 搜索
        function doSearch() {
            var user_id = $("#user_id").val();
            var username = $("#username").val();

            window.location.href = '{{url('admin/subscribeLog')}}' + '?user_id=' + user_id + '&username=' + username;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('admin/subscribeLog')}}';
        }

        // 启用禁用用户的订阅
        function setStatus(id, status) {
            $.post("{{url('admin/setSubscribeStatus')}}", {_token:'{{csrf_token()}}', id:id, status:status}, function(ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    window.location.reload();
                });
            });
        }
    </script>
@endsection