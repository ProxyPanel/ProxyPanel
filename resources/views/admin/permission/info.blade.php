@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <x-ui.panel :title="trans(isset($permission) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.permission.attribute')])">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.permission.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>

            <x-admin.form.container :route="isset($permission) ? route('admin.permission.update', $permission) : route('admin.permission.store')" :method="isset($permission) ? 'PUT' : 'POST'">
                <x-admin.form.input name="description" :label="trans('model.permission.description')" :help="trans('admin.permission.description_hint')" required />
                <x-admin.form.input name="name" :label="trans('model.permission.name')" :help="trans('admin.permission.name_hint')" required />
                <div class="form-actions text-right">
                    <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script>
        let permissionData = {};

        @isset($permission)
            permissionData = @json($permission)
        @endisset
        @if (old())
            permissionData = @json(old())
        @endif

        $(document).ready(function() {
            autoPopulateForm(permissionData); // 填充表单数据
        });
    </script>
@endsection
