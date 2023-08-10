@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {!! trans('admin.tools.convert.title') !!}
                </h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="method">{{ trans('model.node.method') }}</label>
                        <select class="form-control" name="method" id="method">
                            @foreach (Helpers::methodList() as $method)
                                <option value="{{$method->name}}" @if($method->is_default) selected @endif>
                                    {{$method->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="transfer_enable">{{ trans('model.user.usable_traffic') }}</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="transfer_enable" value="1000" id="transfer_enable" placeholder="" required>
                            <span class="input-group-text">GB</span>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="protocol">{{ trans('model.node.protocol') }}</label>
                        <select class="form-control" name="protocol" id="protocol">
                            @foreach (Helpers::protocolList() as $protocol)
                                <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>
                                    {{$protocol->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="protocol_param">{{ trans('model.node.protocol_param') }}</label>
                        <input type="text" class="form-control" name="protocol_param" id="protocol_param" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="obfs">{{ trans('model.node.obfs') }}</label>
                        <select class="form-control" name="obfs" id="obfs">
                            @foreach (Helpers::obfsList() as $obfs)
                                <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>
                                    {{$obfs->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="obfs_param">{{ trans('model.node.obfs_param') }}</label>
                        <input type="text" class="form-control" name="obfs_param" id="obfs_param" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="22" name="content" id="content" placeholder="{{ trans('admin.tools.convert.content_placeholder') }}"
                                  autofocus></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="22" name="result" id="result" onclick="this.focus();this.select()" readonly="readonly"></textarea>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-block btn-primary" onclick="Convert()">{{ trans('common.convert') }}</button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{route('admin.tools.download', ['type' => 1])}}" class="btn btn-block btn-danger">{{ trans('common.download') }}</a>
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
          swal.fire({
            title: '{{ trans('admin.tools.convert.content_placeholder') }}',
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
          cancelButtonText: '{{ trans('common.close') }}',
          confirmButtonText: '{{ trans('common.confirm') }}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('admin.tools.convert')}}',
              dataType: 'json',
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
