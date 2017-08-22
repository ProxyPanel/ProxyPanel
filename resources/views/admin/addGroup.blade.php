@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="javascript:;">节点管理</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/groupList')}}">节点分组</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/addGroup')}}">添加节点分组</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-6">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-body form">
                            <!-- BEGIN FORM-->
                            <form action="{{url('admin/addGroup')}}" method="post" class="form-horizontal" onsubmit="return do_submit();">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label for="name" class="col-md-3 control-label"> 分组名称 </label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="name" id="name" placeholder="" autofocus required>
                                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="server" class="col-md-3 control-label"> 级别 </label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="level" id="level" placeholder="" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-6 col-md-4">
                                            <button type="submit" class="btn green"> 提 交 </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- END FORM-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var name = $('#name').val();
            var server = $('#server').val();
            var method = $('#method').val();
            var custom_method = $('#custom_method').val();
            var traffic_rate = $('#traffic_rate').val();
            var protocol = $('#protocol').val();
            var protocol_param = $('#protocol_param').val();
            var obfs = $('#obfs').val();
            var obfs_param = $('#obfs_param').val();
            var bandwidth = $('#bandwidth').val();
            var traffic = $('#traffic').val();
            var monitor_url = $('#monitor_url').val();
            var compatible = $('#compatible').val();
            var sort = $('#sort').val();
            var status = $('#status').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addNode')}}",
                async: false,
                data: {_token:_token, name: name, server:server, method:method, custom_method:custom_method, traffic_rate:traffic_rate, protocol:protocol, protocol_param:protocol_param, obfs:obfs, obfs_param:obfs_param, bandwidth:bandwidth, traffic:traffic, monitor_url:monitor_url, compatible:compatible, sort:sort, status:status},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status == 'success') {
                        bootbox.alert(ret.message, function () {
                            window.location.href = '{{url('admin/nodeList')}}';
                        });
                    } else {
                        bootbox.alert(ret.message);
                    }
                }
            });

            return false;
        }
    </script>
@endsection