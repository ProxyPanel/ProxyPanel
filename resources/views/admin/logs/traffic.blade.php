@extends('admin.table_layouts')
@push('css')
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.log.traffic')" :theads="[
            '#',
            trans('common.account'),
            trans('model.node.attribute'),
            trans('model.node.data_rate'),
            trans('model.user_traffic.upload'),
            trans('model.user_traffic.download'),
            trans('model.user_traffic.total'),
            trans('model.user_traffic.log_time'),
        ]" :count="trans('admin.logs.counts', ['num' => $dataFlowLogs->total()])" :pagination="$dataFlowLogs->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-2 col-sm-4" name="user_id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-lg-3 col-sm-8" name="username" :placeholder="trans('common.account')" />
                <x-admin.filter.input class="col-lg-2 col-sm-4" name="port" type="number" :placeholder="trans('model.user.port')" />
                <x-admin.filter.selectpicker class="col-lg-3 col-sm-8" name="node_id" :title="trans('admin.logs.user_traffic.choose_node')" :options="$nodes" />
                <x-admin.filter.daterange />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($dataFlowLogs as $log)
                    <tr>
                        <td> {{ $log->id }} </td>
                        <td>
                            @if (empty($log->user))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['id' => $log->user->id]) }}" target="_blank"> {{ $log->user->username }} </a>
                                @else
                                    {{ $log->user->username }}
                                @endcan
                            @endif
                        </td>
                        <td> {{ $log->node->name ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]) . '】' }} </td>
                        <td> {{ $log->rate }} </td>
                        <td> {{ $log->u }} </td>
                        <td> {{ $log->d }} </td>
                        <td><span class="badge badge-danger"> {{ $log->traffic }} </span></td>
                        <td> {{ $log->log_time }} </td>
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
@endpush
