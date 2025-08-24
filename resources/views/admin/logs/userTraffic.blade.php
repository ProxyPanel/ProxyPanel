@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.traffic_flow')" :theads="[
            '#',
            trans('common.account'),
            trans('model.order.attribute'),
            trans('model.user_data_modify.before'),
            trans('model.user_data_modify.after'),
            trans('model.common.description'),
            trans('model.user_data_modify.created_at'),
        ]" :count="trans('admin.logs.counts', ['num' => $userTrafficLogs->total()])" :pagination="$userTrafficLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-4 col-sm-6" name="username" :placeholder="trans('common.account')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($userTrafficLogs as $log)
                    <tr>
                        <td> {{ $log->id }} </td>
                        <td>
                            @if (empty($log->user))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                <a href="{{ route('admin.log.flow', ['username' => $log->user->username]) }}"> {{ $log->user->username }} </a>
                            @endif
                        </td>
                        <td>
                            @if ($log->order_id)
                                @if ($log->order)
                                    @can('admin.order')
                                        <a href="{{ route('admin.order', ['id' => $log->order_id]) }}"></a>
                                    @else
                                        {{ $log->order->goods->name }}
                                    @endcan
                                @else
                                    【{{ trans('common.deleted_item', ['attribute' => trans('model.order.attribute')]) }}】
                                @endif
                            @endif
                        </td>
                        <td> {{ $log->before }} </td>
                        <td> {{ $log->after }} </td>
                        <td> {{ $log->description }} </td>
                        <td> {{ $log->created_at }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
