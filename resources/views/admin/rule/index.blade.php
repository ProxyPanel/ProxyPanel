@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.rule.list')" :theads="['#', trans('model.rule.attribute'), trans('model.rule.name'), trans('model.rule.pattern'), trans('common.action')]" :count="trans('admin.rule.counts', ['num' => $rules->total()])" :pagination="$rules->links()" :delete-config="['url' => route('admin.rule.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.rule.attribute'), 'nameColumn' => 2]">
            @can('admin.rule.store')
                <x-slot:actions>
                    <button class="btn btn-outline-primary" data-toggle="modal" data-target="#add">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </button>
                </x-slot:actions>
            @endcan
            <x-slot:filters>
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="type" :title="trans('model.rule.attribute')" :options="[
                    1 => trans('admin.rule.type.reg'),
                    2 => trans('admin.rule.type.domain'),
                    3 => trans('admin.rule.type.ip'),
                    4 => trans('admin.rule.type.protocol'),
                ]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($rules as $rule)
                    <tr>
                        <td> {{ $rule->id }} </td>
                        <td> {!! $rule->type_label !!} </td>
                        <td>
                            <input class="form-control" id="name_{{ $rule->id }}" name="name" type="text" value="{{ $rule->name }}" />
                        </td>
                        <td>
                            <input class="form-control" id="pattern_{{ $rule->id }}" name="pattern" type="text" value="{{ $rule->pattern }}" />
                        </td>
                        <td>
                            @canany(['admin.rule.update', 'admin.rule.destroy'])
                                <div class="btn-group">
                                    @can('admin.rule.update')
                                        <button class="btn btn-sm btn-outline-primary" onclick="editRule('{{ $rule->id }}')">
                                            <i class="icon wb-edit"></i></button>
                                    @endcan
                                    @can('admin.rule.destroy')
                                        <button class="btn btn-sm btn-outline-danger" data-action="delete">
                                            <i class="icon wb-trash"></i></button>
                                    @endcan
                                </div>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    @can('admin.rule.store')
        <x-ui.modal id="add" form :title="trans('admin.action.add_item', ['attribute' => trans('model.rule.attribute')])" size="simple" position="center">
            <form class="modal-body form-horizontal" action="#" method="post">
                <div class="alert alert-danger" id="msg" style="display: none;"></div>
                <x-admin.form.select name="type" :label="trans('model.rule.attribute')" :options="[
                    1 => trans('admin.rule.type.reg'),
                    2 => trans('admin.rule.type.domain'),
                    3 => trans('admin.rule.type.ip'),
                    4 => trans('admin.rule.type.protocol'),
                ]" />
                <x-admin.form.input name="name" :label="trans('model.rule.name')" required />
                <x-admin.form.textarea name="pattern" :label="trans('model.rule.pattern')" input_grid="col-sm-8" required />
            </form>
            <x-slot:actions>
                <button class="btn btn-primary" type="button" onclick="addRule()">{{ trans('common.add') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan
@endsection
@push('javascript')
    <script>
        @can('admin.rule.store')
            // 添加规则
            function addRule() {
                const data = collectFormData('#add form');

                ajaxPost("{{ route('admin.rule.store') }}", data, {
                    success: function(ret) {
                        handleResponse(ret, {
                            showMessage: false,
                            onSuccess: function() {
                                $("#add").modal("hide");
                            }
                        });
                    },
                    error: function(xhr) {
                        handleErrors(xhr, {
                            default: 'element',
                            form: '#add form',
                            element: '#msg'
                        });
                    }
                });
            }
        @endcan

        @can('admin.rule.update')
            // 编辑规则
            function editRule(id) {
                ajaxPut(jsRoute('{{ route('admin.rule.update', 'PLACEHOLDER') }}', id), {
                    name: $(`#name_${id}`).val(),
                    pattern: $(`#pattern_${id}`).val()
                }, {
                    error: function(xhr) {
                        handleErrors(xhr, {
                            form: 'tr:has(#name_' + id + ')'
                        });
                    }
                });
            }
        @endcan
    </script>
@endpush
