@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel :title="trans('admin.menu.tools.convert')" :subtitle="trans('admin.tools.convert.sub_title')">
            <div class="form-row">
                <x-admin.form.select name="method" :label="trans('model.node.method')" :options="$methods" container="form-group col-sm-6 col-md-3" label_grid="col-12"
                                     input_grid="col-12" />
                <x-admin.form.select name="protocol" :label="trans('model.node.protocol')" :options="$protocols" container="form-group col-sm-6 col-md-3" label_grid="col-12"
                                     input_grid="col-12" />
                <x-admin.form.select name="obfs" :label="trans('model.node.obfs')" :options="$obfs" container="form-group col-sm-6 col-md-3" label_grid="col-12"
                                     input_grid="col-12" />
                <x-admin.form.input-group name="transfer_enable" type="number" value="1000" :label="trans('model.user.usable_traffic')" append="GB" required
                                          container="form-group col-sm-6 col-md-3" label_grid="col-12" input_grid="col-12" />
                <x-admin.form.textarea name="protocol_param" rows="3" :label="trans('model.node.protocol_param')" container="form-group col-md-6" label_grid="col-12"
                                       input_grid="col-12" />
                <x-admin.form.textarea name="obfs_param" rows="3" :label="trans('model.node.obfs_param')" container="form-group col-md-6" label_grid="col-12"
                                       input_grid="col-12" />
                <div class="col-md-6">
                    <x-admin.form.textarea name="content" rows="25" :placeholder="trans('admin.tools.convert.content_placeholder')" autofocus input_grid="col-12" />
                </div>
                <div class="col-md-6">
                    <x-admin.form.textarea name="result" rows="25" onclick="this.focus();this.select()" readonly="readonly" input_grid="col-12" />
                </div>
                <div class="col-md-6">
                    <button class="btn btn-block btn-primary" onclick="Convert()">{{ trans('common.convert') }}</button>
                </div>
                <div class="col-md-6">
                    <a class="btn btn-block btn-danger" href="{{ route('admin.tools.download', ['type' => 1]) }}">{{ trans('common.download') }}</a>
                </div>
            </div>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        // 转换
        function Convert() {
            const content = $("#content").val();

            if (content.trim() === "") {
                showMessage({
                    title: '{{ trans('admin.tools.convert.content_placeholder') }}',
                    icon: "warning",
                    timer: 1000
                });
                return;
            }

            showConfirm({
                title: '{{ trans('admin.confirm.continues') }}',
                onConfirm: function() {
                    // 使用 collectFormData 收集表单数据
                    ajaxPost('{{ route('admin.tools.convert') }}', collectFormData('.panel-body'), {
                        success: function(ret) {
                            if (ret.status === "success") {
                                $("#result").val(ret.data);
                            } else {
                                $("#result").val(ret.message);
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
