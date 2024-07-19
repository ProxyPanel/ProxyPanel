@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {!! trans('admin.logs.ip_monitor') !!}
                </h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-2">
                        <input class="form-control" name="id" type="number" value="{{ Request::query('id') }}" placeholder="{{ trans('model.user.id') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input class="form-control" name="username" type="text" value="{{ Request::query('username') }}"
                               placeholder="{{ trans('common.account') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <input class="form-control" name="ip" type="text" value="{{ Request::query('ip') }}" placeholder="IP" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-3">
                        <input class="form-control" name="port" type="number" value="{{ Request::query('port') }}"
                               placeholder="{{ trans('model.user.port') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-5">
                        <select class="form-control" id="node_id" name="node_id" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.node.attribute') }}">
                            @foreach ($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.ip.network_type') }}</th>
                            <th> {{ trans('model.node.attribute') }}</th>
                            <th> {{ trans('common.account') }}</th>
                            <th> IP</th>
                            <th> {{ trans('model.ip.info') }}</th>
                            <th> {{ trans('validation.attributes.time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($onlineIPLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->type }}</td>
                                <td>{{ $log->node->name ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]) . '】' }}</td>
                                <td>{{ $log->user->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.user.attribute')]) . '】' . '$log->user_id' }}
                                </td>
                                <td>
                                    @if (str_contains($log->ip, ','))
                                        @foreach (explode(',', $log->ip) as $ip)
                                            <a href="https://db-ip.com/{{ $ip }}" target="_blank">{{ $ip }}</a>
                                        @endforeach
                                    @else
                                        <a href="https://db-ip.com/{{ $log->ip }}" target="_blank">{{ $log->ip }}</a>
                                    @endif
                                </td>
                                <td>{{ $log->ipInfo ?? '' }}</td>
                                <td>{{ date('Y-m-d H:i:s', $log->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $onlineIPLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $onlineIPLogs->links() }}
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
            $('#node_id').selectpicker('val', @json(Request::query('node_id')));
        });
    </script>
@endpush
