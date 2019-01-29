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
                            <span class="caption-subject bold uppercase"> 工单列表 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th style="width: 10%;"> # </th>
                                    <th style="width: 20%;"> 账号 </th>
                                    <th style="width: 55%;"> 标题 </th>
                                    <th style="width: 15%; text-align: center;"> 状态 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($ticketList->isEmpty())
                                    <tr>
                                        <td colspan="4" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($ticketList as $key => $ticket)
                                        <tr class="odd gradeX">
                                            <td> {{$key + 1}} </td>
                                            <td>
                                                @if(empty($ticket->user))
                                                    【账号已删除】
                                                @else
                                                    <a href="{{url('admin/userList?id=' . $ticket->user->id)}}" target="_blank">{{$ticket->user->username}}</a> </td>
                                                @endif
                                            <td> <a href="{{url('ticket/replyTicket?id=') . $ticket->id}}" target="_blank">{{$ticket->title}}</a> </td>
                                            <td style="text-align: center;">
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
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        // 回复工单
        function reply(id) {
            window.location.href = '{{url('ticket/replyTicket?id=')}}' + id;
        }
    </script>
@endsection