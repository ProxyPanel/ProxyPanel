@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">标签列表
                    <small>标签影响用户查看/订阅节点信息（用户和节点通过标签进行关联）</small>
                </h3>
                <div class="panel-actions">
                    <button class="btn btn-primary" onclick="addLabel()"><i class="icon wb-plus"></i>添加标签</button>
                </div>
            </div>
            <div class="panel-body">
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 关联用户数</th>
                        <th> 关联节点数</th>
                        <th> 排序</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($labelList->isEmpty())
                        <tr>
                            <td colspan="6">暂无数据</td>
                        </tr>
                    @else
                        @foreach($labelList as $label)
                            <tr>
                                <td> {{$label->id}} </td>
                                <td> {{$label->name}} </td>
                                <td> {{$label->userCount}} </td>
                                <td> {{$label->nodeCount}} </td>
                                <td> {{$label->sort}} </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-primary" onclick="editLabel('{{$label->id}}')"><i class="icon wb-edit"></i></button>
                                        <button class="btn btn-danger" onclick="delLabel('{{$label->id}}')"><i class="icon wb-trash"></i></button>
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
                    <div class="col-sm-4">
                        共 {{$labelList->total()}} 个标签
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $labelList->links() }}
                        </nav>
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
        // 添加标签
        function addLabel() {
            window.location.href = '/admin/addLabel';
        }

        // 编辑标签
        function editLabel(id) {
            window.location.href = '/admin/editLabel?id=' + id + '&page={{Request::get('page', 1)}}';
        }

        // 删除标签
        function delLabel(id) {
            swal.fire({
                title: '警告',
                text: '确定删除标签?',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/admin/delLabel", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
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