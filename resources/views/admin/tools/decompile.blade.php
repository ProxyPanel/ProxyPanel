@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.menu.tools.decompile') }}</h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <textarea class="form-control" id="content" name="content" rows="25" placeholder="{{ trans('admin.tools.decompile.content_placeholder') }}" autofocus></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" id="result" name="result" rows="25" readonly="readonly"></textarea>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-block btn-primary" onclick="Decompile()">{{ trans('admin.tools.decompile.attribute') }}</button>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-block btn-danger" href="{{ route('admin.tools.download', ['type' => 2]) }}">{{ trans('common.download') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        // 转换
        function Decompile() {
            const content = $("#content").val();

            if (content.trim() === "") {
                swal.fire({
                    title: '{{ trans('admin.tools.decompile.content_placeholder') }}',
                    icon: "warning",
                    timer: 1000,
                    showConfirmButton: false
                });
                return;
            }
            swal.fire({
                title: '{{ trans('admin.confirm.continues') }}',
                icon: "question",
                allowEnterKey: false,
                showCancelButton: true,
                cancelButtonText: '{{ trans('common.close') }}',
                confirmButtonText: '{{ trans('common.confirm') }}'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: "POST",
                        url: '{{ route('admin.tools.decompile') }}',
                        dataType: "json",
                        data: {
                            _token: '{{ csrf_token() }}',
                            content: content
                        },
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
            return false;
        }
    </script>
@endsection
