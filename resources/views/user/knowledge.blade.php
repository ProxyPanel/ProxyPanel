@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/material-design/material-design.min.css" rel="stylesheet">
    <link href="/assets/custom/articles.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ __('user.knowledge.title') }}</h1>
    </div>
    <div class="page-content container-fluid">
        @if ($knowledge->isNotEmpty())
            <div class="row">
                <div class="offset-xxl-1 col-xxl-2 col-xl-3 offset-lg-0 col-lg-4 offset-sm-2 col-sm-8">
                    <div class="panel">
                        <div class="list-group" role="tablist">
                            @foreach ($knowledge as $category => $articles)
                                @php $str = string_urlsafe($category) @endphp
                                <a class="list-group-item @if ($loop->first) list-group-item-action active @endif" data-toggle="tab"
                                   href="#{{ $str }}" role="tab" aria-controls="{{ $str }}">{{ $category }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8 col-xl-9 col-lg-8 col-md-12">
                    <div class="panel">
                        <div class="panel-heading progress" id="loading_article" style="display: none;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                 style="width: 100%">
                                <span class="sr-only">100% Complete</span>
                            </div>
                        </div>
                        <div class="panel-body pt-30">
                            <div class="tab-content">
                                @foreach ($knowledge as $category => $articles)
                                    <div class="tab-pane animation-fade @if ($loop->first) active @endif" id="{{ string_urlsafe($category) }}"
                                         role="tabpanel">
                                        <div class="panel-group panel-group-simple panel-group-continuous" role="tablist" aria-multiselectable="true">
                                            @if ($loop->first)
                                                <div class="panel">
                                                    <div class="panel-heading" id="question_1" role="tab">
                                                        <a class="panel-title cyan-600" data-toggle="collapse" href="#answer_1" aria-controls="answer_1"
                                                           aria-expanded="true">
                                                            <i class="icon wb-link" aria-hidden="true"></i>{{ trans('user.subscribe.link') }}
                                                        </a>
                                                    </div>
                                                    <div class="panel-collapse collapse show" id="answer_1" role="tabpanel" aria-labelledby="question_1">
                                                        <div class="panel-body">
                                                            @if ($subscribe['status'])
                                                                <x-alert type="warning" :message="trans('user.subscribe.tips')" />
                                                                <div class="input-group">
                                                                    <input class="form-control" id="sub_link" type="text" value="{{ $subUrl }}" />
                                                                    <div class="input-group-btn btn-group" role="group">
                                                                        @if (count($subType) > 1)
                                                                            <div class="btn-group" role="group">
                                                                                <button class="btn btn-primary dropdown-toggle" id="sublink"
                                                                                        data-toggle="dropdown" type="button" aria-expanded="false">
                                                                                    {{ __('user.subscribe.custom') }}
                                                                                </button>
                                                                                <div class="dropdown-menu" role="menu" aria-labelledby="sublink">
                                                                                    @if (in_array('ss', $subType, true))
                                                                                        <a class="dropdown-item" role="menuitem"
                                                                                           onclick="linkManager('0')">{{ __('user.subscribe.ss_only') }}</a>
                                                                                    @endif
                                                                                    @if (in_array('ssr', $subType, true))
                                                                                        <a class="dropdown-item" role="menuitem"
                                                                                           onclick="linkManager('1')">{{ __('user.subscribe.ssr_only') }}</a>
                                                                                    @endif
                                                                                    @if (in_array('v2', $subType, true))
                                                                                        <a class="dropdown-item" role="menuitem"
                                                                                           onclick="linkManager('2')">{{ __('user.subscribe.v2ray_only') }}</a>
                                                                                    @endif
                                                                                    @if (in_array('trojan', $subType, true))
                                                                                        <a class="dropdown-item" role="menuitem"
                                                                                           onclick="linkManager('3')">{{ __('user.subscribe.trojan_only') }}</a>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        <button class="btn btn-outline-info" onclick="exchangeSubscribe();">
                                                                            <i class="icon wb-refresh" aria-hidden="true"></i>
                                                                            {{ trans('common.change') }}</button>
                                                                        <button class="btn btn-outline-info mt-clipboard" data-clipboard-action="copy"
                                                                                data-clipboard-target="#sub_link">
                                                                            <i class="icon wb-copy" aria-hidden="true"></i>
                                                                            {{ trans('common.copy.attribute') }}</button>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <x-alert type="danger" :message="__($subscribe['ban_desc'])" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @foreach ($articles as $article)
                                                <div class="panel">
                                                    <div class="panel-heading"id="article_Q{{ $article->id }}">
                                                        <a class="panel-title collapsed" data-toggle="collapse" href="#article_A{{ $article->id }}"
                                                           role="tab" aria-controls="article_A{{ $article->id }}" aria-expanded="false"
                                                           style="display: flex;" onclick="getArticle('{{ $article->id }}')">
                                                            @if ($article->logo)
                                                                <img class="mr-5" src="{{ asset($article->logo) }}" alt=""
                                                                     style="height: 36px; align-self: center" loading="lazy" />
                                                            @endif
                                                            <h4 style="margin-top: 11px">{{ $article->title }}</h4>
                                                        </a>
                                                        <div class="panel-collapse collapse" id="article_A{{ $article->id }}" role="tabpanel"
                                                             aria-labelledby="article_Q{{ $article->id }}">
                                                            <div class="panel-body" id="load_article_{{ $article->id }}"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@section('javascript')
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
    <script src="/assets/global/vendor/asprogress/jquery-asProgress.min.js"></script>
    <script src="/assets/global/js/Plugin/responsive-tabs.js"></script>
    <script src="/assets/global/js/Plugin/tabs.js"></script>
    <script src="/assets/custom/jump-tab.js"></script>
    <script src="/assets/global/js/Plugin/asprogress.js"></script>
    <script>
        const clipboard = new ClipboardJS(".mt-clipboard");

        function getArticle(id) {
            if (!document.getElementById("load_article_" + id).innerHTML) {
                $.ajax({
                    method: "GET",
                    url: '{{ route('knowledge.show', '') }}/' + id,
                    beforeSend: function() {
                        $("#loading_article").show();
                    },
                    success: function(ret) {
                        document.getElementById("load_article_" + id).innerHTML = ret.content;
                    },
                    complete: function() {
                        $("#loading_article").hide();
                    }
                });
            }

            return false;
        }

        function linkManager($type) {
            $("#sub_link").val('{{ $subUrl }}?type=' + $type);
            return false;
        }

        // 更换订阅地址
        function exchangeSubscribe() {
            swal.fire({
                title: '{{ trans('common.warning') }}',
                text: '{{ trans('user.subscribe.exchange_warning') }}',
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: '{{ trans('common.close') }}',
                confirmButtonText: '{{ trans('common.confirm') }}'
            }).then((result) => {
                if (result.value) {
                    $.post('{{ route('changeSub') }}', {
                        _token: '{{ csrf_token() }}'
                    }, function(ret) {
                        if (ret.status === "success") {
                            swal.fire({
                                title: ret.message,
                                icon: "success",
                                timer: 1000,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            swal.fire({
                                title: ret.message,
                                icon: "error"
                            }).then(() => window.location.reload());
                        }
                    });
                }
            });
        }

        clipboard.on("success", function() {
            swal.fire({
                title: '{{ trans('common.copy.success') }}',
                icon: "success",
                timer: 1300,
                showConfirmButton: false
            });
        });
        clipboard.on("error", function() {
            swal.fire({
                title: '{{ trans('common.copy.failed') }}',
                icon: "error",
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>
@endsection
