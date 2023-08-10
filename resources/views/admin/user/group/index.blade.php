@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{!! trans('admin.user.group.title') !!}</h2>
                @can('admin.user.group.create')
                    <div class="panel-actions">
                        <a class="btn btn-primary" href="{{route('admin.user.group.create')}}">
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
                        <th> {{ trans('admin.user.group.name') }}</th>
                        <th> {{ trans('common.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($groups as $group)
                        <tr>
                            <td> {{$group->id}} </td>
                            <td> {{$group->name}} </td>
                            <td>
                                @canany(['admin.user.group.edit', 'admin.user.group.destroy'])
                                    <div class="btn-group">
                                        @can('admin.user.group.edit')
                                            <a href="{{route('admin.user.group.edit',$group)}}" class="btn btn-primary">
                                                <i class="icon wb-edit" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('admin.user.group.destroy')
                                            <button onclick="deleteUserGroup('{{route('admin.user.group.destroy',$group)}}', '{{$group->name}}')" class="btn btn-danger">
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
                        {!! trans('admin.user.group.counts', ['num' => $groups->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$groups->links()}}
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
          function deleteUserGroup(url, name) {
            swal.fire({
              title: '{{ trans('admin.hint') }}',
              text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.user_group.attribute')]) }}' +
                  name + '{{ trans('admin.confirm.delete.1') }}',
              icon: 'info',
              showCancelButton: true,
              cancelButtonText: '{{ trans('common.close') }}',
              confirmButtonText: '{{ trans('common.confirm') }}',
            }).then((result) => {
              if (result.value) {
                $.ajax({
                  method: 'DELETE',
                  url: url,
                  data: {_token: '{{ csrf_token() }}'},
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
