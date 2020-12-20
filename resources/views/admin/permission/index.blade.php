@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">权限行为列表</h2>
                @can('admin.permission.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.permission.create')}}" class="btn btn-outline-primary">
                            <i class="icon wb-plus" aria-hidden="true"></i>添加权限行为
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 行为</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{$permission->id}}</td>
                            <td>{{$permission->description}}</td>
                            <td>{{$permission->name}}</td>
                            <td>
                                @canany(['admin.permission.edit', 'admin.permission.destroy'])
                                    <div class="btn-group">
                                        @can('admin.permission.edit')
                                            <a class="btn btn-sm btn-outline-primary" href="{{route('admin.permission.edit', $permission)}}">
                                                <i class="icon wb-edit"></i></a>
                                        @endcan
                                        @can('admin.permission.destroy')
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="delPermission('{{route('admin.permission.destroy', $permission)}}','{{$permission->name}}')">
                                                <i class="icon wb-trash"></i></button>
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
                        共 <code>{{$permissions->total()}}</code> 条权限行为
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$permissions->links()}}
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
    @can('admin.permission.destroy')
        <script>
          function delPermission(url, name) {
            swal.fire({
              title: '警告',
              text: '确定删除 【' + name + '】 权限行为？',
              icon: 'warning',
              showCancelButton: true,
              cancelButtonText: '{{trans('home.close')}}',
              confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
              if (result.value) {
                $.ajax({
                  method: 'DELETE',
                  url: url,
                  data: {_token: '{{csrf_token()}}'},
                  dataType: 'json',
                  success: function(ret) {
                    if (ret.status === 'success') {
                      swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
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