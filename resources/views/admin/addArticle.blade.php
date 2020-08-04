@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/dropify/dropify.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.css">
    <link rel="stylesheet" href="/assets/global/fonts/font-awesome/font-awesome.min.css">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">添加文章</h2>
            </div>
            @if($errors->any())
                <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    {{$errors->first()}}
                </div>
            @endif
            <div class="panel-body">
                <form action="/admin/addArticle" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="type">类型</label>
                        <ul class="col-md-9 list-unstyled list-inline">
                            <li class="list-inline-item">
                                <div class="radio-custom radio-primary">
                                    <input type="radio" name="type" value="1" checked>
                                    <label>文章</label>
                                </div>
                            </li>
                            <li class="list-inline-item">
                                <div class="radio-custom radio-primary">
                                    <input type="radio" name="type" value="2">
                                    <label>公告</label>
                                </div>
                            </li>
                            <li class="list-inline-item">
                                <div class="radio-custom radio-primary">
                                    <input type="radio" name="type" value="3" disabled>
                                    <label>购买说明</label>
                                </div>
                            </li>
                            <li class="list-inline-item">
                                <div class="radio-custom radio-primary">
                                    <input type="radio" name="type" value="4" disabled>
                                    <label>使用教程</label>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="title">标题</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="title" id="title" autofocus required/>
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                        </div>
                    </div>
                    <div class="form-group row" id="summary">
                        <label class="col-form-label col-md-2" for="summary">简介</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="summary" id="summary"/>
                        </div>
                    </div>
                    <div class="form-group row" id="sort">
                        <label class="col-form-label col-md-2" for="sort">排序</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="sort" id="sort" value="0" required/>
                            <span class="text-help offset-md-1"> 值越高显示时越靠前 </span>
                        </div>
                    </div>
                    <div class="form-group row" id="all_logo">
                        <label class="col-form-label col-md-2" for="logo">LOGO</label>
                        <div class="col-md-6" id="icon" style="display: none;">
                            <input type="txt" id="logo" class="form-control" name="logo" placeholder=""/>
                            <span class="text-help"><a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">图标列表</a> | 格式： fa-windows</span>
                        </div>
                        <div class="col-md-6" id="logoUpload">
                            <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="/assets/images/noimage.png"/>
                            <button type="submit" class="btn btn-success float-right mt-10"> 提交</button>
                            <span class="text-help offset-md-1"> 推荐尺寸：100x75 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="content">内容</label>
                        <textarea class="col-md-10" name="content" data-provide="markdown" data-iconlibrary="fa" rows="15"> </textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">提交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.js"></script>
    <script src="/assets/global/vendor/marked/marked.js"></script>
    <script src="/assets/global/vendor/to-markdown/to-markdown.js"></script>
@endsection
