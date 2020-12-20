@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">格式转换
                    <small>Shadowsocks 转 ShadowsocksR</small>
                </h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="method">加密方式</label>
                        <select class="form-control" name="method" id="method">
                            @foreach (Helpers::methodList() as $method)
                                <option value="{{$method->name}}" @if($method->is_default) selected @endif>
                                    {{$method->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="transfer_enable">可用流量</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="transfer_enable" value="1000" id="transfer_enable" placeholder="" required>
                            <span class="input-group-text">GB</span>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="protocol">协议</label>
                        <select class="form-control" name="protocol" id="protocol">
                            @foreach (Helpers::protocolList() as $protocol)
                                <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>
                                    {{$protocol->name}}
                                </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-4 form-group">
                        <label for="protocol_param">协议参数</label>
                        <input type="text" class="form-control" name="protocol_param" id="protocol_param" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="obfs">混淆</label>
                        <select class="form-control" name="obfs" id="obfs">
                            @foreach (Helpers::obfsList() as $obfs)
                                <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>
                                    {{$obfs->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="obfs_param">混淆参数</label>
                        <input type="text" class="form-control" name="obfs_param" id="obfs_param" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="22" name="content" id="content" placeholder="请填入要转换的配置信息" autofocus></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="22" name="result" id="result" onclick="this.focus();this.select()" readonly="readonly"></textarea>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-block btn-primary" onclick="Convert()">转 换</button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{route('admin.tools.download', ['type' => 1])}}" class="btn btn-block btn-danger">下 载</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('javascript')
    <script>
      // 转换
      function Convert() {
        const content = $('#content').val();

        if (content.trim() === '') {
          swal.fire({title: '请填入要转换的配置信息', icon: 'warning', timer: 1000, showConfirmButton: false});
          return;
        }
        swal.fire({
          title: '确定继续转换吗？',
          icon: 'question',
          allowEnterKey: false,
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('admin.tools.convert')}}',
              async: false,
              data: {
                _token: '{{csrf_token()}}',
                method: $('#method').val(),
                transfer_enable: $('#transfer_enable').val(),
                protocol: $('#protocol').val(),
                protocol_param: $('#protocol_param').val(),
                obfs: $('#obfs').val(),
                obfs_param: $('#obfs_param').val(),
                content: content,
              },
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
