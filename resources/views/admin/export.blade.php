@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
    <link rel="stylesheet" href="/assets/global/fonts/font-awesome/font-awesome.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">【{{$user->username}}】连接配置信息</h2>
            </div>
            <div class="panel-body">
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th>#</th>
                        <th>节点</th>
                        <th>扩展</th>
                        <th>域名</th>
                        <th>IPv4</th>
                        <th>配置信息</th>
                    </tr>
                    </thead>
                    <tbody class="table-striped">
                    @foreach($nodeList as $node)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <a href="/admin/editNode?id={{$node->id}}" target="_blank"> {{$node->name}} </a>
                            </td>
                            <td>
                                @if($node->compatible) <span class="label label-info">兼</span> @endif
                                @if($node->single) <span class="label label-danger">单</span> @endif
                                @if($node->ipv6) <span class="label label-danger">IPv6</span> @endif
                            </td>
                            <td>{{$node->server}}</td>
                            <td>{{$node->ip}}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-info" data-toggle="modal" href="#txt_{{$node->id}}"><i class="icon fa-file-text"></i></button>
                                    <button class="btn btn-info" data-toggle="modal" href="#link_{{$node->id}}"><i class="icon fa-code"></i></button>
                                    <button class="btn btn-info" data-toggle="modal" href="#qrcode_{{$node->id}}"><i class="icon fa-qrcode"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 {{$nodeList->total()}} 个账号
                    </div>
                    <div class="col-sm-8">
                        <div class="Page navigation float-right">
                            {{ $nodeList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach ($nodeList as $node)
        <div class="modal fade draggable-modal" id="txt_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">{{trans('home.setting_info')}}</h4>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" rows="12" readonly="readonly">{{$node->txt}}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- 配置链接 -->
        <div class="modal fade draggable-modal" id="link_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">{{$node->name}}</h4>
                    </div>
                    <div class="modal-body">
                        @if($node->type == 1)
                            @if($node->ss_scheme)
                                <textarea class="form-control" rows="3" readonly="readonly">{{$node->ss_scheme}}</textarea>
                                <a href="{{$node->ss_scheme}}" class="btn btn-danger btn-block mt-10">打开SS</a>
                            @else
                                <textarea class="form-control" rows="5" readonly="readonly">{{$node->ssr_scheme}}</textarea>
                                <a href="{{$node->ssr_scheme}}" class="btn btn-danger btn-block mt-10">打开SSR</a>
                            @endif
                        @else
                            @if($node->v2_scheme)
                                <textarea class="form-control" rows="3" readonly="readonly">{{$node->v2_scheme}}</textarea>
                                <a href="{{$node->v2_scheme}}" class="btn btn-danger btn-block mt-10">打开V2ray</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- 配置二维码 -->
        <div class="modal fade" id="qrcode_{{$node->id}}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog @if($node->type == 2 || !$node->compatible) modal-sm @endif">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">{{trans('home.scan_qrcode')}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @if ($node->type == 1)
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
                            @else
                                <div class="col-md-12">
                                    <div id="qrcode_v2_img_{{$node->id}}" style="text-align: center;"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/custom/Plugin/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/webui-popover.js"></script>

    <script type="text/javascript">
        const UIModals = function () {
            const n = function () {
                @foreach($nodeList as $node)
                $("#txt_{{$node->id}}").draggable({handle: ".modal-header"});
                $("#link_{{$node->id}}").draggable({handle: ".modal-header"});
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
        @if($node->type == 1)
        $('#qrcode_ssr_img_{{$node->id}}').qrcode("{{$node->ssr_scheme}}");
        $('#download_qrcode_ssr_img_{{$node->id}}').attr({
            'download': 'code',
            'href': $('#qrcode_ssr_img_{{$node->id}} canvas')[0].toDataURL("image/png")
        });
        @if($node->compatible)
        $('#qrcode_ss_img_{{$node->id}}').qrcode("{{$node->ss_scheme}}");
        $('#download_qrcode_ss_img_{{$node->id}}').attr({
            'download': 'code',
            'href': $('#qrcode_ss_img_{{$node->id}} canvas')[0].toDataURL("image/png")
        });
        @endif
        @else
        $('#qrcode_v2_img_{{$node->id}}').qrcode("{{$node->v2_scheme}}");
        $('#download_qrcode_v2_img_{{$node->id}}').attr({
            'download': 'code',
            'href': $('#qrcode_v2_img_{{$node->id}} canvas')[0].toDataURL("image/png")
        });
        @endif
        @endforeach
    </script>
@endsection