@extends('admin.layouts')
@section('css')
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 格式转换 </span>
                            <small>Shadowsocks 转 ShadowsocksR</small>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row" style="padding-bottom:10px;">
                            <div class="col-md-4">
                                <label for="method" class="col-md-4">加密方式</label>
                                <div class="col-md-8" style="padding-bottom:10px;">
                                    <select class="form-control" name="method" id="method">
                                        @foreach ($method_list as $method)
                                            <option value="{{$method->name}}" @if($method->is_default) selected @endif>{{$method->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="transfer_enable" class="col-md-4">可用流量</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="transfer_enable" value="1000" id="transfer_enable" placeholder="" required>
                                        <span class="input-group-addon">GiB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="protocol" class="col-md-4">协议</label>
                                <div class="col-md-8" style="padding-bottom:10px;">
                                    <select class="form-control" name="protocol" id="protocol">
                                        @foreach ($protocol_list as $protocol)
                                            <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>{{$protocol->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="protocol_param" class="col-md-4">协议参数</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="protocol_param" id="protocol_param" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="obfs" class="col-md-4">混淆</label>
                                <div class="col-md-8" style="padding-bottom:10px;">
                                    <select class="form-control" name="obfs" id="obfs">
                                        @foreach ($obfs_list as $obfs)
                                            <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>{{$obfs->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="obfs_param" class="col-md-4">混淆参数</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="obfs_param" id="obfs_param" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <textarea class="form-control" rows="22" name="content" id="content" placeholder="请填入要转换的配置信息" autofocus></textarea>
                            </div>
                            <div class="col-md-6">
                                <textarea class="form-control" rows="22" name="result" id="result" onclick="this.focus();this.select()" readonly="readonly"></textarea>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-6">
                                <button class="btn blue btn-block" onclick="doConvert()">转 换</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn red btn-block" onclick="doDownload()">下 载</button>
                            </div>
                        </div>
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
    <script type="text/javascript">
        // 转换
        function doConvert() {
            var _token = '{{csrf_token()}}';
            var method = $('#method').val();
            var transfer_enable = $('#transfer_enable').val();
            var protocol = $('#protocol').val();
            var protocol_param = $('#protocol_param').val();
            var obfs = $('#obfs').val();
            var obfs_param = $('#obfs_param').val();
            var content = $('#content').val();

            if (content == '') {
                layer.msg('请填入要转换的配置信息', {time:1000});
                return ;
            }

            layer.confirm('确定继续转换吗？', {icon: 2, title:'警告'}, function(index) {
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/convert')}}",
                    async: false,
                    data: {_token:_token, method:method, transfer_enable:transfer_enable, protocol:protocol, protocol_param:protocol_param, obfs:obfs, obfs_param:obfs_param, content: content},
                    dataType: 'json',
                    success: function (ret) {
                        if (ret.status == 'success') {
                            $("#result").val(ret.data);
                        } else {
                            $("#result").val(ret.message);
                        }
                    }
                });

                layer.close(index);
            });

            return false;
        }

        // 下载
        function doDownload() {
            window.location.href = '{{url('admin/download?type=1')}}';
        }
    </script>
@endsection