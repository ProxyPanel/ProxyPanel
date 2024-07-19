@extends('admin.table_layouts')
@push('css')
    <link href="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.marketing.push.title') }}</h3>
                @can('admin.marketing.add')
                    <div class="panel-actions">
                        <button class="btn btn-primary disabled" data-toggle="modal" data-target="#send_modal" type="button">
                            <i class="icon wb-plus"></i>{{ trans('admin.marketing.push.send') }}</button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <select class="form-control" id="status" name="status" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('common.status.attribute') }}">
                            <option value="0">{{ trans('common.to_be_send') }}</option>
                            <option value="-1">{{ trans('common.failed') }}</option>
                            <option value="1">{{ trans('common.success') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                {{--                <div class="alert alert-info alert-dismissible" role="alert"> --}}
                {{--                    <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('common.close') }}"> --}}
                {{--                        <span aria-hidden="true">×</span></button> --}}
                {{--                    仅会推送给关注了您的消息通道的用户 @can('admin.system.index')<a href="{{route('admin.system.index')}}" class="alert-link" target="_blank">设置PushBear</a> @else 设置PushBear @endcan --}}
                {{--                </div> --}}
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
                        @foreach ($pushes as $push)
                            <tr>
                                <td> {{ $push->id }} </td>
                                <td> {{ $push->title }} </td>
                                <td> {{ $push->content }} </td>
                                <td> {{ $push->status_label }} </td>
                                <td> {{ $push->created_at }} </td>
                                <td> {{ $push->error }} </td>
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
                            {{ $pushes->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.marketing.add')
        <!-- 推送消息 -->
        <div class="modal fade" id="send_modal" data-focus-on="input:first" data-backdrop="static" data-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-lg modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ trans('admin.marketing.push.send') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" id="msg" style="display: none;"></div>
                        <form class="form-horizontal" action="#" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-2 control-label" for="title"> {{ trans('validation.attributes.title') }} </label>
                                        <div class="col-md-6">
                                            <input class="form-control" id="title" name="title" type="text" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-2 control-label" for="content"> {{ trans('validation.attributes.content') }} </label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="content" name="content" data-provide="markdown" data-iconlibrary="fa" rows="10"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                        <button class="btn btn-primary disabled" type="button" onclick="return send();">{{ trans('common.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@push('javascript')
    <script src="/assets/global/vendor/bootstrap-markdown/bootstrap-markdown.min.js"></script>
    <script src="/assets/global/vendor/marked/marked.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#status').selectpicker('val', @json(Request::query('status')));
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
                    url: '{{ route('admin.marketing.add') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        title: title,
                        content: $('#content').val()
                    },
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
                    complete: function() {},
                });
            }

            // 关闭modal触发
            $('#send_modal').on('hide.bs.modal', function() {
                window.location.reload();
            });
        @endcan
    </script>
@endpush
