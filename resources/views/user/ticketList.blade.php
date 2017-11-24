@extends('user.layouts')

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
                            <i class="icon-question font-dark"></i>
                            <span class="caption-subject bold uppercase"> 工单列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" data-toggle="modal" data-target="#charge_modal"> 发起工单 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 标题 </th>
                                    <th> 状态 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($ticketList->isEmpty())
                                    <tr>
                                        <td colspan="4">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($ticketList as $key => $ticket)
                                        <tr class="odd gradeX">
                                            <td> {{$key + 1}} </td>
                                            <td> <a href="{{url('user/replyTicket?id=') . $ticket->id}}" target="_blank">{{$ticket->title}}</a> </td>
                                            <td>
                                                @if ($ticket->status == 0)
                                                    <span class="label label-info"> 待处理 </span>
                                                @elseif ($ticket->status == 1)
                                                    <span class="label label-danger"> 已回复 </span>
                                                @else
                                                    <span class="label label-default"> 已关闭 </span>
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
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$ticketList->total()}} 个工单</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $ticketList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <div id="charge_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"> 发起工单 </h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="title" id="title" placeholder="工单标题" class="form-control margin-bottom-20">
                        <textarea name="content" id="content" placeholder="请填写您的问题，如果图片请提交工单后在回复处上传图片" class="form-control margin-bottom-20" rows="4"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn red btn-outline">关闭</button>
                        <button type="button" data-dismiss="modal" class="btn green btn-outline" onclick="addTicket()">提交</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 回复工单
        function reply(id) {
            window.location.href =  + id;
        }

        // 发起工单
        function addTicket() {
            var title = $("#title").val();
            var content = $("#content").val();

            if (title == '' || title == undefined) {
                bootbox.alert('工单标题不能为空');
                return false;
            }

            if (content == '' || content == undefined) {
                bootbox.alert('工单内容不能为空');
                return false;
            }

            layer.confirm('确定提交工单？', {icon: 3, title:'警告'}, function(index) {
                $.post("{{url('user/addTicket')}}", {_token:'{{csrf_token()}}', title:title, content:content}, function(ret) {
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