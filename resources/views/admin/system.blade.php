@extends('admin.layouts')

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
                <a href="javascript:;">设置</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/config')}}">系统配置</a>
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
                            <i class="icon-info font-dark"></i>
                            <span class="caption-subject bold uppercase"> 系统配置 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <form action="#" method="post" class="form-horizontal" onsubmit="return do_submit();">
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="username" class="col-md-2 control-label">随机端口</label>
                                    <div class="col-md-6">
                                        <input type="checkbox" class="make-switch" @if($is_rand_port) checked @endif id="is_rand_port" data-on-color="success" data-off-color="danger" data-on-text="启用" data-off-text="关闭">
                                    </div>
                                </div>
                            </div>
                        </form>
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
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function(event, state) {
                var is_rand_port = 0;

                if (state) {
                    var is_rand_port = 1;
                }

                $.post("{{url('admin/enableRandPort')}}", {_token:'{{csrf_token()}}', value:is_rand_port}, function (ret) {
                    console.log(ret);
                });
            }
        });
    </script>
@endsection