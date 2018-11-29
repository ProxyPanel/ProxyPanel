@extends('admin.layouts')
@section('css')
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
                            <span class="caption-subject bold uppercase"> 有赞云回调日志 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> 应用名称 </th>
                                        {{--<th> client_id </th>--}}
                                        {{--<th> kdt_id </th>--}}
                                        <th> 有赞订单ID </th>
                                        <th> 模式 </th>
                                        <th> 消息详情 </th>
                                        {{--<th> 发送次数 </th>--}}
                                        {{--<th> 鉴权标记 </th>--}}
                                        <th> 状态 </th>
                                        {{--<th> 是否测试 </th>--}}
                                        {{--<th> 类型 </th>--}}
                                        {{--<th> 版本 </th>--}}
                                        <th> 创建时间 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($list->isEmpty())
                                    <tr>
                                        <td colspan="14" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($list as $vo)
                                        <tr class="odd gradeX">
                                            <td> {{$vo->id}} </td>
                                            <td> {{$vo->kdt_name}} </td>
                                            {{--<td> {{$vo->client_id}} </td>--}}
                                            {{--<td> {{$vo->kdt_id}} </td>--}}
                                            <td> {{$vo->yz_id}} </td>
                                            <td> {{$vo->mode ? '自用型/工具型/平台型消息' : '签名模式消息'}} </td>
                                            <td> {{$vo->msg}} </td>
                                            {{--<td> {{$vo->sendCount}} </td>--}}
                                            {{--<td> {{$vo->sign}} </td>--}}
                                            <td> {{$vo->status}} </td>
                                            {{--<td> {{$vo->test ? '是' : '否'}} </td>--}}
                                            {{--<td> {{$vo->type}} </td>--}}
                                            {{--<td> {{$vo->version}} </td>--}}
                                            <td> {{$vo->created_at}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $list->links() }}
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
@endsection
