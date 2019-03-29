@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('successMsg'))
                    <div class="alert alert-success">
                        <button class="close" data-close="alert"></button>
                        {{Session::get('successMsg')}}
                    </div>
                @endif
                @if (Session::has('errorMsg'))
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <strong>错误：</strong> {{Session::get('errorMsg')}}
                    </div>
                @endif

                <div class="note note-danger">
                    <p>公告：仅展示最后一条有效的</p>
                    <p>购买说明：标题随意填，简介不用填</p>
                    <p>使用教程：标题随意填，简介不用填，排序1=Mac,2=Windows,3=Linux,4=iOS,5=Android,6=Games</p>
                </div>

                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark sbold uppercase">添加文章</span>
                        </div>
                        <div class="actions"></div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="{{url('admin/addArticle')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="type" class="control-label col-md-2">类型</label>
                                    <div class="col-md-6">
                                        <div class="mt-radio-inline">
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="1" checked> 文章
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="2"> 公告
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="3"> 购买说明
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="4"> 使用教程
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-2">标题</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="title" id="title" placeholder="" autofocus required>
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-2">简介</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="summary" id="summary" placeholder="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-2">排序</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="sort" id="sort" value="0" required />
                                        <span class="help-block"> 值越高显示时越靠前 </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-2">LOGO</label>
                                    <div class="col-md-6">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                <img src="/assets/images/noimage.png" alt="" /> </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                            <div>
                                                <span class="btn default btn-file">
                                                    <span class="fileinput-new"> 选择 </span>
                                                    <span class="fileinput-exists"> 更换 </span>
                                                    <input type="file" name="logo" id="logo">
                                                </span>
                                                <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                            </div>
                                        </div>
                                        <span class="help-block"> 推荐尺寸：100x75 </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-2">内容</label>
                                    <div class="col-md-10">
                                        <script id="editor" type="text/plain" style="height:400px;"></script>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-2 col-md-10">
                                        <button type="submit" class="btn green">提 交</button>
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
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/js/ueditor/ueditor.config.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/ueditor/ueditor.all.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript">
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

        // ajax同步提交
        {{--function do_submit() {--}}
            {{--var _token = '{{csrf_token()}}';--}}
            {{--var title = $('#title').val();--}}
            {{--var type = $("input:radio[name='type']:checked").val();--}}
            {{--var author = $('#author').val();--}}
            {{--var summary = $('#summary').val();--}}
            {{--var content = UE.getEditor('editor').getContent();--}}
            {{--var sort = $('#sort').val();--}}

            {{--$.ajax({--}}
                {{--type: "POST",--}}
                {{--url: "{{url('admin/addArticle')}}",--}}
                {{--async: false,--}}
                {{--data: {_token:_token, title: title, type:type, author:author, summary:summary, content:content, sort:sort},--}}
                {{--dataType: 'json',--}}
                {{--success: function (ret) {--}}
                    {{--layer.msg(ret.message, {time:1000}, function() {--}}
                        {{--if (ret.status == 'success') {--}}
                            {{--window.location.href = '{{url('admin/articleList')}}';--}}
                        {{--}--}}
                    {{--});--}}
                {{--}--}}
            {{--});--}}

            {{--return false;--}}
        {{--}--}}
    </script>
@endsection