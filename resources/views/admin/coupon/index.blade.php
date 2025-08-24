@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.shop.coupon')" :theads="[
            '#',
            trans('model.coupon.name'),
            trans('model.coupon.sn'),
            trans('model.coupon.logo'),
            trans('model.common.type'),
            trans('model.coupon.priority'),
            trans('model.coupon.usable_times'),
            trans('admin.coupon.discount'),
            trans('common.available_date'),
            trans('common.status.attribute'),
            trans('common.action'),
        ]" :count="trans('admin.coupon.counts', ['num' => $couponList->total()])" :pagination="$couponList->links()" :delete-config="['url' => route('admin.coupon.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.coupon.attribute')]">
            @canany(['admin.coupon.export', 'admin.coupon.create'])
                <x-slot:actions>
                    <div class="btn-group">
                        @can('admin.coupon.export')
                            <button class="btn btn-info" onclick="exportCoupon()"><i class="icon wb-code"></i>{{ trans('admin.massive_export') }}</button>
                        @endcan
                        @can('admin.coupon.create')
                            <a class="btn btn-primary" href="{{ route('admin.coupon.create') }}"><i class="icon wb-plus"></i> {{ trans('common.add') }}</a>
                        @endcan
                    </div>
                </x-slot:actions>
            @endcanany
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-4" name="sn" :placeholder="trans('model.coupon.sn')" />
                <x-admin.filter.selectpicker class="col-lg-3 col-sm-4" name="type" :title="trans('model.common.type')" :options="[1 => trans('admin.coupon.type.voucher'), 2 => trans('admin.coupon.type.discount'), 3 => trans('admin.coupon.type.charge')]" />
                <x-admin.filter.selectpicker class="col-lg-3 col-sm-4" name="status" :title="trans('common.status.attribute')" :options="[0 => trans('common.status.available'), 1 => trans('common.status.used'), 2 => trans('common.status.expire')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($couponList as $coupon)
                    <tr>
                        <td> {{ $coupon->id }} </td>
                        <td> {{ $coupon->name }} </td>
                        <td> {{ $coupon->sn }} </td>
                        <td>
                            @if ($coupon->logo)
                                <img class="h-50" src="{{ asset($coupon->logo) }}" alt="{{ trans('model.coupon.logo') }}" />
                            @endif
                        </td>
                        <td>
                            {{ [trans('common.status.unknown'), trans('admin.coupon.type.voucher'), trans('admin.coupon.type.discount'), trans('admin.coupon.type.charge')][$coupon->type] }}
                        </td>
                        <td> {{ $coupon->priority }} </td>
                        <td> {{ $coupon->type === 3 ? trans('admin.coupon.single_use') : $coupon->usable_times ?? trans('common.unlimited') }} </td>
                        <td>
                            {{ trans_choice('admin.coupon.value', $coupon->type, ['num' => $coupon->type === 2 ? $coupon->value : \App\Utils\Helpers::getPriceTag($coupon->value)]) }}
                        </td>
                        <td> {{ $coupon->start_time }} ~ {{ $coupon->end_time }} </td>
                        <td>
                            <span class="badge badge-lg @if ($coupon->status) badge-default @else badge-success @endif">
                                {{ [trans('common.status.available'), trans('common.status.used'), trans('common.status.expire')][$coupon->status] }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                @can('admin.coupon.show')
                                    <a class="btn btn-info" href="{{ route('admin.coupon.show', $coupon) }}" target="_blank">
                                        <i class="icon wb-eye"></i>
                                    </a>
                                @endcan
                                @if ($coupon->status !== 1)
                                    @can('admin.coupon.destroy')
                                        <button class="btn btn-danger" data-action="delete">
                                            <i class="icon wb-close"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@can('admin.coupon.export')
    @push('javascript')
        <script>
            function exportCoupon() { // 批量导出卡券
                showConfirm({
                    title: '{{ trans('admin.coupon.export_title') }}',
                    text: '{{ trans('admin.confirm.export') }}',
                    onConfirm: function() {
                        window.location.href = '{{ route('admin.coupon.export') }}';
                    }
                });
            }
        </script>
    @endpush
@endcan
