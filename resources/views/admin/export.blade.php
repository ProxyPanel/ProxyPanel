@extends('admin.layouts')

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
                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr class="uppercase">
                                        <th style="width: 10%;">#</th>
                                        <th style="width: 15%;">节点</th>
                                        <th style="width: 15%;">域名</th>
                                        <th style="width: 15%;">IPv4</th>
                                        <th style="width: 45%;">配置信息</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nodeList as $k => $node)
                                        <tr>
                                            <td>{{$k + 1}}</td>
                                            <td>
                                                {{$node->name}}
                                                @if($node->compatible) <span class="label label-info">兼</span> @endif
                                                @if($node->single) <span class="label label-danger">单</span> @endif
                                                @if($node->ipv6) <span class="label label-danger">IPv6</span> @endif
                                            </td>
                                            <td>{{$node->server}}</td>
                                            <td>{{$node->ip}}</td>
                                            <td>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#txt_{{$node->id}}"> 文本 </a>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#scheme_{{$node->id}}"> SCHEME </a>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#qrcode_{{$node->id}}"> 二维码 </a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                                            <textarea class="form-control" rows="10" readonly="readonly"> {{$node->txt}} </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade draggable-modal" id="scheme_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                            <h4 class="modal-title">Url Scheme</h4>
                                        </div>
                                        <div class="modal-body">
                                            <textarea class="form-control" rows="7" readonly="readonly">
                                                {{$node->ssr_scheme}}
                                                @if ($node->compatible)
                                                    {{$node->ss_scheme}}
                                                @endif
                                            </textarea>
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
                                                        <div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div id="qrcode_ss_img_{{$node->id}}" style="text-align: center;"></div>
                                                    </div>
                                                @else
                                                    <div class="col-md-12">
                                                        <div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
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
                <!-- END PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        var UIModals = function () {
            var n = function () {
                @foreach($nodeList as $node)
                    $("#txt_{{$node->id}}").draggable({handle: ".modal-header"});
                    $("#txt_v6_{{$node->id}}").draggable({handle: ".modal-header"});
                    $("#scheme_{{$node->id}}").draggable({handle: ".modal-header"});
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