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
                            <span class="caption-subject bold uppercase"> 反解析 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-6">
                                <textarea class="form-control" rows="33" name="content" id="content" placeholder="请填入要反解析的ShadowsocksR链接，一行一条" autofocus></textarea>
                            </div>
                            <div class="col-md-6">
                                <textarea class="form-control" rows="33" name="result" id="result" readonly="readonly"></textarea>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-6">
                                <button class="btn blue btn-block" onclick="doDecompile()">反解析</button>
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
        function doDecompile() {
            var _token = '{{csrf_token()}}';
            var content = $('#content').val();

            if (content == '') {
                layer.msg('请填入要反解析的链接信息', {time:1000});
                return ;
            }

            layer.confirm('确定继续反解析吗？', {icon: 3, title:'警告'}, function(index) {
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/decompile')}}",
                    async: false,
                    data: {_token:_token, content: content},
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
            window.location.href = '{{url('admin/download?type=2')}}';
        }
    </script>
@endsection