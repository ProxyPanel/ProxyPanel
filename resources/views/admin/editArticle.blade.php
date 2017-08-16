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
                <a href="{{url('admin/articleList')}}">文章管理</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:;">编辑文章</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="tab-pane">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    <form action="{{url('admin/editArticle')}}" method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return do_submit();">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="portlet-body">
                                        <div class="form-group">
                                            <label for="title" class="col-md-3 control-label">标题</label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" name="title" value="{{$article->title}}" id="title" placeholder="" autofocus required>
                                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="sort" class="col-md-3 control-label">排序</label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" name="sort" value="{{$article->sort}}" id="sort" value="0" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="content" class="col-md-3 control-label">内容</label>
                                            <div class="col-md-8">
                                                <script id="editor" type="text/plain" style="width:680px;height:400px;">
                                                    {!! $article->content !!}
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-5 col-md-4">
                                    <button type="submit" class="btn green">提 交</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/js/ueditor/ueditor.config.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/ueditor/ueditor.all.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript">
        // 百度富文本编辑器
        var ue = UE.getEditor('editor', {
            toolbars:[['source','undo','redo','bold','italic','underline','insertimage','insertvideo','map','lineheight','fontfamily','fontsize','justifyleft','justifycenter','justifyright','justifyjustify','forecolor','backcolor','link','unlink','date','time','fullscreen']],
            wordCount:true,                //关闭字数统计
            elementPathEnabled : false,    //是否启用元素路径
            maximumWords:300,              //允许的最大字符数
            initialContent:'',             //初始化编辑器的内容
            initialFrameWidth:960,         //初始化宽度
            autoClearinitialContent:false, //是否自动清除编辑器初始内容
        });

        // ajax同步提交
        function do_submit() {
            var _token = '{{csrf_token()}}';
            var id = '{{$article->id}}';
            var title = $('#title').val();
            var sort = $('#sort').val();
            var content = UE.getEditor('editor').getContent();

            $.ajax({
                type: "POST",
                url: "{{url('admin/editArticle')}}",
                async: false,
                data: {_token:_token, id:id, title: title, sort:sort, content:content},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status == 'success') {
                        bootbox.alert(ret.message, function () {
                            window.location.href = '{{url('admin/articleList')}}';
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