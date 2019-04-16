@extends('admin.layouts')
@section('css')
@endsection
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
                            <span class="caption-subject bold uppercase"> 敏感词列表 </span><small>（用于屏蔽注册邮箱后缀）</small>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" data-toggle="modal" data-target="#add_sensitive_words"> 添加敏感词 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 敏感词 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($list->isEmpty())
                                    <tr>
                                        <td colspan="3" style="text-align: center;">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($list as $vo)
                                        <tr class="odd gradeX">
                                            <td> {{$vo->id}} </td>
                                            <td> {{$vo->words}} </td>
                                            <td>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delWord('{{$vo->id}}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$list->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $list->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <div id="add_sensitive_words" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"> 添加敏感词 </h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="words" id="words" placeholder="请填入敏感词" class="form-control margin-bottom-20">
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline"> 关闭 </button>
                        <button type="button" data-dismiss="modal" class="btn green btn-outline" onclick="addSensitiveWords()"> 提交 </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        // 添加敏感词
        function addSensitiveWords()
        {
            var words = $("#words").val();

            if (words == null) {
                bootbox.alert('敏感词不能为空');
                $("#words").focus();
                return false;
            }

            $.post("{{url('sensitiveWords/add')}}", {_token:'{{csrf_token()}}', words:words}, function(ret) {
                layer.msg(ret.message, {time:1000}, function() {
                    if (ret.status == 'success') {
                        window.location.reload();
                    }
                });
            });
        }

        // 删除敏感词
        function delWord(id)
        {
            layer.confirm('确定删除该敏感词？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('sensitiveWords/del')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }
    </script>
@endsection
