@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <ul class="nav nav-tabs">
                            <li @if(Request::get('tab') == '' || Request::get('tab') == '1') class="active" @endif>
                                <a href="#tab1" data-toggle="tab"> 加密方式 </a>
                            </li>
                            <li @if(Request::get('tab') == '2') class="active" @endif>
                                <a href="#tab2" data-toggle="tab"> 协议 </a>
                            </li>
                            <li @if(Request::get('tab') == '3') class="active" @endif>
                                <a href="#tab3" data-toggle="tab"> 混淆 </a>
                            </li>
                            <li @if(Request::get('tab') == '4') class="active" @endif>
                                <a href="#tab4" data-toggle="tab"> 等级 </a>
                            </li>
                            <li @if(Request::get('tab') == '5') class="active" @endif>
                                <a href="#tab5" data-toggle="tab"> 国家地区 </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade {{Request::get('tab') == '' || Request::get('tab') == '1' ? 'active in' : ''}}" id="tab1">
                                <div class="actions">
                                    <div class="btn-group">
                                        <button class="btn sbold blue" data-toggle="modal" data-target="#add_config_modal"> 新增 <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light table-checkable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%;"> 名称 </th>
                                                <th style="width: 50%;"> 操作 </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if($method_list->isEmpty())
                                            <tr>
                                                <td colspan="2">暂无数据</td>
                                            </tr>
                                        @else
                                            @foreach($method_list as $method)
                                                <tr class="odd gradeX">
                                                    <td> {{$method->name}} </td>
                                                    <td>
                                                        @if($method->is_default)
                                                            <span class='label label-info'>默认</span>
                                                        @else
                                                            <button type="button" class="btn btn-sm blue btn-outline" onclick="setDefault('1', '{{$method->id}}')">默认</button>
                                                            <button type="button" class="btn btn-sm red btn-outline" onclick="delConfig('1', '{{$method->id}}')">删除</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{Request::get('tab') == '2' ? 'active in' : ''}}" id="tab2">
                                <div class="actions">
                                    <div class="btn-group">
                                        <button class="btn sbold blue" data-toggle="modal" data-target="#add_config_modal"> 新增 <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light table-checkable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50%;"> 名称 </th>
                                                <th style="width: 50%;"> 操作 </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if($method_list->isEmpty())
                                            <tr>
                                                <td colspan="2">暂无数据</td>
                                            </tr>
                                        @else
                                            @foreach($protocol_list as $protocol)
                                                <tr class="odd gradeX">
                                                    <td> {{$protocol->name}} @if($protocol->is_default) <small><span class='label label-info label-sm'>默认</span></small> @endif </td>
                                                    <td>
                                                        @if(!$protocol->is_default)
                                                            <button type="button" class="btn btn-sm blue btn-outline" onclick="setDefault('2', '{{$protocol->id}}')">默认</button>
                                                            <button type="button" class="btn btn-sm red btn-outline" onclick="delConfig('2', '{{$protocol->id}}')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{Request::get('tab') == '3' ? 'active in' : ''}}" id="tab3">
                                <div class="actions">
                                    <div class="btn-group">
                                        <button class="btn sbold blue" data-toggle="modal" data-target="#add_config_modal"> 新增 <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light table-checkable">
                                        <thead>
                                        <tr>
                                            <th style="width: 50%;"> 名称 </th>
                                            <th style="width: 50%;"> 操作 </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if($obfs_list->isEmpty())
                                            <tr>
                                                <td colspan="2">暂无数据</td>
                                            </tr>
                                        @else
                                            @foreach($obfs_list as $obfs)
                                                <tr class="odd gradeX">
                                                    <td> {{$obfs->name}} @if($obfs->is_default) <small><span class='label label-info label-sm'>默认</span></small> @endif </td>
                                                    <td>
                                                        @if(!$obfs->is_default)
                                                            <button type="button" class="btn btn-sm blue btn-outline" onclick="setDefault('3', '{{$obfs->id}}')">默认</button>
                                                            <button type="button" class="btn btn-sm red btn-outline" onclick="delConfig('3', '{{$obfs->id}}')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{Request::get('tab') == '4' ? 'active in' : ''}}" id="tab4">
                                <div class="actions">
                                    <div class="btn-group">
                                        <button class="btn sbold blue" data-toggle="modal" data-target="#add_level_modal"> 新增 <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light table-checkable">
                                        <thead>
                                            <tr>
                                                <th style="width: 35%;"> 等级 </th>
                                                <th style="width: 35%;"> 名称 </th>
                                                <th style="width: 30%;"> 操作 </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if($level_list->isEmpty())
                                            <tr>
                                                <td colspan="3">暂无数据</td>
                                            </tr>
                                        @else
                                            @foreach($level_list as $level)
                                                <tr class="odd gradeX" >
                                                    <td> <input id="level_{{$level->id}}" name="level" value="{{$level->level}}" type="text" class="form-control"> </td>
                                                    <td> <input id="level_name_{{$level->id}}" name="level_name" value="{{$level->level_name}}" type="text" class="form-control"></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm blue btn-outline" onclick="updateLevel('4', '{{$level->id}}')">修改</button>
                                                        <button type="button" class="btn btn-sm red btn-outline" onclick="delLevel('4', '{{$level->id}}')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{Request::get('tab') == '5' ? 'active in' : ''}}" id="tab5">
                                <div class="actions">
                                    <div class="btn-group">
                                        <button class="btn sbold blue" data-toggle="modal" data-target="#add_country_modal"> 新增 <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <div class="table-scrollable table-scrollable-borderless">
                                    <table class="table table-hover table-light table-checkable">
                                        <thead>
                                            <tr>
                                                <th style="width: 20%;"> 图标 </th>
                                                <th style="width: 25%;"> 国家/地区名称 </th>
                                                <th style="width: 25%;"> 代码 </th>
                                                <th style="width: 30%;"> 操作 </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if($country_list->isEmpty())
                                            <tr>
                                                <td colspan="4">暂无数据</td>
                                            </tr>
                                        @else
                                            @foreach($country_list as $country)
                                                <tr class="odd gradeX" >
                                                    <td>
                                                        <img src="{{asset('assets/images/country/' . $country->country_code . '.png')}}" />
                                                    </td>
                                                    <td> <input id="country_name_{{$country->id}}" name="country_name" value="{{$country->country_name}}" type="text" class="form-control"> </td>
                                                    <td> <input id="country_code_{{$country->id}}" name="country_code" value="{{$country->country_code}}" type="text" class="form-control"></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm blue btn-outline" onclick="updateCountry('5', '{{$country->id}}')">修改</button>
                                                        <button type="button" class="btn btn-sm red btn-outline" onclick="delCountry('5', '{{$country->id}}')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix margin-top-20"></div>
                        <div id="add_config_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">新增配置</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger" style="display: none;" id="msg"></div>
                                        <!-- BEGIN FORM-->
                                        <form action="#" method="post" class="form-horizontal">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label for="type" class="col-md-4 control-label">类型</label>
                                                    <div class="col-md-6">
                                                        <select class="form-control" name="type" id="type">
                                                            <option value="1" selected>加密方式</option>
                                                            <option value="2">协议</option>
                                                            <option value="3">混淆</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name" class="col-md-4 control-label"> 名称 </label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="name" id="name" placeholder="">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- END FORM-->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                                        <button type="button" class="btn red btn-outline" onclick="return addConfig();">提交</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="add_level_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">新增配置</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger" style="display: none;" id="level_msg"></div>
                                        <!-- BEGIN FORM-->
                                        <form action="#" method="post" class="form-horizontal">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label for="level" class="col-md-4 control-label"> 等级 </label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="level" id="add_level" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="level_name" class="col-md-4 control-label"> 等级名称 </label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="level_name" id="add_level_name" placeholder="">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- END FORM-->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                                        <button type="button" class="btn red btn-outline" onclick="return addLevel(4);">提交</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="add_country_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">新增国家/地区</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger" style="display: none;" id="country_msg"></div>
                                        <!-- BEGIN FORM-->
                                        <form action="#" method="post" class="form-horizontal">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label for="level" class="col-md-4 control-label"> 国家/地区名称 </label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="country_name" id="add_country_name" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="level_name" class="col-md-4 control-label"> 国家代码 </label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="country_code" id="add_country_code" placeholder="">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- END FORM-->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                                        <button type="button" class="btn red btn-outline" onclick="return addCountry(5);">提交</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    <script type="text/javascript">
        // modal关闭时刷新页面
        $(".modal").on("hidden.bs.modal", function () {
            window.location.reload();
        });

        // 添加等级
        function addLevel(tabId) {
            var level = $('#add_level').val();
            var level_name = $('#add_level_name').val();

            if (level == '') {
                $("#level_msg").show().html("等级不能为空");
                $("#level").focus();
                return false;
            }

            if (level_name == '') {
                $("#level_msg").show().html("等级名称不能为空");
                $("#level_name").focus();
                return false;
            }

            $.ajax({
                url:'{{url('admin/addLevel')}}',
                type:"POST",
                data:{_token:'{{csrf_token()}}', level:level, level_name:level_name},
                beforeSend:function(){
                    $("#level_msg").show().html("正在添加");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#level_msg").show().html(ret.message);
                        return false;
                    }

                    $("#add_level_modal").modal("hide");
                    window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                },
                error:function(){
                    $("#level_msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }

        // 更新等级
        function updateLevel(tabId, id) {
            var level = $('#level_' + id).val();
            var level_name = $('#level_name_' + id).val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/updateLevel')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', id: id, level:level, level_name:level_name},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                }
            });
        }

        // 删除等级
        function delLevel(tabId, id) {
            layer.confirm('确定删除该等级吗？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delLevel')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 添加国家/地区
        function addCountry(tabId) {
            var country_name = $('#add_country_name').val();
            var country_code = $('#add_country_code').val();

            if (country_name == '') {
                $("#country_msg").show().html("国家/地区名称不能为空");
                $("#add_country_name").focus();
                return false;
            }

            if (country_code == '') {
                $("#country_msg").show().html("国家/地区代码不能为空");
                $("#add_country_code").focus();
                return false;
            }

            $.ajax({
                url:'{{url('admin/addCountry')}}',
                type:"POST",
                data:{_token:'{{csrf_token()}}', country_name:country_name, country_code:country_code},
                beforeSend:function(){
                    $("#country_msg").show().html("正在添加");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#country_msg").show().html(ret.message);
                        return false;
                    }

                    $("#add_country_modal").modal("hide");
                    window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                },
                error:function(){
                    $("#country_msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }

        // 更新国家/地区
        function updateCountry(tabId, id) {
            var country_name = $('#country_name_' + id).val();
            var country_code = $('#country_code_' + id).val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/updateCountry')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', id: id, country_name:country_name, country_code:country_code},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                }
            });
        }

        // 删除国家/地区
        function delCountry(tabId, id) {
            layer.confirm('确定删除该国家/地区吗？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delCountry')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 添加配置
        function addConfig() {
            var _token = '{{csrf_token()}}';
            var name = $("#name").val();
            var type = $("#type").val();

            if (name == '') {
                $("#msg").show().html("名称不能为空");
                $("#name").focus();
                return false;
            }

            $.ajax({
                url:'{{url('admin/config')}}',
                type:"POST",
                data:{_token:_token, name:name, type:type},
                beforeSend:function(){
                    $("#msg").show().html("正在添加");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    }

                    $("#add_config_modal").modal("hide");
                },
                error:function(){
                    $("#msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }

        // 删除配置
        function delConfig(tabId, id) {
            layer.confirm('确定删除配置？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delConfig')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 置为默认
        function setDefault(tabId, id) {
            var _token = '{{csrf_token()}}';
            $.ajax({
                type: "POST",
                url: "{{url('admin/setDefaultConfig')}}",
                async: false,
                data: {_token:_token, id: id},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/config?tab=')}}' + tabId;
                        }
                    });
                }
            });
        }
    </script>
@endsection