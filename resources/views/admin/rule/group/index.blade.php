@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.rule.group')" :theads="['#', trans('model.rule_group.name'), trans('model.rule_group.type'), trans('common.action')]" :count="trans('admin.rule.group.counts', ['num' => $ruleGroups->total()])" :pagination="$ruleGroups->links()" :delete-config="['url' => route('admin.rule.group.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.rule_group.attribute')]">
            @can('admin.rule.group.create')
                <x-slot:actions>
                    <a class="btn btn-outline-primary" href="{{ route('admin.rule.group.create') }}">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </a>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($ruleGroups as $ruleGroup)
                    <tr>
                        <td> {{ $ruleGroup->id }} </td>
                        <td> {{ $ruleGroup->name }} </td>
                        <td> {!! $ruleGroup->type_label !!} </td>
                        <td>
                            @canany(['admin.rule.group.edit', 'admin.rule.group.destroy'])
                                <div class="btn-group">
                                    @can('admin.rule.group.edit')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.rule.group.edit', $ruleGroup) }}">
                                            <i class="icon wb-edit"></i> {{ trans('common.edit') }}
                                        </a>
                                    @endcan
                                    @can('admin.rule.group.destroy')
                                        <button class="btn btn-sm btn-outline-danger" data-action="delete">
                                            <i class="icon wb-trash"></i> {{ trans('common.delete') }}
                                        </button>
                                    @endcan
                                </div>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
