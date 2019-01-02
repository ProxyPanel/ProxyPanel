@extends('admin.layouts')
@section('css')
    <style type="text/css">
        input,select {
            margin-bottom: 5px;
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
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
                        <div class="row" style="padding-bottom:5px;">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <input type="text" class="col-md-4 form-control" name="username" value="{{Request::get('username')}}" id="username" placeholder="申请账号" onkeydown="if(event.keyCode==13){do_search();}">
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select class="form-control" name="status" id="status" onChange="do_search()">
                                    <option value="" @if(Request::get('status') == '') selected @endif>状态</option>
                                    <option value="-1" @if(Request::get('status') == '-1') selected @endif>驳回</option>
                                    <option value="0" @if(Request::get('status') == '0') selected @endif>待审核</option>
                                    <option value="1" @if(Request::get('status') == '1') selected @endif>审核通过待打款</option>
                                    <option value="2" @if(Request::get('status') == '2') selected @endif>已打款</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <button type="button" class="btn blue" onclick="do_search();">查询</button>
                                <button type="button" class="btn grey" onclick="do_reset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr class="uppercase">
                                        <th> # </th>
                                        <th> 申请账号 </th>
                                        <th> 提现金额 </th>
                                        <th> 申请时间 </th>
                                        <th> 状态 </th>
                                        <th> 处理时间 </th>
                                        <th> 操作 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($applyList->isEmpty())
                                        <tr>
                                            <td colspan="7" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($applyList as $apply)
                                            <tr>
                                                <td> {{$apply->id}} </td>
                                                <td>
                                                    @if(empty($apply->user))
                                                        【账号已删除】
                                                    @else
                                                        <a href="{{url('admin/editUser?id=' . $apply->user_id)}}" target="_blank">{{$apply->user->username}}</a>
                                                    @endif
                                                </td>
                                                <td> {{$apply->amount}} </td>
                                                <td> {{$apply->created_at}} </td>
                                                <td>
                                                    @if($apply->status == -1)
                                                        <span class="label label-default label-danger"> 驳回 </span>
                                                    @elseif($apply->status == 0)
                                                        <span class="label label-default label-info"> 待审核 </span>
                                                    @elseif($apply->status == 2)
                                                        <span class="label label-default label-success"> 已打款 </span>
                                                    @else
                                                        <span class="label label-default label-default"> 审核通过待打款 </span>
                                                    @endif
                                                </td>
                                                <td> {{$apply->created_at == $apply->updated_at ? '' : $apply->updated_at}} </td>
                                                <td>
                                                    @if($apply->status > 0 && $apply->status < 2)
                                                        <button type="button" class="btn btn-sm red btn-outline" onclick="doAudit('{{$apply->id}}')"> 审核 </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm blue btn-outline" onclick="doAudit('{{$apply->id}}')"> <i class="fa fa-search"></i> </button>
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
    <script type="text/javascript">
        // 审核
        function doAudit(id) {
            window.open('{{url('admin/applyDetail?id=')}}' + id);
        }

        // 搜索
        function do_search() {
            var username = $("#username").val();
            var status = $("#status option:checked").val();

            window.location.href = '{{url('admin/applyList')}}' + '?username=' + username + '&status=' + status;
        }

        // 重置
        function do_reset() {
            window.location.href = '{{url('admin/applyList')}}';
        }
    </script>
@endsection