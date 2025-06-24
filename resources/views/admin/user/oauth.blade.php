@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.menu.user.oauth') }}</h2>
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.user.attribute') }}</th>
                            <th> {{ trans('model.oauth.type') }}</th>
                            <th> {{ trans('model.oauth.identifier') }}</th>
                            <th> {{ trans('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list as $item)
                            <tr>
                                <td> {{ $item->id }} </td>
                                <td> {{ $item->user->username ?? $item->user->id }} </td>
                                <td> {{ $item->type }} </td>
                                <td> {{ $item->identifier }} </td>
                                <td>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.oauth.counts', ['num' => $list->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $list->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
