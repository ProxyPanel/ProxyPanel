@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">用户分组控制<small>（同一节点可分配至多个分组，一个用户只能属于一个分组；对于用户可见/可用节点：先按分组后按等级）</small></h2>
                @can('admin.user.group.create')
                    <div class="panel-actions">
                        <a class="btn btn-primary" href="{{route('admin.user.group.create')}}">
                            <i class="icon wb-plus" aria-hidden="true"></i>添加分组
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 分组名称</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td> {{$vo->name}} </td>
                            <td>
                                @canany(['admin.user.group.edit', 'admin.user.group.destroy'])
                                    <div class="btn-group">
                                        @can('admin.user.group.edit')
                                            <a href="{{route('admin.user.group.edit',$vo)}}" class="btn btn-primary">
                                                <i class="icon wb-edit" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('admin.user.group.destroy')
                                            <button onclick="deleteUserGroup('{{route('admin.user.group.destroy',$vo)}}')" class="btn btn-danger">
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
                        共 <code>{{$list->total()}}</code> 个分组
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$list->links()}}
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

    @can('admin.user.group.edit')
        <script>
          // 删除用户分组
          function deleteUserGroup(url) {
            swal.fire({
              title: '提示',
              text: '确定删除该分组吗?',
              icon: 'info',
              showCancelButton: true,
              cancelButtonText: '{{trans('home.ticket_close')}}',
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
