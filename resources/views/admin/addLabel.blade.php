@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('errorMsg'))
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <strong>错误：</strong> {{Session::get('errorMsg')}}
                    </div>
                @endif
                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">添加标签</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form role="form" action="{{url('admin/addLabel')}}" method="post" enctype="multipart/form-data" onsubmit="return doSubmit();">
                            <div class="form-body">
                                <div class="form-group">
                                    <label>名称</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="" autofocus required>
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                </div>
                                <div class="form-group">
                                    <label>排序</label>
                                    <input type="text" class="form-control" name="sort" id="sort" value="0" required />
                                    <span class="help-block"> 值越高显示时越靠前 </span>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn green">提交</button>
                            </div>
                        </form>
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
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // ajax同步提交
        function doSubmit() {
            var _token = '{{csrf_token()}}';
            var name = $('#name').val();
            var sort = $('#sort').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addLabel')}}",
                async: false,
                data: {_token:_token, name: name, sort:sort},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/labelList')}}';
                        }
                    });
                }
            });

            return false;
        }
    </script>
@endsection