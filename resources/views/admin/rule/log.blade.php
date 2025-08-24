@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.rule.trigger')" :theads="[
            '#',
            'UID',
            trans('model.user.username'),
            trans('model.node.attribute'),
            trans('admin.logs.rule.name'),
            trans('admin.logs.rule.reason'),
            trans('admin.logs.rule.created_at'),
        ]" :count="trans('admin.logs.counts', ['num' => $ruleLogs->total()])" :pagination="$ruleLogs->links()">
            @can('admin.rule.clear')
                <x-slot:actions>
                    <button class="btn btn-outline-primary" onclick="clearLog()">
                        <i class="icon wb-rubber" aria-hidden="true"></i>{{ trans('admin.logs.rule.clear_all') }}
                    </button>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.input class="col-xxl-1 col-lg-2 col-md-1 col-sm-4" name="user_id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="username" :placeholder="trans('model.user.username')" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="node_id" :title="trans('model.node.attribute')" :options="$nodes->mapWithKeys(fn($name, $id) => [$id => $id . ' - ' . $name])" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="rule_id" :title="trans('model.rule.attribute')" :options="$rules" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($ruleLogs as $ruleLog)
                    <tr>
                        <td> {{ $ruleLog->id }} </td>
                        <td> {{ $ruleLog->user->id ?? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' }} </td>
                        <td> {{ $ruleLog->user->username ?? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' }} </td>
                        <td> {{ empty($ruleLog->node) ? '【' . trans('common.deleted_item', ['attribute' => trans('model.node.attribute')]) . '】' : '【' . trans('model.node.attribute') . '#: ' . $ruleLog->node_id . '】' . $ruleLog->node->name }}
                        </td>
                        <td> {{ $ruleLog->rule_id ? '⛔  ' . ($ruleLog->rule->name ?? '【' . trans('common.deleted_item', ['attribute' => trans('model.rule.attribute')]) . '】') : trans('admin.logs.rule.tag') }}
                        </td>
                        <td> {{ $ruleLog->reason }} </td>
                        <td> {{ $ruleLog->created_at }} </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script>
        @can('admin.rule.clear')
            // 清除所有记录
            function clearLog() {
                showConfirm({
                    title: '{{ trans('common.warning') }}',
                    text: '{{ trans('admin.logs.rule.clear_confirm') }}',
                    icon: 'warning',
                    onConfirm: function() {
                        ajaxPost('{{ route('admin.rule.clear') }}');
                    }
                });
            }
        @endcan
    </script>
@endpush
