@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <x-ui.panel :title="trans(isset($article) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.article.attribute')])">
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>
            <x-admin.form.container :route="isset($article) ? route('admin.article.update', $article) : route('admin.article.store')" :method="isset($article) ? 'PUT' : 'POST'" enctype="true">
                <x-admin.form.radio-group name="type" :label="trans('model.common.type')" :options="[1 => trans('admin.article.type.knowledge'), 2 => trans('admin.article.type.announcement')]" />
                <x-admin.form.input name="title" :label="ucfirst(trans('validation.attributes.title'))" required autofocus />
                <x-admin.form.input name="category" container="form-group row article" :label="trans('model.article.category')" attribute="list=category_list" :help="trans('admin.article.category_hint')" />
                <datalist id="category_list">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </datalist>
                <x-admin.form.select name="language" :label="trans('model.article.language')">
                    @foreach (config('common.language') as $key => $value)
                        <option data-content="<i class='fi fi-{{ $value[1] }}' aria-hidden='true'></i> {{ $value[0] }}" value="{{ $key }}">
                        </option>
                    @endforeach
                </x-admin.form.select>
                <x-admin.form.input class="form-group row article" name="sort" type="number" :label="trans('model.common.sort')" min="0" max="255" required
                                    :help="trans('admin.sort_asc')" />
                <x-admin.form.skeleton name="logo" :label="trans('model.article.logo')">
                    <input id="logo" name="logo" data-plugin="dropify" data-default-file="{{ asset($article->logo ?? '/assets/images/default.png') }}"
                           type="file" />
                    <input class="form-control" id="logoUrl" type="text" placeholder="{{ trans('admin.article.logo_placeholder') }}">
                </x-admin.form.skeleton>
                <x-admin.form.textarea name="content" :label="ucfirst(trans('validation.attributes.content'))" label_grid="col-xxl-1 col-lg-2" input_grid="col-lg-10" />
                <div class="form-actions text-right">
                    <div class="btn-group">
                        <a class="btn btn-danger" href="{{ route('admin.article.index') }}">{{ trans('common.back') }}</a>
                        <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                    </div>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/custom/tinymce/tinymce.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        // 初始化 TinyMCE
        tinymce.init({
            selector: "textarea", // change this value according to your HTML
            license_key: "gpl",
            plugins: "advlist autolink autoresize autosave code emoticons help image importcss link lists media " +
                "preview quickbars searchreplace table visualblocks visualchars wordcount",
            toolbar: "restoredraft undo redo | styles | bold italic forecolor backcolor emoticons| alignleft aligncenter alignright alignjustify" +
                " | bullist numlist outdent indent | link image media",
            menubar: "view edit insert format table tools help",
            link_default_target: "_blank",
            quickbars_insert_toolbar: "quicktable image media",
            quickbars_selection_toolbar: "bold italic underline | blocks | bullist numlist | blockquote quicklink",
            extended_valid_elements: "button[onclick|class],i[class|aria-hidden]", // Allow more attributes for <a>
            language: '{{ app()->getLocale() !== 'ko' ? app()->getLocale() : 'ko_KR' }}',
            content_css: [
                "/assets/bundle/app.min.css",
                "/assets/global/fonts/font-awesome/css/all.min.css",
                "/assets/global/fonts/material-design/material-design.min.css",
                "/assets/global/fonts/web-icons/web-icons.min.css",
                "https://fonts.loli.net/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap",
                "/assets/custom/articles.min.css",
                "/assets/custom/tinymce.min.css"
            ],
            min_height: 500,
            max_height: 800
        });

        // 类型切换处理
        $("input:radio[name=type]").on("change", function() {
            $(".article").toggle($(this).val() === "1");
        });

        // 初始化 Dropify
        const $logo = $("#logo");
        const $logoUrl = $("#logoUrl");
        let logoCleared = false;
        const dropify = $logo.dropify().data("dropify");

        // 添加 Dropify clear 事件处理
        $logo.on("dropify.afterClear", function() {
            logoCleared = true;
        });

        // Logo URL 输入处理
        $logoUrl.on("input", function() {
            const imageUrl = $logoUrl.val();
            if (imageUrl) { //updateDropifyPreview
                dropify.settings.defaultFile = imageUrl;
                dropify.destroy();
                $logo.dropify({
                    defaultFile: imageUrl
                }).data("dropify").init();
            } else {
                dropify.resetPreview();
                dropify.clearElement();
            }
        });

        // 表单提交处理
        $("form").on("submit", function() {
            const logoUrl = $logoUrl.val();
            if (logoUrl || (logoCleared && !$logo.val())) {
                $logo.attr("type", "text").val(logoUrl || null);
            }
        });

        $(document).ready(function() {
            let articleData = {
                type: 1
            };
            @isset($article)
                articleData = @json($article);
            @endisset

            autoPopulateForm(articleData, {
                skipFields: ['logo']
            })
        });
    </script>
@endsection
