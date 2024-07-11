@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.logs.notification') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-4">
                        <input class="form-control" name="username" type="text" value="{{ Request::query('username') }}"
                               placeholder="{{ trans('common.account') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="type" name="type" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.common.type') }}">
                            @foreach (config('common.notification.labels') as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-1 col-sm-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <a class="btn btn-danger" href="{{ route('admin.log.notify') }}">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.common.type') }}</th>
                            <th> {{ trans('model.notification.address') }}</th>
                            <th> {{ trans('validation.attributes.title') }}</th>
                            <th> {{ trans('validation.attributes.content') }}</th>
                            <th> {{ trans('model.notification.created_at') }}</th>
                            <th> {{ trans('model.notification.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notificationLogs as $log)
                            <tr>
                                <td> {{ $log->id }} </td>
                                <td> {{ $log->type_label }} </td>
                                <td> {{ $log->address }} </td>
                                <td> {{ $log->title }} </td>
                                <td class="text-break"> {{ $log->content }} </td>
                                <td> {{ $log->created_at }} </td>
                                <td>
                                    @if ($log->status < 0)
                                        <p class="badge badge-danger text-break font-size-14"> {{ $log->error }} </p>
                                    @elseif($log->status > 0)
                                        <labe class="badge badge-success">{{ trans('common.success') }}</labe>
                                    @else
                                        <span class="badge badge-default"> {{ trans('common.status.waiting_tobe_send') }} </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $notificationLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $notificationLogs->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    <script>
        $(document).ready(function() {
            $('#type').selectpicker('val', @json(Request::query('type')));
        });
    </script>
@endpush
