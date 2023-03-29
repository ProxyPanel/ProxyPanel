@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('admin.logs.credit_title') }}</h3>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.log.credit')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> {{ trans('model.order.id') }}</th>
                        <th> {{ trans('model.user_credit.before') }}</th>
                        <th> {{ trans('model.user_credit.amount') }}</th>
                        <th> {{ trans('model.user_credit.after') }}</th>
                        <th> {{ trans('model.common.description') }}</th>
                        <th> {{ trans('model.user_credit.created_at') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($userCreditLogs as $log)
                        <tr>
                            <td> {{$log->id}} </td>
                            <td>
                                @if(empty($log->user))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    <a href="{{route('admin.log.credit', ['username'=>$log->user->username])}}"> {{$log->user->username}} </a>
                                @endif
                            </td>
                            <td> {{$log->order_id}} </td>
                            <td> {{$log->before}} </td>
                            <td> {{$log->amount}} </td>
                            <td> {{$log->after}} </td>
                            <td> {{$log->description}} </td>
                            <td> {{$log->created_at}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $userCreditLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$userCreditLogs->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
@endsection
