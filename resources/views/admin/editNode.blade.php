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
                <a href="{{url('admin/nodeList')}}">节点管理</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:;">编辑节点</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN PAGE BASE CONTENT -->
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-body form">
                            <!-- BEGIN FORM-->
                            <form action="{{url('admin/editNode')}}" method="post" class="form-horizontal" onsubmit="return do_submit();">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- BEGIN SAMPLE FORM PORTLET-->
                                            <div class="portlet light bordered">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <span class="caption-subject font-dark bold uppercase">基础信息</span>
                                                    </div>
                                                </div>
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="name" class="col-md-3 control-label"> 节点名称 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="name" value="{{$node->name}}" id="name" placeholder="" autofocus required>
                                                            <input type="hidden" name="id" value="{{$node->id}}">
                                                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="group_id" class="col-md-3 control-label"> 所属分组 </label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="group_id" id="group_id">
                                                                <option value="0">请选择</option>
                                                                @if(!$group_list->isEmpty())
                                                                    @foreach($group_list as $group)
                                                                        <option value="{{$group->id}}" {{$node->group_id == $group->id ? 'selected' : ''}}>{{$group->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <span class="help-block">没有关联任何分组时则节点不可见</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="server" class="col-md-3 control-label"> 服务器地址 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="server" value="{{$node->server}}" id="server" placeholder="" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="method" class="col-md-3 control-label">加密方式</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="method" id="method">
                                                                @foreach ($method_list as $method)
                                                                    <option value="{{$method->name}}" @if($method->name == $node->method) selected @endif>{{$method->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="bandwidth" class="col-md-3 control-label">出口带宽</label>
                                                        <div class="col-md-8">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="bandwidth" value="{{$node->bandwidth}}" id="bandwidth" placeholder="" required>
                                                                <span class="input-group-addon">M</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="traffic" class="col-md-3 control-label">每月可用流量</label>
                                                        <div class="col-md-8">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control right" name="traffic" value="{{$node->traffic}}" id="traffic" placeholder="" required>
                                                                <span class="input-group-addon">G</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="sort" class="col-md-3 control-label">排序</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="sort" value="{{$node->sort}}" id="sort" placeholder="">
                                                            <span class="help-block"> 值越大排越前 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status" class="col-md-3 control-label">状态</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="status" id="status">
                                                                <option value="1" {{$node->status == '1' ? 'selected' : ''}}>正常</option>
                                                                <option value="0" {{$node->status == '0' ? 'selected' : ''}}>维护</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END SAMPLE FORM PORTLET-->
                                        </div>
                                        <div class="col-md-6">
                                            <!-- BEGIN SAMPLE FORM PORTLET-->
                                            <div class="portlet light bordered">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <span class="caption-subject font-dark bold">扩展信息</span>
                                                    </div>
                                                </div>
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="compatible" class="col-md-3 control-label">兼容SS</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="compatible" id="compatible">
                                                                <option value="0" {{!$node->compatible ? 'selected' : ''}}>否</option>
                                                                <option value="1" {{$node->compatible ? 'selected' : ''}}>是</option>
                                                            </select>
                                                            <span class="help-block"> 请在服务端配置协议和混淆时加上<span style="color:red">_compatible</span> </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="traffic_rate" class="col-md-3 control-label"> 流量比例 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="traffic_rate" value="{{$node->traffic_rate}}" value="1.0" id="traffic_rate" placeholder="" required>
                                                            <span class="help-block"> 举例：0.1用100M结算10M，5用100M结算500M </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="protocol" class="col-md-3 control-label">协议</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="protocol" id="protocol">
                                                                @foreach ($protocol_list as $protocol)
                                                                    <option value="{{$protocol->name}}" @if($protocol->name == $node->protocol) selected @endif>{{$protocol->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="protocol_param" class="col-md-3 control-label"> 协议参数 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="protocol_param" value="{{$node->protocol_param}}" id="protocol_param" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="obfs" class="col-md-3 control-label">混淆</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="obfs" id="obfs">
                                                                @foreach ($obfs_list as $obfs)
                                                                    <option value="{{$obfs->name}}" @if($obfs->name == $node->obfs) selected @endif>{{$obfs->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="obfs_param" class="col-md-3 control-label"> 混淆参数 </label>
                                                        <div class="col-md-8">
                                                            <textarea class="form-control" rows="5" name="obfs_param" id="obfs_param">{{$node->obfs_param}}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="monitor_url" class="col-md-3 control-label">监控地址</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control right" name="monitor_url" value="{{$node->monitor_url}}" id="monitor_url" placeholder="">
                                                            <span class="help-block"> 例如：http://us1.xxx.com/monitor.php </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END SAMPLE FORM PORTLET-->
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-offset-11 col-md-4">
                                                    <button type="submit" class="btn green">提 交</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"> </div>
                                    </div>
                                </div>
                            </form>
                            <!-- END FORM-->
                        </div>
                    </div>
                </div>
                <!-- END PAGE BASE CONTENT -->
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var id = '{{Request::get('id')}}';
            var name = $('#name').val();
            var group_id = $("#group_id option:selected").val();
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
                url: "{{url('admin/editNode')}}",
                async: false,
                data: {_token:_token, id:id, name: name, group_id:group_id, server:server, method:method, custom_method:custom_method, traffic_rate:traffic_rate, protocol:protocol, protocol_param:protocol_param, obfs:obfs, obfs_param:obfs_param, bandwidth:bandwidth, traffic:traffic, monitor_url:monitor_url, compatible:compatible, sort:sort, status:status},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/nodeList?page=') . Request::get('page')}}';
                        }
                    });
                }
            });

            return false;
        }
    </script>
@endsection