@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{!! trans('admin.node.cert.title') !!}</h2>
                @can('admin.node.cert.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.node.cert.create')}}" class="btn btn-primary">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.node_cert.domain') }}</th>
                        <th> {{ trans('model.node_cert.key') }}</th>
                        <th> {{ trans('model.node_cert.pem') }}</th>
                        <th> {{ trans('model.node_cert.issuer') }}</th>
                        <th> {{ trans('model.node_cert.signed_date') }}</th>
                        <th> {{ trans('model.node_cert.expired_date') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($certs as $cert)
                        <tr>
                            <td> {{$cert->id}} </td>
                            <td> {{$cert->domain}} </td>
                            <td> {{$cert->key ? '✔️' : '❌'}} </td>
                            <td> {{$cert->pem ? '✔️' : '❌'}} </td>
                            <td> {{$cert->issuer}} </td>
                            <td> {{$cert->from}} </td>
                            <td> {{$cert->to}} </td>
                            <td>
                                @canany(['admin.node.cert.edit', 'admin.node.cert.destroy'])
                                    <div class="btn-group">
                                        @can('admin.node.cert.edit')
                                            <a href="{{route('admin.node.cert.edit', $cert)}}" class="btn btn-primary">
                                                <i class="icon wb-edit" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('admin.node.cert.destroy')
                                            <button onclick="delCertificate('{{$cert->id}}')" class="btn btn-danger">
                                                <i class="icon wb-trash" aria-hidden="true"></i>
                                            </button>
                                        @endcan
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.node.cert.counts', ['num' => $certs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$certs->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    @can('admin.node.cert.destroy')
        <script>
          // 删除授权
          function delCertificate(id) {
            swal.fire({
              title: '{{ trans('admin.hint') }}',
              text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.node_cert.attribute')]) }}' + id +
                  '{{ trans('admin.confirm.delete.1') }}',
              icon: 'info',
              showCancelButton: true,
              cancelButtonText: '{{ trans('common.close') }}',
              confirmButtonText: '{{ trans('common.confirm') }}',
            }).then((result) => {
              if (result.value) {
                $.ajax({
                  method: 'DELETE',
                  url: '{{route('admin.node.cert.destroy', '')}}/' + id,
                  data: {_token: '{{csrf_token()}}'},
                  dataType: 'json',
                  success: function(ret) {
                    if (ret.status === 'success') {
                      swal.fire({
                        title: ret.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false,
                      }).then(() => window.location.reload());
                    } else {
                      swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                  },
                });
              }
            });
          }
        </script>
    @endcan
@endsection
