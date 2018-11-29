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
                        <div class="caption">
                            <span class="caption-subject font-dark sbold uppercase">添加节点分组</span>
                        </div>
                        <div class="actions"></div>
                    </div>
                    <div class="portlet-body form">
                        @if (Session::has('errorMsg'))
                            <div class="alert alert-danger">
                                <button class="close" data-close="alert"></button>
                                <strong>错误：</strong> {{Session::get('errorMsg')}}
                            </div>
                        @endif
                        <!-- BEGIN FORM-->
                        <form action="#" method="post" enctype="multipart/form-data" class="form-horizontal" role="form" onsubmit="return do_submit();">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-3">分组名称</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="name" value="" id="name" placeholder="" required>
                                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">分组级别</label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="level" id="level" required>
                                            @if(!$levelList->isEmpty())
                                                @foreach($levelList as $level)
                                                    <option value="{{$level->level}}">{{$level->level_name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <span class="help-block">暂无用</span>
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
                        <!-- END FORM-->
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
        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var name = $('#name').val();
            var level = $("#level option:selected").val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addGroup')}}",
                async: false,
                data: {_token:_token, name:name, level:level},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/groupList')}}';
                        }
                    });
                }
            });

            return false;
        }
    </script>
@endsection