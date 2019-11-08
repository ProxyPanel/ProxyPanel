@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/dropify/dropify.min.css">
    <link rel="stylesheet" href="/assets/global/vendor/summernote/summernote.min.css">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">编辑文章</h2>
            </div>
            @if (Session::has('successMsg'))
                <div class="alert alert-success">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    {{Session::get('successMsg')}}
                </div>
            @endif
            @if (Session::has('errorMsg'))
                <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    <strong>错误：</strong> {{Session::get('errorMsg')}}
                </div>
        @endif
        <!-- BEGIN PORTLET-->
            <div class="panel-body">
                <form action="/admin/editArticle" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group row">
                        <label for="type" class="col-form-label col-md-2">类型</label>
                        <ul class="col-md-9 list-unstyled list-inline">
                            <li class="list-inline-item">
                                <div class="radio-custom radio-primary">
                                    <input type="radio" name="type" value="{{$article->type}}" checked>
                                    <label>
                                        @switch($article->type)
                                            @case(1)
                                            文章
                                            @break
                                            @case(2)
                                            公告
                                            @break
                                            @case(3)
                                            购买说明
                                            @break
                                            @case(4)
                                            使用教程
                                            @break
                                            @default
                                        @endswitch
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2">标题</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="title" value="{{$article->title}}" id="title" autofocus required/>
                            <input type="hidden" name="id" value="{{$article->id}}">
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                        </div>
                    </div>
                    @if($article->type != '4' && $article->type != '2')
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">简介</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="summary" value="{{$article->summary}}" id="summary" required/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">排序</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="sort" id="sort" value="{{$article->sort}}" required/>
                                <span class="text-help"> 值越高显示时越靠前 </span>
                            </div>
                        </div>
                    @endif
                    @if($article->type != '2')
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">LOGO/图标</label>
                            @if($article->type == '4')
                                <div class="col-md-6 input-group">
                                    @if($article->logo)
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa {{$article->logo}}" aria-hidden="true"></i></span>
                                        </div>
                                    @endif
                                    <input type="txt" class="form-control" id="logo" name="logo" value="{{$article->logo}}"/>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file=@if($article->logo) {{$article->logo}} @else /assets/images/noimage.png @endif />
                                    <button type="submit" class="btn btn-success float-right mt-10"> 提交</button>
                                    <span class="text-help"> 推荐尺寸：100x75 </span>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-form-label col-md-2">内容</label>
                        <textarea class="col-md-10" name="content" id="summernote" data-plugin="summernote" rows="15"> </textarea>
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
    <script src="/assets/global/vendor/summernote/summernote.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/js/Plugin/summernote.js"></script>
@endsection
