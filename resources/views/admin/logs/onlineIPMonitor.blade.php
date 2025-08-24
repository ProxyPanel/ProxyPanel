@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :theads="[
            '#',
            trans('model.ip.network_type'),
            trans('model.node.attribute'),
            trans('common.account'),
            'IP',
            trans('model.ip.info'),
            ucfirst(trans('validation.attributes.time')),
        ]" :count="trans('admin.logs.counts', ['num' => $onlineIPLogs->total()])" :pagination="$onlineIPLogs->links()" :title="trans('admin.menu.log.online_monitor') . ' <small>' . trans('admin.logs.monitor.sub_title') . '</small>'">
            <x-slot:filters>
                <x-admin.filter.input class="col-sm-2" name="id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-lg-2 col-sm-5" name="username" :placeholder="trans('common.account')" />
                <x-admin.filter.input class="col-lg-2 col-sm-5" name="ip" placeholder="IP" />
                <x-admin.filter.input class="col-lg-2 col-sm-3" name="port" type="number" :placeholder="trans('model.user.port')" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-5" name="node_id" :title="trans('model.node.attribute')" :options="$nodes" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($onlineIPLogs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->type }}</td>
                        <td>{{ $log->node->name ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]) . '】' }}</td>
                        <td>{{ $log->user->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.user.attribute')]) . '】' . '$log->user_id' }}
                        </td>
                        <td>
                            @if (str_contains($log->ip, ','))
                                @foreach (explode(',', $log->ip) as $ip)
                                    <a href="https://db-ip.com/{{ $ip }}" target="_blank">{{ $ip }}</a>
                                @endforeach
                            @else
                                <a href="https://db-ip.com/{{ $log->ip }}" target="_blank">{{ $log->ip }}</a>
                            @endif
                        </td>
                        <td>{{ $log->ipInfo ?? '' }}</td>
                        <td>{{ date('Y-m-d H:i:s', $log->created_at) }}</td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
