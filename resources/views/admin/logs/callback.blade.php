@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.payment_callback')" :theads="[
            '#',
            trans('model.payment_callback.method'),
            trans('model.payment_callback.trade_no'),
            trans('model.payment_callback.out_trade_no'),
            trans('model.payment_callback.amount'),
            trans('common.status.attribute'),
            trans('model.payment_callback.payload'),
            trans('model.payment_callback.created_at'),
        ]" :count="trans('admin.logs.counts', ['num' => $callbackLogs->total()])" :pagination="$callbackLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="trade_no" :placeholder="trans('model.payment_callback.trade_no')" />
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="out_trade_no" :placeholder="trans('model.payment_callback.out_trade_no')" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="method" :title="trans('model.payment_callback.method')" :options="$methods" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="status" :title="trans('common.status.attribute')" :options="[
                    1 => trans('common.success'),
                    0 => trans('common.status.pending'),
                ]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($callbackLogs as $log)
                    <tr>
                        <td> {{ $log->id }} </td>
                        <td> {{ $log->method_label }} </td>
                        <td>
                            @can('admin.order')
                                @if ($sn = $log->payment?->order?->sn)
                                    <a href="{{ route('admin.order', ['sn' => $sn]) }}" target="_blank"> {{ $log->trade_no }} </a>
                                @else
                                    {{ $log->trade_no ?: '-' }}
                                @endif
                            @else
                                {{ $log->trade_no ?: '-' }}
                            @endcan
                        </td>
                        <td> {{ $log->out_trade_no ?: '-' }} </td>
                        <td> {{ $log->amount_tag }} </td>
                        <td> {{ $log->status_label }} </td>
                        <td title="{{ $log->payload }}"> {{ Str::limit(preg_replace('/\s+/', ' ', $log->payload), 60) }} </td>
                        <td> {{ $log->created_at }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
