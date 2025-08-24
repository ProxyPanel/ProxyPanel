@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.service_ban')" :theads="[
            '#',
            trans('common.account'),
            trans('admin.logs.ban.time') . ' (' . ucfirst(trans('validation.attributes.minute')) . ')',
            trans('admin.logs.ban.reason'),
            trans('admin.logs.ban.ban_time'),
            trans('admin.logs.ban.last_connect_at'),
        ]" :count="trans('admin.logs.counts', ['num' => $userBanLogs->total()])" :pagination="$userBanLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="username" :placeholder="trans('common.account')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($userBanLogs as $log)
                    <tr>
                        <td>
                            {{ $log->id }}
                        </td>
                        <td>
                            @if ($log->user)
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['username' => $log->user->username]) }}" target="_blank">
                                        {{ $log->user->username }}</a>
                                @else
                                    {{ $log->user->username }}
                                @endcan
                            @else
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @endif
                        </td>
                        <td> {{ $log->time }}</td>
                        <td> {{ $log->description }} </td>
                        <td> {{ $log->created_at }} </td>
                        <td> {{ date('Y-m-d H:i:s', $log->user->t) }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
