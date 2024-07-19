@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.logs.ban.title') }}</h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input class="form-control" name="username" type="text" value="{{ Request::query('username') }}"
                               placeholder="{{ trans('common.account') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('common.account') }}</th>
                            <th> {{ trans('admin.logs.ban.time') }}</th>
                            <th> {{ trans('admin.logs.ban.reason') }}</th>
                            <th> {{ trans('admin.logs.ban.ban_time') }}</th>
                            <th> {{ trans('admin.logs.ban.last_connect_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userBanLogs as $log)
                            <tr>
                                <td>
                                    {{ $log->id }}
                                </td>
                                <td>
                                    @if ($log->user)
                                        @can('admin.user.index')
                                            <a href="{{ route('admin.user.index', ['username' => $log->user->username]) }}" target="_blank">
                                                {{ $log->user->username }}</a>
                                        @else
                                            {{ $log->user->username }}
                                        @endcan
                                    @else
                                        【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                                    @endif
                                </td>
                                <td> {{ $log->time }} {{ trans('admin.minute') }}</td>
                                <td> {{ $log->description }} </td>
                                <td> {{ $log->created_at }} </td>
                                <td> {{ date('Y-m-d H:i:s', $log->user->t) }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $userBanLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $userBanLogs->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
