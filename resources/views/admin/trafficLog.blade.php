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
                <a href="{{url('admin/trafficLog')}}">流量日志</a>
                <i class="fa fa-circle"></i>
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
                            <i class="icon-speedometer font-dark"></i>
                            <span class="caption-subject bold uppercase"> 流量日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 用户 </th>
                                    <th> 节点 </th>
                                    <th> 流量比例 </th>
                                    <th> 上传流量 </th>
                                    <th> 下载流量 </th>
                                    <th> 总流量 </th>
                                    <th> 记录时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($trafficLogList->isEmpty())
                                        <tr>
                                            <td colspan="9">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($trafficLogList as $trafficLog)
                                            <tr class="odd gradeX">
                                                <td> {{$trafficLog->id}} </td>
                                                <td> <a href="{{url('admin/userList?port=') . $trafficLog->user->port}}" target="_blank"> <span class="label label-info"> {{$trafficLog->user->username}} </span> </a> </td>
                                                <td> {{$trafficLog->ssnode->name}} </td>
                                                <td> {{$trafficLog->rate}} </td>
                                                <td> {{$trafficLog->u}} </td>
                                                <td> {{$trafficLog->d}} </td>
                                                <td> <span class="label label-danger"> {{$trafficLog->traffic}} </span> </td>
                                                <td> {{$trafficLog->log_time}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$trafficLogList->total()}} 条记录</div>
                            </div>
                            <div class="col-md-7 col-sm-7">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $trafficLogList->links() }}
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
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 添加节点
        function addNode() {
            window.location.href = '{{url('admin/addNode')}}';
        }

        // 编辑节点
        function editNode(id) {
            window.location.href = '{{url('admin/editNode?id=')}}' + id;
        }

        // 删除节点
        function delNode(id) {
            var _token = '{{csrf_token()}}';

            bootbox.confirm({
                message: "确定删除节点？",
                buttons: {
                    confirm: {
                        label: '确定',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: '取消',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.post("{{url('admin/delNode')}}", {id:id, _token:_token}, function(ret){
                            if (ret.status == 'success') {
                                bootbox.alert(ret.message, function(){
                                    window.location.reload();
                                });
                            } else {
                                bootbox.alert(ret.message);
                            }
                        });
                    }
                }
            });
        }
    </script>
@endsection