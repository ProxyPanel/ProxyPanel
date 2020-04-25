@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h3 class="panel-title">节点分组</h3>
				<div class="panel-actions">
					<a href="/admin/addGroup" class="btn btn-primary"><i class="icon wb-plus"></i>添加分组</a>
				</div>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 分组名称</th>
						<th> 分组级别</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach($groupList as $group)
						<tr>
							<td> {{$group->id}} </td>
							<td> {{$group->name}} </td>
							<td> {{$levelMap[$group->level]}} </td>
							<td>
								<div class="btn-group">
									<a href="/admin/editGroup/{{$group->id}}" class="btn btn-primary"><i class="icon wb-edit"></i></a>
									<button class="btn btn-danger" onclick="delGroup('{{$group->id}}','{{$group->name}}')"><i class="icon wb-trash"></i></button>
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$groupList->total()}}</code> 个节点分组
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$groupList->links()}}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
	<script type="text/javascript">
        // 删除节点分组
        function delGroup(id, name) {
            swal.fire({
                title: '警告',
                text: '确定删除分组 【' + name + '】 ?',
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
