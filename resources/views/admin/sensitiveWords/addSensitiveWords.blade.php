@extends('admin.layouts')
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">添加敏感词</h2>
			</div>
			@if (Session::has('errorMsg'))
				<div class="alert alert-danger">
					<button class="close" data-dismiss="alert" aria-label="Close"><span
								aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span>
					</button>
					<strong>错误：</strong> {{Session::get('errorMsg')}}
				</div>
			@endif
			<div class="panel-body">
				<form action="#" method="post" enctype="multipart/form-data" class="form-horizontal" role="form"
						onsubmit="return Submit()">
					<div class="form-group row">
						<label class="col-form-label col-md-2">敏感词</label>
						<div class="col-md-5">
							<input type="text" class="form-control" name="words" id="words" required/>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn btn-success">提 交</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="charge_modal" class="modal fade" aria-labelledby="charge_modal" role="dialog" tabindex="-1"
			aria-hidden="true">
		<div class="modal-dialog modal-simple modal-center">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
								aria-hidden="true">×</span></button>
					<h4 class="modal-title"> {{trans('home.ticket_table_new_button')}} </h4>
				</div>
				<div class="modal-body">
					<input type="text" name="title" id="title" placeholder="{{trans('home.ticket_table_title')}}"
							class="form-control mb-20"/>
					<textarea name="content" id="content" placeholder="{{trans('home.ticket_table_new_desc')}}"
							class="form-control mb-20" rows="4"></textarea>
				</div>
				<div class="modal-footer">
					<button data-dismiss="modal"
							class="btn btn-danger"> {{trans('home.ticket_table_new_cancel')}} </button>
					<button class="btn btn-primary"
							onclick="addTicket()"> {{trans('home.ticket_table_new_yes')}} </button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script type="text/javascript">
		// ajax同步提交
		function Submit() {
			$.ajax({
				type: "POST",
				url: "/admin/addSensitiveWords",
				async: false,
				data: {_token: '{{csrf_token()}}', words: $('#words').val()},
				dataType: 'json',
				success: function (ret) {
					swal.fire({title: ret.message, timer: 1000, showConfirmButton: false,})
						.then(() => {
							window.location.href = '/admin/sensitiveWordsList';
						})
				}
			});
			return false;
		}
	</script>
@endsection
