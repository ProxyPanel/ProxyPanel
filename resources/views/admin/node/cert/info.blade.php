@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <x-ui.panel :title="trans(isset($cert) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.node_cert.attribute')])">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.node.cert.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>

            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>

            <x-admin.form.container :route="isset($cert) ? route('admin.node.cert.update', $cert) : route('admin.node.cert.store')" :method="isset($cert) ? 'PUT' : 'POST'">
                <x-admin.form.input-group name="domain" type="text" :label="trans('model.node_cert.domain')" required label_grid="col-md-3" input_grid="col-md-9" />

                <x-admin.form.textarea name="key" :label="trans('model.node_cert.key')" rows="10" :placeholder="trans('admin.node.cert.key_placeholder')" label_grid="col-md-3" input_grid="col-md-9" />

                <x-admin.form.textarea name="pem" :label="trans('model.node_cert.pem')" rows="10" :placeholder="trans('admin.node.cert.pem_placeholder')" label_grid="col-md-3" input_grid="col-md-9" />

                <div class="form-actions">
                    <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script>
        let certData = {};

        @isset($cert)
            certData = @json($cert)
        @endisset
        @if (old())
            certData = @json(old())
        @endif

        $(document).ready(function() {
            autoPopulateForm(certData); // 填充表单数据
        });
    </script>
@endsection
