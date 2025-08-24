@extends('admin.layouts')
@section('css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/material-design/material-design.min.css" rel="stylesheet">
    <link href="/assets/custom/articles.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            @if ($article->logo)
                                <img class="mr-10" src="{{ asset($article->logo) }}" alt="logo" style="height: 32px" />
                            @endif
                            {{ $article->title }}
                            @if ($article->category)
                                <sub class="ml-20">{{ $article->category }}</sub>
                            @endif
                        </h3>
                        <div class="panel-actions"><code>{{ $article->created_at }}</code></div>
                    </div>
                    <div class="panel-body pt-0 pb-60">
                        <div style="padding: 10px;">{!! $article->content !!}</div>
                        <div class="panel-footer text-right">
                            <a class="btn btn-primary" href="{{ route('admin.article.index') }}">{{ trans('common.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        function fetch(id) {
            const articleElement = document.getElementById(`article_B${id}`);
            if (!articleElement.innerHTML) {
                ajaxGet(jsRoute('{{ route('admin.article.show', 'PLACEHOLDER') }}', id), {}, {
                    loadingSelector: "#loading_article",
                    success: function(ret) {
                        articleElement.innerHTML = ret.content;
                    }
                });
            }

            return false;
        }

        // 更换订阅地址
        function exchangeSubscribe() {
            showConfirm({
                title: '{{ trans('common.warning') }}',
                html: `{!! trans('user.subscribe.exchange_warning') !!}`,
                icon: "warning",
                onConfirm: function() {
                    ajaxPost('{{ route('changeSub') }}');
                }
            });
        }

        $(document).on('click', '.mt-clipboard', function(e) {
            e.preventDefault();
            copyToClipboard($(this).text());
        });
    </script>
@endsection
