@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
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
                            <span class="caption-subject bold uppercase"> 节点分组 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addGroup()"> 添加分组 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 分组名称 </th>
                                    <th> 分组级别 </th>
                                    <th style="text-align: center;"> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($groupList->isEmpty())
                                        <tr>
                                            <td colspan="3" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($groupList as $group)
                                            <tr class="odd gradeX">
                                                <td> {{$group->id}} </td>
                                                <td> {{$group->name}} </td>
                                                <td> {{$levelMap[$group->level]}} </td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="btn btn-sm blue btn-outline" onclick="editGroup('{{$group->id}}')"> 编辑 </button>
                                                    <button type="button" class="btn btn-sm red btn-outline" onclick="delGroup('{{$group->id}}')"> 删除 </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$groupList->total()}} 个节点分组</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{$groupList->links()}}
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
        // 添加节点分组
        function addGroup() {
            window.location.href = '{{url('admin/addGroup')}}';
        }

        // 编辑节点分组
        function editGroup(id) {
            window.location.href = '{{url('admin/editGroup?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 删除节点分组
        function delGroup(id) {
            layer.confirm('确定删除分组？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delGroup')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }
    </script>
@endsection