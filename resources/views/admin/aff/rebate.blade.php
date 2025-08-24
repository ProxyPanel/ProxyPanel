@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.promotion.rebate_flow')" :theads="[
            '#',
            trans('model.aff.invitee'),
            trans('model.user.inviter'),
            trans('model.order.id'),
            trans('model.aff.amount'),
            trans('model.aff.commission'),
            trans('model.aff.created_at'),
            trans('model.aff.updated_at'),
            trans('common.status.attribute'),
        ]" :count="trans('admin.aff.counts', ['num' => $referralLogs->total()])" :pagination="$referralLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-4 col-sm-6" name="invitee_username" :placeholder="trans('model.aff.invitee')" />
                <x-admin.filter.input class="col-lg-4 col-sm-6" name="inviter_username" :placeholder="trans('model.user.inviter')" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-6" name="status" :title="trans('common.status.attribute')" :options="[0 => trans('common.status.withdrawal_pending'), 1 => trans('common.status.applying'), 2 => trans('common.status.withdrawn')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($referralLogs as $referralLog)
                    <tr>
                        <td> {{ $referralLog->id }} </td>
                        <td>
                            @if (empty($referralLog->invitee))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                <a href="{{ route('admin.aff.rebate', ['invitee_username' => $referralLog->invitee->username]) }}">
                                    {{ $referralLog->invitee->username }} </a>
                            @endif
                        </td>
                        <td>
                            @if (empty($referralLog->inviter))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                <a href="{{ route('admin.aff.rebate', ['inviter_username' => $referralLog->inviter->username]) }}">
                                    {{ $referralLog->inviter->username }} </a>
                            @endif
                        </td>
                        <td> {{ $referralLog->order_id }} </td>
                        <td> {{ $referralLog->amount }} </td>
                        <td> {{ $referralLog->commission }} </td>
                        <td> {{ $referralLog->created_at }} </td>
                        <td> {{ $referralLog->updated_at }} </td>
                        <td> {!! $referralLog->status_label !!} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
