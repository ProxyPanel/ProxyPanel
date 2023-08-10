@extends('admin.layouts')
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
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{$article->title}} {!! $article->category ?'<sub class="ml-20">'.$article->category.'</sub>':'' !!}</h3>
                        <div class="panel-actions"><code>{{$article->created_at}}</code></div>
                    </div>
                    <div class="panel-body pt-0 pb-60">
                        <div style="padding: 10px;">{!! $article->content !!}</div>
                        <div class="panel-footer text-right">
                            <a href="{{route('admin.article.index')}}" class="btn btn-primary">{{ trans('common.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
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

      // 更换订阅地址
      function exchangeSubscribe() {
        swal.fire({
          title: '{{ trans('common.warning') }}',
          text: '{{ trans('user.subscribe.exchange_warning') }}',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('changeSub')}}', {_token: '{{csrf_token()}}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({
                  title: ret.message,
                  icon: 'success',
                  timer: 1000,
                  showConfirmButton: false,
                }).then(() => window.location.reload());
              } else {
                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
              }
            });
          }
        });
      }

      clipboard.on('success', function() {
        swal.fire({
          title: '{{ trans('common.copy.success') }}',
          icon: 'success',
          timer: 1300,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '{{ trans('common.copy.failed') }}',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });
    </script>
@endsection