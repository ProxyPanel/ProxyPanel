@extends('admin.layouts')

@section('css')
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/applyList')}}">提现管理</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">提现申请列表</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                    <tr class="uppercase">
                                        <th> ID </th>
                                        <th> 申请账号 </th>
                                        <th> 提现金额 </th>
                                        <th> 申请时间 </th>
                                        <th> 审核时间 </th>
                                        <th> 状态 </th>
                                        <th> 操作 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($applyList->isEmpty())
                                        <tr>
                                            <td colspan="9" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($applyList as $apply)
                                            <tr>
                                                <td> {{$apply->id}} </td>
                                                <td> <a href="{{url('admin/editUser?id=' . $apply->user_id)}}" target="_blank">{{$apply->user->username}}</a> </td>
                                                <td> {{$apply->amount}} </td>
                                                <td> {{$apply->created_at}} </td>
                                                <td> {{$apply->created_at == $apply->updated_at ? '' : $apply->updated_at}} </td>
                                                <td>
                                                    @if($apply->status == -1)
                                                        <span class="label label-sm label-danger"> 驳回 </span>
                                                    @elseif($apply->status == 0)
                                                        <span class="label label-sm label-info"> 审核通过待打款 </span>
                                                    @elseif($apply->status == 2)
                                                        <span class="label label-sm label-success"> 已打款 </span>
                                                    @else
                                                        <span class="label label-sm label-default"> 待审核 </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm blue btn-outline" onclick="doAudit('{{$apply->id}}')">审核</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$applyList->total()}} 个申请</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $applyList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 审核
        function doAudit(id) {
            window.open('{{url('admin/applyDetail?id=')}}' + id);
        }

    </script>
@endsection