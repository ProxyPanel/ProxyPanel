@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">反解析</h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <textarea class="form-control" rows="25" name="content" id="content" placeholder="请填入要反解析的ShadowsocksR链接，一行一条" autofocus></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="25" name="result" id="result" readonly="readonly"></textarea>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-block btn-primary" onclick="Decompile()">反解析</button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{route('admin.tools.download', ['type' => 2])}}" class="btn btn-block btn-danger">下 载</a>
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
          swal.fire({title: '请填入要反解析的链接信息', icon: 'warning', timer: 1000, showConfirmButton: false});
          return;
        }
        swal.fire({
          title: '确定继续反解析吗？',
          icon: 'question',
          allowEnterKey: false,
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('admin.tools.decompile')}}',
              async: false,
              data: {_token: '{{csrf_token()}}', content: content},
              dataType: 'json',
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
