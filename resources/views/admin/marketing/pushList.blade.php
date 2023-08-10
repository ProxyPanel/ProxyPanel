@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.marketing.push.title') }}</h3>
                @can('admin.marketing.add')
                    <div class="panel-actions">
                        <button type="button" class="btn btn-primary disabled" data-toggle="modal" data-target="#send_modal">
                            <i class="icon wb-plus"></i>{{ trans('admin.marketing.push.send') }}</button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <select class="form-control" name="status" id="status">
                            <option value="" hidden>{{ trans('common.status.attribute') }}</option>
                            <option value="0">{{ trans('common.to_be_send') }}</option>
                            <option value="-1">{{ trans('common.failed') }}</option>
                            <option value="1">{{ trans('common.success') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.marketing.push')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                {{--                <div class="alert alert-info alert-dismissible" role="alert">--}}
                {{--                    <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('common.close') }}">--}}
                {{--                        <span aria-hidden="true">×</span></button>--}}
                {{--                    仅会推送给关注了您的消息通道的用户 @can('admin.system.index')<a href="{{route('admin.system.index')}}" class="alert-link" target="_blank">设置PushBear</a> @else 设置PushBear @endcan--}}
                {{--                </div>--}}
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('validation.attributes.title') }}</th>
                        <th> {{ trans('validation.attributes.content') }}</th>
                        <th> {{ trans('admin.marketing.send_status') }}</th>
                        <th> {{ trans('admin.marketing.send_time') }}</th>
                        <th> {{ trans('admin.marketing.error_message') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pushes as $push)
                        <tr>
                            <td> {{$push->id}} </td>
                            <td> {{$push->title}} </td>
                            <td> {{$push->content}} </td>
                            <td> {{$push->status_label}} </td>
                            <td> {{$push->created_at}} </td>
                            <td> {{$push->error}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.marketing.push.counts', ['num' => $pushes->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$pushes->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.marketing.add')
        <!-- 推送消息 -->
        <div id="send_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-lg modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ trans('admin.marketing.push.send') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display: none;" id="msg"></div>
                        <form action="#" method="post" class="form-horizontal">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="title" class="col-md-2 control-label"> {{ trans('validation.attributes.title') }} </label>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title" id="title"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="content" class="col-md-2 control-label"> {{ trans('validation.attributes.content') }} </label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" rows="10" name="content" id="content" data-provide="markdown" data-iconlibrary="fa"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                        <button type="button" class="btn btn-primary disabled" onclick="return send();">{{ trans('common.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.js"></script>
    <script src="/assets/global/vendor/marked/marked.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#status').val({{Request::query('status')}});
      });

      @can('admin.marketing.add')
      // 发送通道消息
      function send() {
        const title = $('#title').val();

        if (title.trim() === '') {
          $('#msg').show().html('{{ trans('validation.filled', ['attribute' => trans('validation.attributes.title')]) }}');
          title.focus();
          return false;
        }

        $.ajax({
          url: '{{route('admin.marketing.add')}}',
          method: 'POST',
          data: {_token: '{{csrf_token()}}', title: title, content: $('#content').val()},
          beforeSend: function() {
            $('#msg').show().html('{{ trans('admin.creating') }}');
          },
          success: function(ret) {
            if (ret.status === 'fail') {
              $('#msg').show().html(ret.message);
              return false;
            }
            $('#send_modal').modal('hide');
          },
          error: function() {
            $('#msg').show().html('{{ trans('common.request_failed') }}');
          },
          complete: function() {
          },
        });
      }

      // 关闭modal触发
      $('#send_modal').on('hide.bs.modal', function() {
        window.location.reload();
      });
        @endcan
    </script>
@endsection
