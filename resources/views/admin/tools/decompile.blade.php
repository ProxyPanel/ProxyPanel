@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel :title="trans('admin.menu.tools.decompile')">
            <div class="form-row">
                <div class="col-md-6">
                    <x-admin.form.textarea name="content" rows="25" :placeholder="trans('admin.tools.decompile.content_placeholder')" autofocus input_grid="col-12" />
                </div>
                <div class="col-md-6">
                    <x-admin.form.textarea name="result" rows="25" onclick="this.focus();this.select()" readonly="readonly" input_grid="col-12" />
                </div>
                <div class="col-md-6">
                    <button class="btn btn-block btn-primary" onclick="Decompile()">{{ trans('admin.tools.decompile.attribute') }}</button>
                </div>
                <div class="col-md-6">
                    <a class="btn btn-block btn-danger" href="{{ route('admin.tools.download', ['type' => 2]) }}">{{ trans('common.download') }}</a>
                </div>
            </div>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script>
        // 转换
        function Decompile() {
            const content = $("#content").val();

            if (content.trim() === "") {
                showMessage({
                    title: '{{ trans('admin.tools.decompile.content_placeholder') }}',
                    icon: "warning",
                    timer: 1000
                });
                return;
            }

            showConfirm({
                title: '{{ trans('admin.confirm.continues') }}',
                onConfirm: function() {
                    ajaxPost('{{ route('admin.tools.decompile') }}', {
                        content: content
                    }, {
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
