@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin')}}">工具箱</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/subscribeLog')}}">订阅请求日志</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-list font-dark"></i>
                            <span class="caption-subject bold uppercase">订阅请求日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="user_id" value="{{Request::get('user_id')}}" id="user_id" placeholder="用户ID" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="col-md-4 form-control input-sm" name="username" value="{{Request::get('username')}}" id="username" placeholder="用户名" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="do_search();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="do_reset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 用户 </th>
                                    <th> 唯一识别码 </th>
                                    <th> 请求次数 </th>
                                    <th> 最后请求时间 </th>
                                    <th> 异常 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($subscribeList->isEmpty())
                                        <tr>
                                            <td colspan="6">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($subscribeList as $subscribe)
                                            <tr class="odd gradeX">
                                                <td> {{$subscribe->id}} </td>
                                                <td> {{$subscribe->user->username}} </td>
                                                <td> {{$subscribe->code}} </td>
                                                <td> {{$subscribe->times}} </td>
                                                <td> {{$subscribe->updated_at}} </td>
                                                <td>
                                                    @if($subscribe->isWarning)
                                                        <div class="label label-danger">异常</div>
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
    <script type="text/javascript">
        // 搜索
        function do_search() {
            var user_id = $("#user_id").val();
            var username = $("#username").val();

            window.location.href = '{{url('admin/subscribeLog')}}' + '?user_id=' + user_id + '&username=' + username;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/subscribeLog')}}';
        }
    </script>
@endsection