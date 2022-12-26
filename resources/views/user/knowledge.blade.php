@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/fonts/material-design/material-design.min.css" rel="stylesheet">
    <style>
        ol > li {
            margin-bottom: 8px;
        }
    </style>
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title">问题解决库</h1>
    </div>
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-xxl-2 col-lg-4 col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="list-group faq-list" role="tablist">
                            <a class="list-group-item list-group-item-action active" data-toggle="tab"
                               href="#basic" aria-controls="basic" role="tab">基础</a>
                            @foreach($knowledges as $category => $articles)
                                <a class="list-group-item list-group-item-action" data-toggle="tab"
                                   href="#{{$category}}" aria-controls="{{$category}}" role="tab">{{$category}}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-8 col-lg-8 col-md-12">
                <div class="panel">
                    <div class="panel-heading progress" id="loading_article" style="display: none;">
                        <div class="progress-bar progress-bar-striped active" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%" role="progressbar">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                    <div class="panel-body pt-30">
                        <div class="tab-content">
                            <div class="tab-pane animation-fade active" id="basic" role="tabpanel">
                                <div class="panel-group panel-group-simple panel-group-continuous" id="category_basic" aria-multiselectable="true" role="tablist">
                                    <div class="panel">
                                        <div class="panel-heading" id="question_1" role="tab">
                                            <a class="panel-title cyan-600" aria-controls="answer_1" aria-expanded="true" data-toggle="collapse" href="#answer_1"
                                               data-parent="#category_basic">
                                                <i class="icon wb-link" aria-hidden="true"></i>{{trans('user.subscribe.link')}}
                                            </a>
                                        </div>
                                        <div class="panel-collapse collapse show" id="answer_1" aria-labelledby="question_1" role="tabpanel">
                                            <div class="panel-body">
                                                @if($subStatus)
                                                    <x-alert type="warning" :message="trans('user.subscribe.tips')"/>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="sub_link" value="{{$subUrl}}"/>
                                                        <div class="input-group-btn btn-group" role="group">
                                                            @if(count($subType) > 1)
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-primary dropdown-toggle" id="sublink" data-toggle="dropdown"
                                                                            aria-expanded="false">
                                                                        自定义订阅
                                                                    </button>
                                                                    <div class="dropdown-menu" aria-labelledby="sublink" role="menu">
                                                                        @if(in_array('ss', $subType, true))
                                                                            <a class="dropdown-item" onclick="linkManager('0')" role="menuitem">只订阅SS</a>
                                                                        @endif
                                                                        @if(in_array('ssr', $subType, true))
                                                                            <a class="dropdown-item" onclick="linkManager('1')" role="menuitem">只订阅SSR (包含ss)</a>
                                                                        @endif
                                                                        @if(in_array('v2', $subType, true))
                                                                            <a class="dropdown-item" onclick="linkManager('2')" role="menuitem">只订阅V2Ray</a>
                                                                        @endif
                                                                        @if(in_array('trojan', $subType, true))
                                                                            <a class="dropdown-item" onclick="linkManager('3')" role="menuitem">只订阅Trojan</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <button class="btn btn-outline-info" onclick="exchangeSubscribe();">
                                                                <i class="icon wb-refresh" aria-hidden="true"></i>
                                                                {{trans('common.replace')}}</button>
                                                            <button class="btn btn-outline-info mt-clipboard" data-clipboard-action="copy" data-clipboard-target="#sub_link">
                                                                <i class="icon wb-copy" aria-hidden="true"></i>
                                                                {{trans('common.copy.attribute')}}</button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <x-alert type="danger" :message="trans('user.subscribe.baned')"/>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @foreach($knowledges as $category => $articles)
                                @php
                                    $outLoop = $loop->iteration;
                                @endphp
                                <div class="tab-pane animation-fade" id="{{$category}}" role="tabpanel">
                                    <div class="panel-group panel-group-simple panel-group-continuous" id="category_{{$outLoop}}" aria-multiselectable="true" role="tablist">
                                        @foreach ($articles as $article)
                                            <div class="panel">
                                                <div class="panel-heading" id="article_Q{{$article->id}}" role="tab">
                                                    <a class="panel-title" onclick="fetch('{{$article->id}}')" aria-controls="article_A{{$article->id}}"
                                                       aria-expanded="false"
                                                       data-toggle="collapse" href="#article_A{{$article->id}}" data-parent="#category_{{$outLoop}}">
                                                        {{$article->title}}
                                                    </a>
                                                    <div class="panel-collapse" id="article_A{{$article->id}}" aria-labelledby="article_Q{{$article->id}}" role="tabpanel">
                                                        <div class="panel-body" id="article_B{{$article->id}}"></div>
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
      const clipboard = new ClipboardJS('.mt-clipboard');

      function fetch(id) {
        if (!document.getElementById('article_B' + id).innerHTML) {
          $.ajax({
            method: 'GET',
            url: '{{route('article', '')}}/' + id,
            beforeSend: function() {
              $('#loading_article').show();
            },
            success: function(ret) {
              document.getElementById('article_B' + id).innerHTML = ret.content;
            },
            complete: function() {
              $('#loading_article').hide();
            },
          });
        }

        return false;
      }

      function linkManager($type) {
        $('#sub_link').val('{{$subUrl}}?type=' + $type);
        return false;
      }

      // 更换订阅地址
      function exchangeSubscribe() {
        swal.fire({
          title: '{{trans('common.warning')}}',
          text: '{{trans('user.subscribe.exchange_warning')}}',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('changeSub')}}', {_token: '{{csrf_token()}}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }

      clipboard.on('success', function() {
        swal.fire({
          title: '{{trans('common.copy.success')}}',
          icon: 'success',
          timer: 1300,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '{{trans('common.copy.failed')}}',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });
    </script>
@endsection
