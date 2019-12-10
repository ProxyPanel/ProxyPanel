@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/dropify/dropify.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/summernote/summernote.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">添加文章</h2>
			</div>
			@if($errors->any())
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					{{$errors->first()}}
				</div>
			@endif
			<div class="panel-body">
				<form action="/admin/addArticle" method="post" enctype="multipart/form-data" class="form-horizontal">
					<div class="form-group row">
						<label class="col-form-label col-md-2" for="type">类型</label>
						<div class="col-md-10 d-flex align-items-center">
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="type" value="1" checked/>
								<label for="type">文章</label>
							</div>
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="type" value="2"/>
								<label for="type">公告</label>
							</div>
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="type" value="3" disabled/>
								<label for="type">购买说明</label>
							</div>
							<div class="radio-custom radio-primary radio-inline">
								<input type="radio" name="type" value="4" disabled/>
								<label for="type">使用教程</label>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-2" for="title">标题</label>
						<div class="col-md-4">
							<input type="text" class="form-control" name="title" id="title" autofocus required/>
							{{csrf_field()}}
						</div>
					</div>
					<div class="form-group row" id="summary">
						<label class="col-form-label col-md-2" for="summary">简介</label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="summary" id="summary"/>
						</div>
					</div>
					<div class="form-group row" id="sort">
						<label class="col-form-label col-md-2" for="sort">排序</label>
						<div class="col-md-2">
							<input type="number" class="form-control" name="sort" id="sort" value="0" required/>
						</div>
						<span class="text-help"> 值越高显示时越靠前 </span>
					</div>
					<div class="form-group row" id="all_logo">
						<label class="col-form-label col-md-2" for="logo">LOGO</label>
						<div class="col-md-4" id="icon" style="display: none;">
							<input type="text" name="logo" id="logo" class="form-control"/>
							<span class="text-help"><a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">图标列表</a> | 格式： fa-windows</span>
						</div>

						<div class="col-md-4" id="logoUpload">
							<input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="/assets/images/noimage.png"/>
							<span class="text-help"> 推荐尺寸：100x75 </span>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-2" for="summernote">内容</label>
						<div class="col-md-10">
							<textarea class="form-control" name="content" id="summernote" data-plugin="summernote" rows="15"> </textarea>
						</div>
					</div>
					<div class="form-actions text-right">
						<div class="btn-group">
							<a href="/admin/articleList" class="btn btn-danger">返 回</a>
							<button type="submit" class="btn btn-success">提 交</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/dropify/dropify.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/summernote/summernote.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/dropify.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/summernote.js" type="text/javascript"></script>
	<script type="text/javascript">
        $("input:radio[name='type']").on('change', function () {
            switch (parseInt($(this).val())) {
                case 1:
                    $("#summary").show();
                    $("#sort").show();
                    $("#all_logo").show();
                    $("#icon").hide();
                    $("#logoUpload").show();
                    break;
                case 2:
                    $("#summary").hide();
                    $("#sort").hide();
                    $("#all_logo").hide();
                    break;
                case 3:
                    $("#summary").hide();
                    $("#sort").show();
                    $("#all_logo").show();
                    $("#icon").show();
                    $("#logoUpload").hide();
                    break;
                case 4:
                    $("#summary").hide();
                    $("#sort").hide();
                    $("#all_logo").show();
                    $("#icon").show();
                    $("#logoUpload").hide();
                    break;
                default:
            }
        });
	</script>
@endsection
