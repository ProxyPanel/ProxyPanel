@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.payment_callback')" :theads="['#', '支付方式', '平台订单号', '本地订单号', '交易金额', trans('common.status.attribute')]" :count="trans('admin.logs.counts', ['num' => $callbackLogs->total()])" :pagination="$callbackLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="out_trade_no" placeholder="本地订单号" />
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="trade_no" placeholder="外部订单号" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-4" name="status" :title="trans('common.status.attribute')" :options="[1 => trans('common.success'), 0 => trans('common.failed')]" />
            </x-slot:filters>
            <x-slot:tbody>
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
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
