@extends('user.layouts')
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
                <div class="portlet light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-question font-green"></i>
                            <span class="caption-subject bold font-green uppercase"> {{$ticket->title}} </span>
                        </div>
                        <div class="actions">
                            @if($ticket->status != 2)
                                <div class="btn-group btn-group-devided" data-toggle="buttons">
                                    <a class="btn red btn-outline sbold" data-toggle="modal" href="#closeTicket"> 关闭 </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-badge">
                                    <div class="timeline-icon">
                                        <i class="icon-user font-green-haze"></i>
                                    </div>
                                </div>
                                <div class="timeline-body">
                                    <div class="timeline-body-arrow"></div>
                                    <div class="timeline-body-head">
                                        <div class="timeline-body-head-caption">
                                            <span class="timeline-body-alerttitle font-blue-madison">{{trans('home.ticket_reply_me')}}</span>
                                            <span class="timeline-body-time font-grey-cascade"> {{$ticket->created_at}} </span>
                                        </div>
                                        <div class="timeline-body-head-actions"></div>
                                    </div>
                                    <div class="timeline-body-content" style="word-wrap: break-word;">
                                        <span class="font-grey-cascade"> {!! $ticket->content !!} </span>
                                    </div>
                                </div>
                            </div>
                            @if (!$replyList->isEmpty())
                                @foreach ($replyList as $reply)
                                    <div class="timeline-item">
                                        <div class="timeline-badge">
                                            @if($reply->user->is_admin)
                                                <img class="timeline-badge-userpic" src="/assets/images/avatar.png">
                                            @else
                                                <div class="timeline-icon">
                                                    <i class="icon-user font-green-haze"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="timeline-body">
                                            <div class="timeline-body-arrow"></div>
                                            <div class="timeline-body-head">
                                                <div class="timeline-body-head-caption">
                                                    @if($reply->user->is_admin)
                                                        <a href="javascript:;" class="timeline-body-title font-red-intense">{{trans('home.ticket_reply_master')}}</a>
                                                    @else
                                                        <span class="timeline-body-alerttitle font-blue-madison">{{trans('home.ticket_reply_me')}}</span>
                                                    @endif
                                                    <span class="timeline-body-time font-grey-cascade"> {{$reply->created_at}} </span>
                                                </div>
                                                <div class="timeline-body-head-actions"></div>
                                            </div>
                                            <div class="timeline-body-content" style="word-wrap: break-word;">
                                                <span class="font-grey-cascade"> {!! $reply->content !!} </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @if($ticket->status != 2)
                            <hr />
                            <div class="row">
                                <div class="col-md-12">
                                    <script id="editor" type="text/plain" style="padding-bottom:10px;"></script>
                                    <button type="button" class="btn blue" onclick="replyTicket()"> {{trans('home.ticket_reply_button')}} </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 关闭工单弹窗 -->
                <div class="modal fade" id="closeTicket" tabindex="-1" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">关闭工单</h4>
                            </div>
                            <div class="modal-body"> 您确定要关闭该工单吗？ </div>
                            <div class="modal-footer">
                                <button type="button" class="btn dark btn-outline" data-dismiss="modal">取消</button>
                                <button type="button" class="btn red" onclick="closeTicket()">确定</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/ueditor/ueditor.config.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/ueditor/ueditor.all.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript">
        @if($ticket->status != 2)
            // 百度富文本编辑器
            var ue = UE.getEditor('editor', {
                toolbars:[['source','undo','redo','bold','italic','underline','insertimage','insertvideo','lineheight','fontfamily','fontsize','justifyleft','justifycenter','justifyright','justifyjustify','forecolor','backcolor','link','unlink']],
                wordCount:true,                //关闭字数统计
                elementPathEnabled : false,    //是否启用元素路径
                maximumWords:300,              //允许的最大字符数
                initialContent:'',             //初始化编辑器的内容
                initialFrameWidth:null,        //初始化宽度
                autoClearinitialContent:false, //是否自动清除编辑器初始内容
            });
        @endif

        // 关闭工单
        function closeTicket() {
            $.ajax({
                type: "POST",
                url: "{{url('closeTicket')}}",
                async: true,
                data: {_token:'{{csrf_token()}}', id:'{{$ticket->id}}'},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('tickets')}}';
                        }
                    });
                }
            });
        }
      
        // 回复工单
        function replyTicket() {
            var content = UE.getEditor('editor').getContent();

            if (content == "" || content == undefined) {
                layer.alert('您未填写工单内容', {icon: 2, title:'提示'});
                return false;
            }
            
            layer.confirm('确定回复工单？', {icon: 3, title:'提示'}, function(index) {
                $.post("{{url('replyTicket')}}",{_token:'{{csrf_token()}}', id:'{{$ticket->id}}', content:content}, function(ret) {
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