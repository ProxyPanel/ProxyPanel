@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.user.credit_log')" :theads="[
            '#',
            trans('common.account'),
            trans('model.order.id'),
            trans('model.user_credit.before'),
            trans('model.user_credit.amount'),
            trans('model.user_credit.after'),
            trans('model.common.description'),
            trans('model.user_credit.created_at'),
        ]" :count="trans('admin.logs.counts', ['num' => $userCreditLogs->total()])" :pagination="$userCreditLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="username" :placeholder="trans('common.account')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($userCreditLogs as $log)
                    <tr>
                        <td> {{ $log->id }} </td>
                        <td>
                            @if (empty($log->user))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                <a href="{{ route('admin.log.credit', ['username' => $log->user->username]) }}"> {{ $log->user->username }} </a>
                            @endif
                        </td>
                        <td> {{ $log->order_id }} </td>
                        <td> {{ $log->before }} </td>
                        <td> {{ $log->amount }} </td>
                        <td> {{ $log->after }} </td>
                        <td> {{ __($log->description) }} </td>
                        <td> {{ $log->created_at }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
