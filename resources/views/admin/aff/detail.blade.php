@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.aff.commission_title')" grid="col-xxl-10 col-xl-9 col-sm-12" :theads="[
            '#',
            trans('model.aff.invitee'),
            trans('model.goods.attribute'),
            trans('model.aff.amount'),
            trans('model.aff.commission'),
            trans('model.aff.created_at'),
        ]" :count="trans('admin.aff.commission_counts', ['num' => $commissions->total()])" :pagination="$commissions->links()">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.aff.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>
            <x-slot:body>
                <div class="col-xxl-2 col-xl-3 col-sm-12">
                    <ul class="list-group list-group-gap">
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-order" aria-hidden="true"></i> {{ trans('model.referral.id') }}
                            <span class="float-right">{{ $referral->id }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-user-circle" aria-hidden="true"></i> {{ trans('model.referral.user') }}
                            <span class="float-right">{{ $referral->user->username }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-payment" aria-hidden="true"></i> {{ trans('model.referral.amount') }}
                            <span class="float-right">{{ $referral->amount_tag }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-calendar" aria-hidden="true"></i> {{ trans('model.referral.created_at') }}
                            <code class="float-right">{{ $referral->created_at }}</code>
                        </li>
                        @if ($referral->status === -1)
                            <span class="list-group-item badge badge-lg badge-danger"> {{ trans('common.status.rejected') }} </span>
                        @elseif($referral->status === 2)
                            <span class="list-group-item badge badge-lg badge-success"> {{ trans('common.status.paid') }} </span>
                        @endif
                    </ul>
                </div>
            </x-slot:body>
            <x-slot:tbody>
                @foreach ($commissions as $commission)
                    <tr>
                        <td> {{ $commission->id }} </td>
                        <td> {{ $commission->invitee->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' }}
                        </td>
                        <td>
                            @can('admin.order')
                                <a href="{{ route('admin.order', ['id' => $commission->order->id]) }}" target="_blank">
                                    {{ $commission->order->goods->name }}
                                </a>
                            @else
                                {{ $commission->order->goods->name }}
                            @endcan
                        </td>
                        <td> {{ $commission->amount_tag }} </td>
                        <td> {{ $commission->commission_tag }} </td>
                        <td> {{ $commission->created_at }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
