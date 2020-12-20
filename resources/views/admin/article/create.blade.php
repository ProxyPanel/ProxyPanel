@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">添加文章</h2>
            </div>
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            <div class="panel-body">
                <form action="{{route('admin.article.store')}}" class="form-horizontal" enctype="multipart/form-data" method="post">@csrf
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="type">类型</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1" checked/>
                                <label for="type">文章</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2"/>
                                <label for="type">公告</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="3" disabled/>
                                <label for="type">购买说明</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="4" disabled/>
                                <label for="type">使用教程</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="title">标题</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}" autofocus required/>
                        </div>
                    </div>
                    <div class="form-group row" id="summary">
                        <label class="col-form-label col-md-2" for="summary">简介</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="summary" id="summary" value="{{ old('summary') }}"/>
                        </div>
                    </div>
                    <div class="form-group row" id="sort">
                        <label class="col-form-label col-md-2" for="sort">排序</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" name="sort" id="sort" value="{{ old('sort')??0 }}" required/>
                            <span class="text-help"> 值越高显示时越靠前 </span>
                        </div>
                    </div>
                    <div class="form-group row" id="all_logo">
                        <label class="col-form-label col-md-2" for="logo">LOGO</label>
                        <div class="col-md-4" id="icon" style="display: none;">
                            <input type="text" name="logo" id="logo" class="form-control" value="{{ old('logo') }}"/>
                            <span class="text-help"><a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">图标列表</a> | 格式： fa-windows</span>
                        </div>

                        <div class="col-md-4" id="logoUpload">
                            <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="{{asset('/assets/images/default.png')}}"/>
                            <span class="text-help"> 推荐尺寸：100x75 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="summernote">内容</label>
                        <div class="col-md-10">
                            <textarea class="form-control" name="content" id="summernote" data-plugin="summernote" rows="15"> {{ old('content') }} </textarea>
                        </div>
                    </div>
                    <div class="form-actions text-right">
                        <div class="btn-group">
                            <a href="{{route('admin.article.index')}}" class="btn btn-danger">返 回</a>
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
    <script>
        @if(old('type'))
        $(document).ready(function() {
          $("input[name='type'][value='{{old('type')}}']").click();
        });
        @endif

        $('input:radio[name=\'type\']').on('change', function() {
          const summary = $('#summary');
          const sort = $('#sort');
          const allLogo = $('#all_logo');
          const icon = $('#icon');
          const logoUpload = $('#logoUpload');
          summary.hide();
          sort.hide();
          allLogo.show();
          switch (parseInt($(this).val())) {
            case 1:
              summary.show();
              sort.show();
              icon.hide();
              logoUpload.show();
              break;
            case 2:
              allLogo.hide();
              break;
            case 3:
              sort.show();
              icon.show();
              logoUpload.hide();
              break;
            case 4:
              icon.show();
              logoUpload.hide();
              break;
            default:
          }
        });
    </script>
@endsection
