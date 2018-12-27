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
                            <span class="caption-subject bold uppercase"> 标签列表 </span>
                            <small>标签影响用户查看/订阅节点信息（用户和节点通过标签进行关联）</small>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addLabel()"> 添加标签 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 名称 </th>
                                    <th> 关联用户数 </th>
                                    <th> 关联节点数 </th>
                                    <th> 排序 </th>
                                    <th style="text-align: center;"> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($labelList->isEmpty())
                                    <tr>
                                        <td colspan="6" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($labelList as $label)
                                        <tr class="odd gradeX">
                                            <td> {{$label->id}} </td>
                                            <td> {{$label->name}} </td>
                                            <td> {{$label->userCount}} </td>
                                            <td> {{$label->nodeCount}} </td>
                                            <td> {{$label->sort}} </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="editLabel('{{$label->id}}')"> 编辑 </button>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delLabel('{{$label->id}}')"> 删除 </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$labelList->total()}} 个标签</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $labelList->links() }}
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
        // 添加标签
        function addLabel() {
            window.location.href = '{{url('admin/addLabel')}}';
        }

        // 编辑标签
        function editLabel(id) {
            window.location.href = '{{url('admin/editLabel?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 删除标签
        function delLabel(id) {
            layer.confirm('确定删除标签？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delLabel')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
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