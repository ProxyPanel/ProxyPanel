@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">编辑文章</h2>
            </div>
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            <div class="panel-body">
                <form action="{{route('admin.article.update', $article->id)}}" method="POST" enctype="multipart/form-data" class="form-horizontal">@method('PUT')@csrf
                    <div class="form-group row">
                        <label for="type" class="col-form-label col-md-2">类型</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1"
                                       {{$article->type === 1 ? 'checked' : ''}} disabled/>
                                <label for="type">文章</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2"
                                       {{$article->type === 2 ? 'checked' : ''}} disabled/>
                                <label for="type">公告</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="3"
                                       {{$article->type === 3 ? 'checked' : ''}} disabled/>
                                <label for="type">购买说明</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="4"
                                       {{$article->type === 4 ? 'checked' : ''}} disabled/>
                                <label for="type">使用教程</label>
                            </div>
                        </div>
                        <input name="type" value="{{$article->type}}" hidden/>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2">标题</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="title" value="{{$article->title}}"
                                   autofocus required/>
                            <input type="hidden" name="id" value="{{$article->id}}"/>
                        </div>
                    </div>
                    @if($article->type === 1)
                        <div class="form-group row">
                            <label class="col-form-label col-md-2" for="summary">简介</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="summary" value="{{$article->summary}}"/>
                            </div>
                        </div>
                    @endif
                    @if($article->type === 1 || $article->type === 3)
                        <div class="form-group row">
                            <label class="col-form-label col-md-2" for="sort">排序</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="sort" value="{{$article->sort}}" required/>
                                <span class="text-help"> 值越高显示时越靠前 </span>
                            </div>
                        </div>
                    @endif
                    @if($article->type !== 2)
                        <div class="form-group row">
                            <label class="col-form-label col-md-2" for="logo">LOGO/图标</label>
                            @if($article->type === 1)
                                <div class="col-md-4">
                                    <input type="file" name="logo" data-plugin="dropify" data-default-file={{asset($article->logo ?? '/assets/images/default.png')}} />
                                    <span class="text-help"> 推荐尺寸：100x75 </span>
                                </div>
                            @else
                                <div class="col-md-4">
                                    <div class="input-group">
                                        @if($article->logo)
                                            <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fa {{$article->logo}}" aria-hidden="true"></i></span>
                                            </div>
                                        @endif
                                        <input type="text" class="form-control" name="logo" value="{{$article->logo}}"/>
                                    </div>
                                    <span class="text-help"><a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">图标列表</a> | 格式： fa-windows</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="summernote">内容</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="content" data-plugin="summernote" rows="15">{!!$article->content!!}</textarea>
                        </div>
                    </div>
                    <div class="form-actions text-right">
                        <div class="btn-group">
                            <a href="{{route('admin.article.index', ['page'=>Request::input('page')])}}" class="btn btn-danger">
                                返 回
                            </a>
                            <button type="submit" class="btn btn-success">提 交</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/vendor/summernote/summernote.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/js/Plugin/summernote.js"></script>
@endsection
