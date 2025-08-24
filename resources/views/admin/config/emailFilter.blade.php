@extends('admin.table_layouts')
@section('content')
    <div class="page-content container">
        <x-admin.table-panel :theads="['#', trans('admin.setting.email.rule'), trans('admin.setting.email.tail'), trans('common.action')]" :count="trans('admin.logs.counts', ['num' => $filters->total()])" :pagination="$filters->links()" :title="trans('admin.menu.setting.email_suffix') . ' <small>' . trans('admin.setting.email.sub_title') . '</small>'"
                             :delete-config="['url' => route('admin.config.filter.destroy', 'PLACEHOLDER'), 'attribute' => trans('admin.setting.email.tail'), 'nameColumn' => 2]">
            @can('admin.config.filter.store')
                <x-slot:actions>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#add_email_suffix">
                        {{ trans('admin.action.add_item', ['attribute' => trans('admin.setting.email.tail')]) }}
                    </button>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($filters as $filter)
                    <tr>
                        <td> {{ $filter->id }} </td>
                        <td> {{ $filter->type === 1 ? trans('admin.setting.email.black') : trans('admin.setting.email.white') }} </td>
                        <td> {{ $filter->words }} </td>
                        <td>
                            @can('admin.config.filter.destroy')
                                <button class="btn btn-danger" data-action="delete">
                                    <i class="icon wb-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    @can('admin.config.filter.store')
        <x-ui.modal id="add_email_suffix" :title="trans('admin.action.add_item', ['attribute' => trans('admin.setting.email.tail')])" size="lg">
            <x-admin.form.radio-group name="type" :label="trans('admin.setting.email.rule')" :options="[
                1 => trans('admin.setting.email.black'),
                2 => trans('admin.setting.email.white'),
            ]" />

            <x-admin.form.input name="words" :label="trans('admin.setting.email.tail')" :placeholder="trans('admin.setting.email.tail_placeholder')" input_grid="col-md-9" />
            <x-slot:actions>
                <button class="btn btn-success" onclick="addEmailSuffix()">{{ trans('common.submit') }}</button>
            </x-slot:actions>
        </x-ui.modal>
    @endcan
@endsection
@push('javascript')
    <script>
        @can('admin.config.filter.store')
            function addEmailSuffix() { // 添加邮箱后缀
                const words = $("#words").val();
                if (words.trim() === "") {
                    showMessage({
                        title: '{{ trans('validation.required', ['attribute' => trans('admin.setting.email.tail')]) }}',
                        icon: "warning",
                        timer: 1000,
                        showConfirmButton: false
                    });
                    $("#words").focus();
                    return false;
                }

                ajaxPost('{{ route('admin.config.filter.store') }}', {
                    type: $("input:radio[name='type']:checked").val(),
                    words: words
                }, {
                    success: function(ret) {
                        $("#add_email_suffix").modal("hide");
                        handleResponse(ret);
                    },
                });
            }
        @endcan
    </script>
@endpush
