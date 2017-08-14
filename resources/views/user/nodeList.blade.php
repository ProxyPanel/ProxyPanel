@extends('user.layouts')

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
                <a href="{{url('user/nodeList')}}">节点列表</a>
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
                            <i class="icon-list font-dark"></i>
                            <span class="caption-subject bold uppercase"> 节点列表 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 节点名称 </th>
                                    <th> 出口带宽 </th>
                                    <th> 负载 </th>
                                    <th> 在线人数 </th>
                                    <th> 产生流量 </th>
                                    <th> 流量比例 </th>
                                    <th> 协议 </th>
                                    <th> 混淆 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                <div class="alert alert-danger">
                                    <strong>流量比例：</strong> 1表示用100M就结算100M，0.1表示用100M结算10M，5表示用100M结算500M，以此类推。目的是在于限制优质节点频繁使用，请大家珍惜自己的流量，选择适合自己的节点。
                                </div>
                                @if($nodeList->isEmpty())
                                    <tr>
                                        <td colspan="10">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($nodeList as $node)
                                        <tr class="odd gradeX">
                                            <td> {{$node->id}} </td>
                                            <td> {{$node->name}} @if ($node->compatible) <span class="label label-warning"> 兼容SS </span> @endif </td>
                                            <td> {{$node->bandwidth}}M </td>
                                            <td> <span class="label label-danger"> {{$node->load}} </span> </td>
                                            <td> <span class="label label-danger"> {{$node->online_users}} </span> </td>
                                            <td> {{$node->transfer}} </td>
                                            <td> {{$node->traffic_rate}} </td>
                                            <td> <span class="label label-info"> {{$node->protocol}} </span> </td>
                                            <td> <span class="label label-info"> {{$node->obfs}} </span> </td>
                                            <td>
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="">查看配置</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-5 col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$nodeList->total()}} 个节点</div>
                            </div>
                            <div class="col-md-7 col-sm-7">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $nodeList->links() }}
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

@endsection