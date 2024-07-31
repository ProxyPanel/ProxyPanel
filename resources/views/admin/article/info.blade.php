@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($article) ? trans('admin.action.edit_item', ['attribute' => trans('model.article.attribute')]) : trans('admin.action.add_item', ['attribute' => trans('model.article.attribute')]) }}
                </h2>
            </div>
            @if ($errors->any())
                <x-alert type="danger" :message="$errors->all()" />
            @endif
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')" />
            @endif
            <div class="panel-body">
                <form class="form-horizontal" action="{{ isset($article) ? route('admin.article.update', $article) : route('admin.article.store') }}"
                      enctype="multipart/form-data" method="post">@csrf
                    @isset($article)
                        @method('PUT')
                    @endisset
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="type"> {{ trans('model.common.type') }} </label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input name="type" type="radio" value="1" checked />
                                <label for="type">{{ trans('admin.article.type.knowledge') }}</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input name="type" type="radio" value="2" />
                                <label for="type">{{ trans('admin.article.type.announcement') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="title"> {{ ucfirst(trans('validation.attributes.title')) }} </label>
                        <div class="col-md-4">
                            <input class="form-control" id="title" name="title" type="text" autofocus required />
                        </div>
                    </div>
                    <div class="form-group row article">
                        <label class="col-form-label col-md-2" for="category"> {{ trans('model.article.category') }} </label>
                        <div class="col-md-4">
                            @if (isset($categories))
                                <input class="form-control" id="category" name="category" type="text" list="category_list" />
                                <datalist id="category_list">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->category }}">{{ $category->category }}</option>
                                    @endforeach
                                </datalist>
                            @else
                                <input class="form-control" id="category" name="category" type="text" />
                            @endif
                            <span class="text-help"> {{ trans('admin.article.category_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="language"> {{ trans('model.article.language') }} </label>
                        <div class="col-md-4">
                            <select class="form-control" id="language" name="language" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                @foreach (config('common.language') as $key => $value)
                                    <option value="{{ $key }}">
                                        <i class="fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                                        <span style="padding: inherit;">{{ $value[0] }}</span>
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row article">
                        <label class="col-form-label col-md-2" for="sort"> {{ trans('model.common.sort') }} </label>
                        <div class="col-md-2">
                            <input class="form-control" id="sort" name="sort" type="number" value="10" min="0" max="255" required />
                            <span class="text-help"> {{ trans('admin.sort_asc') }} </span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="logo"> {{ trans('model.article.logo') }} </label>
                        <div class="col-md-4">
                            <input id="logo" name="logo" data-plugin="dropify"
                                   data-default-file="{{ asset($article->logo ?? '/assets/images/default.png') }}" type="file" />
                            <input class="form-control" id="logoUrl" type="text" placeholder="{{ trans('admin.article.logo_placeholder') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-2" for="content"> {{ ucfirst(trans('validation.attributes.content')) }} </label>
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
                            <a class="btn btn-danger" href="{{ route('admin.article.index') }}">{{ trans('common.back') }}</a>
                            <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
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
        $(document).ready(function() {
            const $type = $('input:radio[name="type"]')
            const $logo = $('#logo')
            const $logoUrl = $('#logoUrl')
            let logoCleared = false

            // 初始化 TinyMCE
            tinymce.init({
                selector: 'textarea', // change this value according to your HTML
                license_key: 'gpl',
                plugins: 'advlist autolink autoresize autosave code emoticons help image importcss link lists media ' +
                    'preview quickbars searchreplace table visualblocks visualchars wordcount',
                toolbar: 'restoredraft undo redo | styles | bold italic forecolor backcolor emoticons| alignleft aligncenter alignright alignjustify' +
                    ' | bullist numlist outdent indent | link image media',
                menubar: 'view edit insert format table tools help',
                link_default_target: '_blank',
                quickbars_insert_toolbar: 'quicktable image media',
                quickbars_selection_toolbar: 'bold italic underline | blocks | bullist numlist | blockquote quicklink',
                extended_valid_elements: 'button[onclick|class],i[class|aria-hidden]', // Allow more attributes for <a>
                language: '{{ app()->getLocale() !== 'ko' ? app()->getLocale() : 'ko_KR' }}',
                content_css: [
                    '/assets/bundle/app.min.css',
                    '/assets/global/fonts/font-awesome/css/all.min.css',
                    '/assets/global/fonts/material-design/material-design.min.css',
                    '/assets/global/fonts/web-icons/web-icons.min.css',
                    'https://fonts.loli.net/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap',
                    '/assets/custom/articles.min.css',
                    '/assets/custom/tinymce.min.css'
                ],
                min_height: 500,
                max_height: 800,
            })

            // 初始化已有文章数据
            @isset($article)
                $type.filter(`[value="${@json($article->type)}"]`).click()
                $('#title').val(@json($article->title))
                $('#category').val(@json($article->category))
                $('#language').selectpicker('val', @json($article->language))
                $('#sort').val(@json($article->sort))
            @endisset

            // 初始化 Dropify
            const dropify = $logo.dropify().data('dropify')

            // 类型切换处理
            $type.on('change', function() {
                $('.article').toggle($(this).val() === '1')
            })

            // 添加 Dropify clear 事件处理
            $logo.on('dropify.afterClear', function() {
                logoCleared = true
            })

            // Logo URL 输入处理
            $logoUrl.on('input', handleLogoUrlInput)

            // 表单提交处理
            $('form').on('submit', handleFormSubmit)

            function handleLogoUrlInput() {
                const imageUrl = $logoUrl.val()
                if (imageUrl) {
                    updateDropifyPreview(imageUrl)
                } else {
                    dropify.resetPreview()
                    dropify.clearElement()
                }
            }

            function updateDropifyPreview(imageUrl) {
                dropify.settings.defaultFile = imageUrl
                dropify.destroy()
                $logo.dropify({
                    defaultFile: imageUrl
                }).data('dropify').init()
            }

            function handleFormSubmit() {
                const logoUrl = $logoUrl.val()
                if (logoUrl || (logoCleared && !$logo.val())) {
                    $logo.attr('type', 'text').val(logoUrl || null)
                }
            }
        })
    </script>
@endsection
