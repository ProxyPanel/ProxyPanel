@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{isset($article)?'添加':'编辑'}} 文章</h2>
            </div>
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            <div class="panel-body">
                <form action="{{ isset($article) ? route('admin.article.update', $article) : route('admin.article.store')}}" class="form-horizontal" enctype="multipart/form-data"
                      method="post">@csrf
                    @isset($article)
                        @method('PUT')
                    @endisset
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="type"> 类 型 </label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1" checked/>
                                <label for="type">文章</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2"/>
                                <label for="type">公告</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="title"> 标 题 </label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="title" id="title" autofocus required/>
                        </div>
                    </div>
                    <div class="form-group row article">
                        <label class="col-form-label col-md-2" for="category"> 类 别 </label>
                        <div class="col-md-4">
                            @if(isset($categories))
                                <input type="text" class="form-control" list="category_list" id="category" name="category"/>
                                <datalist id="category_list">
                                    @foreach($categories as $category)
                                        <option value="{{$category->category}}">{{$category->category}}</option>
                                    @endforeach
                                </datalist>
                            @else
                                <input type="text" class="form-control" id="category" name="category"/>
                            @endif
                            <span class="text-help"> 同一类别将归类与同一文件夹下 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="language"> 语 言 </label>
                        <div class="col-md-4">
                            <select class="form-control" data-plugin="selectpicker" id="language" name="language" data-style="btn-outline btn-primary">
                                @foreach (config('common.language') as $key => $value)
                                    <option value="{{$key}}">
                                        <i class="fi fi-{{$value[1]}}"></i> <span style="padding: inherit;">{{$value[0]}}</span>
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row article">
                        <label class="col-form-label col-md-2" for="sort"> 排 序 </label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" name="sort" id="sort" value="10" min="0" max="255" required/>
                            <span class="text-help"> 值越高显示时越靠前 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="logo"> 头 图 </label>
                        <div class="col-md-4" id="logoUpload">
                            <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="{{asset($article->logo ?? '/assets/images/default.png')}}"/>
                            <span class="text-help"> 推荐尺寸：100x75 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="content"> 内 容 </label>
                        <div class="col-md-10">
                            <textarea class="form-control" name="content">
                                @isset($article)
                                    {!! $article->content !!}
                                @endisset
                            </textarea>
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
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/custom/tinymce/tinymce.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        @isset($article)
        $(document).ready(function() {
          $("input[name='type'][value='{{$article->type}}']").click();
          $('#title').val(@json($article->title));
          $('#category').val(@json($article->category));
          $('#language').selectpicker('val', '{{$article->language}}');
          $('#sort').val('{{$article->sort}}');
        });
        @endisset

        tinymce.init({
          selector: 'textarea',  // change this value according to your HTML
          plugins: 'advlist autolink autoresize autosave code emoticons help image importcss link lists media ' +
              'preview quickbars searchreplace table visualblocks visualchars wordcount',
          toolbar: 'restoredraft undo redo | styles | bold italic forecolor backcolor emoticons| alignleft aligncenter alignright alignjustify' +
              ' | bullist numlist outdent indent | link image media',
          menubar: 'view edit insert format table tools help',
          link_default_target: '_blank',
          quickbars_insert_toolbar: 'quicktable image media',
          quickbars_selection_toolbar: 'bold italic underline | blocks | bullist numlist | blockquote quicklink',
          extended_valid_elements: 'i[class|aria-hidden]',
          language: '{{app()->getLocale()}}',
          content_css: '/assets/bundle/app.min.css',
          min_height: 500,
          max_height: 800,
        });

        $('input:radio[name=\'type\']').on('change', function() {
          const article = $('.article');
          if ($(this).val() === '1') {
            article.show();
          } else {
            article.hide();
          }
        });
    </script>
@endsection
