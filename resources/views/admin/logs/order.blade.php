@extends('admin.table_layouts')
@push('css')
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.shop.order')" :theads="[
            'id' => '#',
            trans('common.account'),
            'sn' => trans('model.order.id'),
            trans('model.goods.attribute'),
            trans('model.coupon.attribute'),
            trans('model.order.original_price'),
            trans('model.order.price'),
            trans('model.order.pay_way'),
            trans('model.order.status'),
            'expired_at' => trans('common.expired_at'),
            'created_at' => trans('common.created_at'),
            trans('common.action'),
        ]" :count="trans('admin.logs.counts', ['num' => $orders->total()])" :pagination="$orders->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="username" :placeholder="trans('common.account')" />
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="sn" type="number" :placeholder="trans('model.order.id')" />
                <x-admin.filter.daterange />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="is_expire" :title="trans('admin.logs.order.is_expired')" :options="[0 => trans('admin.no'), 1 => trans('admin.yes')]" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="is_coupon" :title="trans('admin.logs.order.is_coupon')" :options="[0 => trans('admin.no'), 1 => trans('admin.yes')]" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="pay_way" :title="trans('model.order.pay_way')" :options="$paymentLabels" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="status" :title="trans('model.order.status')" :options="[
                    -1 => trans('common.order.status.canceled'),
                    0 => trans('common.status.payment_pending'),
                    1 => trans('common.order.status.review'),
                    2 => trans('common.order.status.completed') . '/' . trans('common.status.expire') . '/' . trans('common.order.status.ongoing'),
                    3 => trans('common.order.status.prepaid'),
                ]" :multiple="true" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td> {{ $order->id }} </td>
                        <td>
                            @if (empty($order->user))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['id' => $order->user->id]) }}" target="_blank">{{ $order->user->username }} </a>
                                @else
                                    {{ $order->user->username }}
                                @endcan
                            @endif
                        </td>
                        <td> {{ $order->sn }}</td>
                        <td> {{ $order->goods->name ?? trans('user.recharge_credit') }} </td>
                        <td> {{ $order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : '' }} </td>
                        <td> {{ $order->origin_amount_tag }} </td>
                        <td> {{ $order->amount_tag }} </td>
                        <td>
                            {{ $order->pay_way_label }}
                        </td>
                        <td>
                            {!! $order->status_label !!}
                        </td>
                        <td> {{ $order->is_expire ? trans('common.status.expire') : $order->expired_at }} </td>
                        <td> {{ $order->created_at }} </td>
                        @can(['admin.order.edit'])
                            <td>
                                <x-ui.dropdown>
                                    @if ($order->status !== -1)
                                        <x-ui.dropdown-item url="javascript:changeStatus('{{ $order->id }}', -1)" icon="wb-close" :text="trans('admin.set_to', ['attribute' => $order->statusTags(-1, 0, false)])" />
                                    @endif
                                    @if ($order->status !== 2)
                                        <x-ui.dropdown-item url="javascript:changeStatus('{{ $order->id }}', 2)" icon="wb-check" :text="trans('admin.set_to', ['attribute' => $order->statusTags(2, 0, false)])" />
                                    @endif
                                    @if ($order->status !== 3)
                                        <x-ui.dropdown-item url="javascript:changeStatus('{{ $order->id }}', 3)" icon="wb-check-circle" :text="trans('admin.set_to', ['attribute' => $order->statusTags(3, 0, false)])" />
                                    @endif
                                </x-ui.dropdown>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        @can('admin.order.edit')
            // 重置流量
            function changeStatus(id, status) {
                ajaxPost('{{ route('admin.order.edit') }}', {
                    oid: id,
                    status: status
                });
            }
        @endcan
    </script>
@endpush
