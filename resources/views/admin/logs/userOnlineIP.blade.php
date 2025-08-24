@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :theads="[
            '#',
            trans('common.account'),
            trans('model.user.port'),
            trans('model.user.account_status'),
            trans('model.user.proxy_status'),
            trans('admin.logs.user_ip.connect'),
        ]" :count="trans('admin.logs.counts', ['num' => $userList->total()])" :pagination="$userList->links()" :title="trans('admin.menu.log.online_logs') . ' <small>' . trans('admin.logs.user_ip.sub_title') . '</small>'">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-1 col-sm-4" name="id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-lg-3 col-sm-8" name="username" :placeholder="trans('common.account')" />
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="wechat" :placeholder="trans('model.user.wechat')" />
                <x-admin.filter.input class="col-lg-2 col-sm-6" name="qq" :placeholder="trans('model.user.qq')" />
                <x-admin.filter.input class="col-lg-1 col-sm-6" name="port" type="number" :placeholder="trans('model.user.port')" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($userList as $user)
                    <tr>
                        <td> {{ $user->id }} </td>
                        <td> {{ $user->username }} </td>
                        <td> {{ $user->port }} </td>
                        <td>
                            @if ($user->status > 0)
                                <span class="badge badge-lg badge-success">{{ trans('common.status.normal') }}</span>
                            @elseif ($user->status < 0)
                                <span class="badge badge-lg badge-danger">{{ trans('common.status.banned') }}</span>
                            @else
                                <span class="badge badge-lg badge-default">{{ trans('common.status.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->enable)
                                <span class="badge badge-lg badge-success">{{ trans('common.status.enabled') }}</span>
                            @else
                                <span class="badge badge-lg badge-danger">{{ trans('common.status.banned') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->onlineIPList->isNotEmpty())
                                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                                    <thead>
                                        <tr>
                                            <th> {{ trans('model.node.attribute') }}</th>
                                            <th> {{ trans('model.ip.network_type') }}</th>
                                            <th> IP</th>
                                            <th> {{ ucfirst(trans('validation.attributes.time')) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->onlineIPList as $log)
                                            <tr>
                                                <td>{{ $log->node->name ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]) . '】' }}
                                                </td>
                                                <td>{{ $log->type }}</td>
                                                <td>
                                                    <a href="https://db-ip.com/{{ $log->ip }}" target="_blank">{{ $log->ip }}</a>
                                                </td>
                                                <td>{{ date('Y-m-d H:i:s', $log->created_at) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
