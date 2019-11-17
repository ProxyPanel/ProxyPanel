@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">敏感词列表
					<small>（用于屏蔽注册邮箱后缀）</small>
				</h2>
				<div class="panel-actions">
					<button class="btn btn-primary" data-toggle="modal" data-target="#add_sensitive_words"> 添加敏感词</button>
				</div>
			</div>
			<div class="panel-body">
				<table class="table text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 敏感词</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@if($list->isEmpty())
						<tr>
							<td colspan="3">暂无数据</td>
						</tr>
					@else
						@foreach($list as $vo)
							<tr>
								<td> {{$vo->id}} </td>
								<td> {{$vo->words}} </td>
								<td>
									<button class="btn btn-danger" onclick="delWord('{{$vo->id}}')">
										<i class="icon wb-trash"></i>
									</button>
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
						共 <code>{{$list->total()}}</code> 条记录
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

	<div id="add_sensitive_words" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title"> 添加敏感词 </h4>
				</div>
				<div class="modal-body">
					<input type="text" name="words" id="words" placeholder="请填入敏感词" class="form-control"/>
				</div>
				<div class="modal-footer">
					<button data-dismiss="modal" class="btn btn-danger"> 关 闭</button>
					<button data-dismiss="modal" class="btn btn-success" onclick="addSensitiveWords()"> 提 交</button>
				</div>
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>

	<script type="text/javascript">
        // 添加敏感词
        function addSensitiveWords() {
            const words = $('#words').val();

            if (words.trim() === '') {
                swal.fire({title: '敏感词不能为空', type: 'warning', timer: 1000, showConfirmButton: false});
                $("#words").focus();
                return false;
            }

            $.post("/sensitiveWords/add", {_token: '{{csrf_token()}}', words: words}, function (ret) {
                if (ret.status === 'success') {
                    swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                        .then(() => window.location.reload())
                } else {
                    swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                }
            });
        }


        // 删除敏感词
        function delWord(id) {
            swal.fire({
                title: '警告',
                text: '确定删除该敏感词',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonText: '确定',
            }).then((result) => {
                if (result.value) {
                    $.post("/sensitiveWords/del", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    })
                }
            })
        }
	</script>
@endsection
