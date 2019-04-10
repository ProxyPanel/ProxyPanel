@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        input,select {
            margin-bottom: 5px;
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="note note-info">
                            <p>1.禁止设备订阅功能是根据客户端订阅时请求头信息做判断，禁用相应设备订阅时返回错误信息</p>
                            <p>以下请求头信息不全或者错误，请各位自行检测，提到Issues</p>
                        </div>
                    </div>
                </div>
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase">订阅设备列表</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <select class="form-control" name="status" id="status" onChange="doSearch()">
                                    <option value="" @if(Request::get('status') == '') selected @endif>状态</option>
                                    <option value="0" @if(Request::get('status') == '0') selected @endif>禁用</option>
                                    <option value="1" @if(Request::get('status') == '1') selected @endif>正常</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12">
                                <button type="button" class="btn blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 名称 </th>
                                    <th> 类型 </th>
                                    <th> 平台 </th>
                                    <th> 请求头 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($deviceList->isEmpty())
                                        <tr>
                                            <td colspan="8" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($deviceList as $vo)
                                            <tr class="odd gradeX">
                                                <td> {{$vo->id}} </td>
                                                <td> {{$vo->name}} </td>
                                                <td> {!! $vo->type_label !!} </td>
                                                <td> {!! $vo->platform_label !!} </td>
                                                <td> {{$vo->header}} </td>
                                                <td>
                                                    @if($vo->status == 0)
                                                        <button type="button" class="btn btn-sm green btn-outline" onclick="setDeviceStatus('{{$vo->id}}', 1)">启用</button>
                                                    @endif
                                                    @if($vo->status == 1)
                                                        <button type="button" class="btn btn-sm red btn-outline" onclick="setDeviceStatus('{{$vo->id}}', 0)">禁用</button>
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
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$deviceList->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $deviceList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        // 搜索
        function doSearch() {
            var status = $("#status option:checked").val();

            window.location.href = '{{url('subscribe/deviceList')}}' + '?status=' + status;
        }

        // 重置
        function doReset() {
            window.location.href = '{{url('subscribe/deviceList')}}';
        }

        // 启用禁用订阅设备
        function setDeviceStatus(id, status) {
            $.post("{{url('subscribe/setDeviceStatus')}}", {_token:'{{csrf_token()}}', id:id, status:status}, function(ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    window.location.reload();
                });
            });
        }
    </script>
@endsection