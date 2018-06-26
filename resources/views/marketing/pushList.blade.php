@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 推送消息列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group btn-group-devided">
                                <button class="btn sbold blue" data-toggle="modal" data-target="#send_modal"> 推送消息 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="alert alert-info">
                            <i class="fa fa-warning"></i> 仅会推送给关注了您的消息通道的用户 （<a href="{{url('admin/system')}}" target="_blank">设置PushBear</a>）
                        </div>
                        <div class="row">
                            <div class="col-md-2 col-sm-2">
                                <select class="form-control input-sm" name="status" id="status" onChange="doSearch()">
                                    <option value="" @if(Request::get('status') == '') selected @endif>状态</option>
                                    <option value="0" @if(Request::get('status') == '0') selected @endif>待发送</option>
                                    <option value="-1" @if(Request::get('status') == '-1') selected @endif>失败</option>
                                    <option value="1" @if(Request::get('status') == '1') selected @endif>成功</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <button type="button" class="btn btn-sm blue" onclick="doSearch();">查询</button>
                                <button type="button" class="btn btn-sm grey" onclick="doReset();">重置</button>
                            </div>
                        </div>
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> 消息标题 </th>
                                        <th> 消息内容 </th>
                                        <th> 推送状态 </th>
                                        <th> 推送时间 </th>
                                        <th> 错误信息 </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if ($list->isEmpty())
                                    <tr>
                                        <td colspan="6" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($list as $vo)
                                        <tr class="odd gradeX">
                                            <td> {{$vo->id}} </td>
                                            <td> {{$vo->title}} </td>
                                            <td> {{$vo->content}} </td>
                                            <td> {{$vo->status_label}} </td>
                                            <td> {{$vo->created_at}} </td>
                                            <td> {{$vo->error}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 条推送消息</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $list->links() }}
                                </div>
                            </div>
                        </div>

                        <!-- 推送消息 -->
                        <div id="send_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">推送消息</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger" style="display: none;" id="msg"></div>
                                        <!-- BEGIN FORM-->
                                        <form action="#" method="post" class="form-horizontal">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"> 标题 </label>
                                                    <div class="col-md-9">
                                                        <input type="text" class="form-control" name="title" id="title" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="content" class="col-md-2 control-label"> 内容 </label>
                                                    <div class="col-md-9">
                                                        <textarea class="form-control" rows="6" name="content" id="content"></textarea>
                                                        <span class="help-block"> 内容支持<a href="https://maxiang.io/" target="_blank">Markdown语法</a> </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- END FORM-->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">取消</button>
                                        <button type="button" class="btn red btn-outline" onclick="return send();">推送</button>
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
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 发送通道消息
        function send() {
            var _token = '{{csrf_token()}}';
            var title = $("#title").val();
            var content = $("#content").val();

            if (title == '') {
                $("#msg").show().html("标题不能为空");
                $("#title").focus();
                return false;
            }

            $.ajax({
                url:'{{url('marketing/addPushMarketing')}}',
                type:"POST",
                data:{_token:_token, title:title, content:content},
                beforeSend:function(){
                    $("#msg").show().html("正在添加...");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#msg").show().html(ret.message);
                        return false;
                    }

                    $("#send_modal").modal("hide");

                },
                error:function(){
                    $("#msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }

        // 关闭modal触发
        $('#send_modal').on('hide.bs.modal', function () {
            window.location.reload();
        });


        function doSearch() {
            var status = $("#status").val();

            window.location.href = "{{url('marketing/pushList?status=')}}" + status;
        }

        function doReset() {
            window.location.href = "{{url('marketing/pushList')}}";
        }
    </script>
@endsection