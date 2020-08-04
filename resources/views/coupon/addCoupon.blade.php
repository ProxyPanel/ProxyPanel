@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/dropify/dropify.min.css">
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css">
	<style>
		.hidden {
			display: none
		}
	</style>
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">生成卡券</h2>
			</div>
			@if (Session::has('successMsg'))
				<div class="alert alert-success alert-dismissible">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					{{Session::get('successMsg')}}
				</div>
			@endif
			@if($errors->any())
				<div class="alert alert-danger alert-dismissible">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					<strong>错误：</strong> {{$errors->first()}}
				</div>
			@endif
			<div class="panel-body">
				<form action="{{url('coupon/addCoupon')}}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
					<div class="form-group row">
						<label class="col-form-label col-md-3" for="name">卡券名称</label>
						<div class="col-md-9">
							<input type="text" class="form-control" name="name" id="name" value="{{Request::old('name')}}" required/>
							<input type="hidden" name="_token" value="{{csrf_token()}}"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-3" for="logo">LOGO</label>
						<div class="col-md-9">
							<input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="/assets/images/noimage.png"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-3" for="type">类型</label>
						<ul class="col-md-9 list-unstyled list-inline">
							<li class="list-inline-item">
								<div class="radio-custom radio-primary">
									<input type="radio" name="type" value="1" checked>
									<label>抵用券</label>
								</div>
							</li>
							<li class="list-inline-item">
								<div class="radio-custom radio-primary">
									<input type="radio" name="type" value="2">
									<label>充值券</label>
								</div>
							</li>
							<li class="list-inline-item">
								<div class="radio-custom radio-primary">
									<input type="radio" name="type" value="3">
									<label>折扣券</label>
								</div>
							</li>
						</ul>
					</div>
					<div class="coupon hidden">
						<div class="form-group row">
							<label class="col-form-label col-md-3" for="usage">用途</label>
							<ul class="col-md-9 list-unstyled list-inline">
								<li class="list-inline-item">
									<div class="radio-custom radio-primary">
										<input type="radio" name="usage" value="1" id="usage1" checked>
										<label>仅限一次性使用</label>
									</div>
								</li>
								<li class="list-inline-item">
									<div class="radio-custom radio-primary">
										<input type="radio" name="usage" value="2" id="usage2">
										<label>可重复使用</label>
									</div>
								</li>
							</ul>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-md-3" for="discount">折扣</label>
							<div class="input-group col-md-3">
								<input type="text" class="form-control" name="discount" value="{{Request::old('discount')}}" id="discount">
								<span class="input-group-text">折</span>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-3" for="amount">金额</label>
						<div class="input-group col-md-3">
							<input type="text" class="form-control" name="amount" value="{{Request::old('amount')}}" id="amount" required/>
							<span class="input-group-text">元</span>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-3" for="num">数量</label>
						<div class="input-group col-md-3">
							<input type="text" class="form-control" name="num" value="{{Request::old('num')}}" id="num" required/>
							<span class="input-group-text">张</span>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-form-label col-md-3">有效期</label>
						<div class="input-group col-md-7 input-daterange" data-plugin="datepicker">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
							</div>
							<input type="text" class="form-control" value="{{Request::old('available_start')}}" name="available_start" id="available_start" required/>
							<div class="input-group-prepend">
								<span class="input-group-text">至</span>
							</div>
							<input type="text" class="form-control" value="{{Request::old('available_end')}}" name="available_end" id="available_end" required/>
						</div>
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
	<script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<script src="/assets/global/vendor/dropify/dropify.min.js"></script>
	<script src="/assets/global/js/Plugin/bootstrap-datepicker.min.js"></script>
	<script src="/assets/global/js/Plugin/dropify.min.js"></script>

	<script type="text/javascript">
        $('.input-daterange>input').datepicker({
            format: "yyyy-mm-dd"
        });

        // 根据类型显示
        $("input[name='type']").change(function () {
            var type = $(this).val();
            if (type === '3') {
                $(".coupon").removeClass("hidden");
                $("#amount").parent("div").parent("div").addClass("hidden");
                $("#amount").removeAttr('required');
                $("#amount").val('');
                $("#discount").prop('required', 'required');
            } else {
                $(".coupon").addClass("hidden");
                $("#usage1").prop('checked', 'checked');
                $("#usage2").prop('checked', false);
                $("#amount").parent("div").parent("div").removeClass("hidden");
                $("#discount").removeAttr('required');
                $("#discount").val('');
            }
        });
	</script>
@endsection