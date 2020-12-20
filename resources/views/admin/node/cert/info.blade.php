@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">@isset($Dv) 编辑 @else 添加 @endisset域名证书</h2>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" onsubmit="return Submit()">
                    <div class="form-group row">
                        <label for="domain" class="col-md-3 col-form-label">域名</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="domain" id="domain" @isset($Dv) value="{{$Dv->domain}}" @endisset>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="key" class="col-md-3 col-form-label">Key</label>
                        <div class="col-md-9">
                            <textarea type="text" rows="10" class="form-control" name="key" id="key"
                                      placeholder="域名证书的KEY值，允许为空，VNET-V2Ray后端支持自动签证书">@isset($Dv) {{$Dv->key}} @endisset</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="pem" class="col-md-3 col-form-label">Pem</label>
                        <div class="col-md-9">
                            <textarea type="text" rows="10" class="form-control" name="pem" id="pem"
                                      placeholder="域名证书的PEM值，允许为空，VNET-V2Ray后端支持自动签证书">@isset($Dv) {{$Dv->pem}} @endisset</textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
      // 添加域名证书
      function Submit() {
        $.ajax({
          method: @isset($Dv) 'PUT' @else 'POST' @endisset,
          url: @isset($Dv) '{{route('admin.node.cert.update', $Dv->id)}}' @else '{{route('admin.node.cert.store')}}' @endisset,
          async: false,
          data: {_token: '{{csrf_token()}}', domain: $('#domain').val(), key: $('#key').val(), pem: $('#pem').val()},
          dataType: 'json',
          success: function(ret) {
            if (ret.status === 'success') {
              swal.fire({
                title: ret.message,
                icon: 'success',
                timer: 1000,
                showConfirmButton: false,
              }).then(() => window.location.href = '{{route('admin.node.cert.index')}}');
            } else {
              swal.fire({title: '[错误 | Error]', text: ret.message, icon: 'error'});
            }
          },
        });
        return false;
      }
    </script>
@endsection
