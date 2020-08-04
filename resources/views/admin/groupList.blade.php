@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">节点分组</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary" onclick="addGroup()"><i class="icon wb-plus"></i>添加分组</button>
                </div>
            </div>
            <div class="panel-body">
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 分组名称</th>
                        <th> 分组级别</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($groupList->isEmpty())
                        <tr>
                            <td colspan="4">暂无数据</td>
                        </tr>
                    @else
                        @foreach($groupList as $group)
                            <tr>
                                <td> {{$group->id}} </td>
                                <td> {{$group->name}} </td>
                                <td> {{$levelMap[$group->level]}} </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-primary" onclick="editGroup('{{$group->id}}')"><i class="icon wb-edit"></i></button>
                                        <button class="btn btn-danger" onclick="delGroup('{{$group->id}}')"><i class="icon wb-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4 col-sm-4">
                        共 {{$groupList->total()}} 个节点分组
                    </div>
                    <div class="col-md-8 col-sm-8">
                        <div class="Page navigation float-right">
                            {{ $groupList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script type="text/javascript">
        // 添加节点分组
        function addGroup() {
            window.location.href = '/admin/addGroup';
        }

        // 编辑节点分组
        function editGroup(id) {
            window.location.href = '/admin/editGroup/' + id;
        }

        // 删除节点分组
        function delGroup(id) {
            swal.fire({
                title: '警告',
                text: '确定删除分组?',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delGroup/" + id, {_token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            });
        }
    </script>
@endsection