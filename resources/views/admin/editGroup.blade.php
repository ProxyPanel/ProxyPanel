@extends('admin.layouts')
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">编辑节点分组</h2>
			</div>
			@if (Session::has('errorMsg'))
				<div class="alert alert-danger">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					<strong>错误：</strong> {{Session::get('errorMsg')}}
				</div>
			@endif
			<div class="panel-body">
				<form action="#" method="post" enctype="multipart/form-data" class="form-horizontal" role="form" onsubmit="return do_submit();">
					<div class="form-group row">
						<label class="col-form-label col-md-1">分组名称</label>
						<input type="text" class="form-control col-md-5" name="name" value="{{$group->name}}" id="name" autofocus required/>
						<input type="hidden" name="_token" value="{{csrf_token()}}"/>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-1">分组级别</label>
						<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control" name="level" id="level" required>
							@if(!$levelList->isEmpty())
								@foreach($levelList as $level)
									<option value="{{$level->level}}" {{$group->level == $level->level ? 'selected' : ''}}>{{$level->level_name}}</option>
								@endforeach
							@endif
						</select>
						<span class="text-help offset-md-1">暂时无用&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</span>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn btn-success">提交</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script type="text/javascript">
        // ajax同步提交
        function do_submit() {
            const _token = '{{csrf_token()}}';
			const name = $('#name').val();
			const level = $("#level option:selected").val();

            $.ajax({
                type: "POST",
                url: "admin/editGroup",
                async: false,
                data: {_token: _token, id: '{{$group->id}}', name: name, level: level},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time: 1000}, function () {
                        if (ret.status === 'success') {
                            window.location.href = 'admin/groupLis';
                        }
                    });
                }
            });

            return false;
        }
	</script>
@endsection