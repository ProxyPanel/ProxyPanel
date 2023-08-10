@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{!! trans('admin.tools.decompile.title') !!}</h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <textarea class="form-control" rows="25" name="content" id="content" placeholder="{{ trans('admin.tools.decompile.content_placeholder') }}"
                                  autofocus></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="25" name="result" id="result" readonly="readonly"></textarea>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-block btn-primary" onclick="Decompile()">{{ trans('admin.tools.decompile.attribute') }}</button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{route('admin.tools.download', ['type' => 2])}}" class="btn btn-block btn-danger">{{ trans('common.download') }}</a>
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
        const content = $('#content').val();

        if (content.trim() === '') {
          swal.fire({
            title: '{{ trans('admin.tools.decompile.content_placeholder') }}',
            icon: 'warning',
            timer: 1000,
            showConfirmButton: false,
          });
          return;
        }
        swal.fire({
          title: '{{ trans('admin.confirm.continues') }}',
          icon: 'question',
          allowEnterKey: false,
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('admin.tools.decompile')}}',
              dataType: 'json',
              data: {_token: '{{csrf_token()}}', content: content},
              success: function(ret) {
                if (ret.status === 'success') {
                  $('#result').val(ret.data);
                } else {
                  $('#result').val(ret.message);
                }
              },
            });
          }
        });
        return false;
      }
    </script>
@endsection
