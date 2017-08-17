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
                                    <strong>流量比例：</strong> 1表示用100M就结算100M，0.1表示用100M结算10M，5表示用100M结算500M，以此类推，越是优质节点则比例越高。
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
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#txt_{{$node->id}}"> 查看配置 </a>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#qrcode_{{$node->id}}"> 二维码 </a>
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

                        @foreach ($nodeList as $node)
                            <div class="modal fade draggable-modal" id="txt_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                            <h4 class="modal-title">配置信息</h4>
                                        </div>
                                        <div class="modal-body">
                                            <textarea class="form-control" rows="10" onclick="this.focus();this.select()" readonly="readonly"> {{$node->txt}} </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="qrcode_{{$node->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog @if(!$node->compatible) modal-sm @endif">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                            <h4 class="modal-title">请使用客户端扫描二维码</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                @if ($node->compatible)
                                                    <div class="col-md-6">
                                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SSR</span></div>
                                                        <div id="qrcode_ssr_img_{{$node->id}}"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SS</span></div>
                                                        <div id="qrcode_ss_img_{{$node->id}}"></div>
                                                    </div>
                                                @else
                                                    <div class="col-md-12">
                                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SSR</span></div>
                                                        <div id="qrcode_ssr_img_{{$node->id}}"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
    <script src="/assets/global/plugins/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        var UIModals = function () {
            var n = function () {
                @foreach($nodeList as $node)
                    $("#txt_{{$node->id}}").draggable({handle: ".modal-header"});
                    $("#qrcode_{{$node->id}}").draggable({handle: ".modal-header"});
                @endforeach
            };

            return {
                init: function () {
                    n()
                }
            }
        }();

        jQuery(document).ready(function () {
            UIModals.init()
        });

        // 循环输出节点scheme用于生成二维码
        @foreach ($nodeList as $node)
            $('#qrcode_ssr_img_{{$node->id}}').qrcode("{{$node->ssr_scheme}}");
            $('#qrcode_ss_img_{{$node->id}}').qrcode("{{$node->ss_scheme}}");
        @endforeach
    </script>
@endsection