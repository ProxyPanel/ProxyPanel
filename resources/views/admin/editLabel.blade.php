@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">编辑标签</h2>
            </div>
            @if (Session::has('errorMsg'))
                <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    <strong>错误：</strong> {{Session::get('errorMsg')}}
                </div>
            @endif
            <div class="panel-body">
                <form action="/admin/editLabel" method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return doSubmit();">
                    <div class="form-group row">
                        <label for="name" class="col-form-label col-md-1">标签</label>
                        <input type="text" class="form-control col-md-6" name="name" id="name" value="{{$label->name}}" autofocus required>
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                    </div>
                    <div class="form-group row">
                        <label for="sort" class="col-form-label col-md-1">排序</label>
                        <input type="text" class="form-control col-md-6" name="sort" id="sort" value="{{$label->sort}}" required/>
                        <span class="text-help offset-md-1"> 排序值越高显示时越靠前 &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;</span>
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
        function doSubmit() {
			const _token = '{{csrf_token()}}';
			const id = '{{$label->id}}';
			const name = $('#name').val();
			const sort = $('#sort').val();

			$.ajax({
                type: "POST",
                url: "/admin/editLabel",
                async: false,
                data: {_token: _token, id: id, name: name, sort: sort},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                            .then(() => window.location.href = '/admin/labelList')
                    } else {
                        swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                    }
                }
            });
            return false;
        }
    </script>
@endsection