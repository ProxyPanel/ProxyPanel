@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.user.subscribe')" :theads="[
            'id' => '#',
            trans('model.user.username'),
            trans('model.subscribe.code'),
            'times' => trans('model.subscribe.req_times'),
            trans('model.subscribe.updated_at'),
            trans('model.subscribe.ban_time'),
            trans('model.subscribe.ban_desc'),
            trans('common.action'),
        ]" :count="trans('admin.logs.counts', ['num' => $subscribeList->total()])" :pagination="$subscribeList->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-1 col-sm-6" name="user_id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="username" :placeholder="trans('model.user.username')" />
                <x-admin.filter.input class="col-lg-3 col-sm-6" name="code" :placeholder="trans('model.subscribe.code')" />
                <x-admin.filter.selectpicker class="col-lg-3 col-sm-6" name="status" :title="trans('common.status.attribute')" :options="[0 => trans('common.status.banned'), 1 => trans('common.status.normal')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($subscribeList as $subscribe)
                    <tr>
                        <td> {{ $subscribe->id }} </td>
                        <td>
                            @if ($subscribe->has('user'))
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['id' => $subscribe->user->id]) }}" target="_blank">{{ $subscribe->user->username }}</a>
                                @else
                                    {{ $subscribe->user->username }}
                                @endcan
                            @else
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @endif
                        </td>
                        <td> {{ $subscribe->code }} </td>
                        <td>
                            @can('admin.subscribe.log')
                                <a href="{{ route('admin.subscribe.log', $subscribe) }}" target="_blank">{{ $subscribe->times }}</a>
                            @endcan
                        </td>
                        <td> {{ $subscribe->updated_at }} </td>
                        <td> {{ $subscribe->ban_time ? date('Y-m-d H:i', $subscribe->ban_time) : '' }} </td>
                        <td> {{ __($subscribe->ban_desc) }} </td>
                        <td>
                            @can('admin.subscribe.set')
                                <button class="btn btn-sm @if ($subscribe->status === 0) btn-outline-success @else btn-outline-danger @endif"
                                        onclick="setSubscribeStatus('{{ $subscribe->id }}')">
                                    @if ($subscribe->status === 0)
                                        {{ trans('common.status.enabled') }}
                                    @else
                                        {{ trans('common.status.banned') }}
                                    @endif
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script>
        @can('admin.subscribe.set')
            function setSubscribeStatus(id) { // 启用禁用用户的订阅
                ajaxPost(jsRoute('{{ route('admin.subscribe.set', 'PLACEHOLDER') }}', id));
            }
        @endcan
    </script>
@endpush
