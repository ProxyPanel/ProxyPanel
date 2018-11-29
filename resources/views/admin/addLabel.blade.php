@extends('admin.layouts')
@section('css')
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
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
                <form role="form" action="{{url('admin/addLabel')}}" method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return doSubmit();">
                    <div class="form-body">
                        <div class="form-group">
                            <label for="name" class="control-label col-md-3">名称</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" id="name" placeholder="" autofocus required>
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sort" class="control-label col-md-3">排序</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="sort" id="sort" value="0" required />
                                <span class="help-block"> 值越高显示时越靠前 </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-4">
                                <button type="submit" class="btn green">提交</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END PORTLET-->
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
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