@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ trans('admin.menu.log.payment_callback') }}
                </h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input class="form-control" name="out_trade_no" type="text" value="{{ Request::query('out_trade_no') }}" placeholder="本地订单号"
                               autocomplete="off" />
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <input class="form-control" name="trade_no" type="text" value="{{ Request::query('trade_no') }}" placeholder="外部订单号"
                               autocomplete="off" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="status" name="status" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('common.status.attribute') }}">
                            <option value="1">{{ trans('common.success') }}</option>
                            <option value="0">{{ trans('common.failed') }}</option>
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
                            <th> 支付方式</th>
                            <th> 平台订单号</th>
                            <th> 本地订单号</th>
                            <th> 交易金额</th>
                            <th> {{ trans('common.status.attribute') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($callbackLogs as $log)
                            <tr>
                                <td> {{ $log->id }} </td>
                                <td> {{ $log->type_label }} </td>
                                <td> {{ $log->trade_no }} </td>
                                <td>
                                    @can('admin.order')
                                        <a href="{{ route('admin.order', ['sn' => $log->out_trade_no]) }}" target="_blank"> {{ $log->out_trade_no }} </a>
                                    @else
                                        {{ $log->out_trade_no }}
                                    @endcan
                                </td>
                                <td> {{ $log->amount_tag }}</td>
                                <td> {!! $log->trade_status_label !!} </td>
                                <td> {{ $log->created_at }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $callbackLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $callbackLogs->links() }}
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
            $("#status").selectpicker("val", @json(Request::query('status')));
        });
    </script>
@endpush
