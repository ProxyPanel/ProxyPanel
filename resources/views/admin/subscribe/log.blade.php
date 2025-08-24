@extends('admin.table_layouts')
@push('css')
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.user.subscribe')" grid="col-xl-10 col-sm-12" :theads="['#', trans('model.subscribe.req_ip'), trans('model.ip.info'), trans('model.subscribe.req_times'), trans('model.subscribe.req_header')]" :count="trans('admin.logs.counts', ['num' => $subscribeLog->total()])" :pagination="$subscribeLog->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-xxl-1 col-lg-2 col-md-3" name="id" type="number" placeholder="ID" />
                <x-admin.filter.input class=" col-xxl-2 col-lg-3 col-md-6" name="ip" placeholder="IP" />
                <x-admin.filter.daterange />
            </x-slot:filters>
            <x-slot:body>
                <div class="col-xl-2 col-sm-12">
                    <ul class="list-group list-group-gap">
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-user-circle" aria-hidden="true"></i> {{ trans('model.user.nickname') }}:
                            <span
                                  class="float-right">{{ $subscribe->user->nickname ?? trans('common.deleted_item', ['attribute' => trans('common.account')]) }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-envelope" aria-hidden="true"></i> {{ trans('model.user.username') }}:
                            <span
                                  class="float-right">{{ $subscribe->user->username ?? trans('common.deleted_item', ['attribute' => trans('model.user.attribute')]) }}</span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-heart" aria-hidden="true"></i> {{ trans('common.status.attribute') }}:
                            <span class="float-right"><i class="icon {{ $subscribe->status ? 'wb-check green-600' : 'wb-close red-600' }}"
                                   aria-hidden="true"></i></span>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-bell" aria-hidden="true"></i> {{ trans('model.subscribe.req_times') }}:
                            <code class="float-right">{{ $subscribe->times }}</code>
                        </li>
                        <li class="list-group-item bg-blue-grey-100">
                            <i class="icon wb-time" aria-hidden="true"></i> {{ trans('model.subscribe.updated_at') }}:
                            <span class="float-right">{{ $subscribe->updated_at }}</span>
                        </li>
                        @if ($subscribe->ban_time)
                            <li class="list-group-item bg-blue-grey-100">
                                <i class="icon wb-power" aria-hidden="true"></i> {{ trans('model.subscribe.ban_time') }}
                                : <span class="float-right">{{ date('Y-m-d H:i', $subscribe->ban_time) }}</span>
                            </li>
                            <li class="list-group-item bg-blue-grey-100">
                                <i class="icon wb-lock" aria-hidden="true"></i> {{ trans('model.subscribe.ban_desc') }}:
                                <span class="float-right">{{ __($subscribe->ban_desc) }}</span>
                            </li>
                        @endif
                        @can('admin.subscribe.set')
                            <button class="list-group-item btn btn-block @if ($subscribe->status) btn-danger @else btn-success @endif"
                                    onclick="setSubscribeStatus('{{ $subscribe->id }}')">
                                @if ($subscribe->status === 0)
                                    <i class="icon wb-unlock" aria-hidden="true"></i> {{ trans('common.status.enabled') }}
                                @else
                                    <i class="icon wb-unlock" aria-hidden="true"></i> {{ trans('common.status.disabled') }}
                                @endif
                            </button>
                        @endcan
                    </ul>
                </div>
            </x-slot:body>
            <x-slot:tbody>
                @foreach ($subscribeLog as $subscribe)
                    <tr>
                        <td>{{ $subscribe->id }}</td>
                        <td>
                            @if ($subscribe->request_ip)
                                <a href="https://db-ip.com/{{ $subscribe->request_ip }}" target="_blank">{{ $subscribe->request_ip }}</a>
                            @endif
                        </td>
                        <td>{{ $subscribe->ipInfo }}</td>
                        <td>{{ $subscribe->request_time }}</td>
                        <td>{{ trim($subscribe->request_header) }}</td>
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
        @can('admin.subscribe.set')
            function setSubscribeStatus(id) { // 启用禁用用户的订阅
                ajaxPost(jsRoute('{{ route('admin.subscribe.set', 'PLACEHOLDER') }}', id));
            }
        @endcan
    </script>
@endpush
