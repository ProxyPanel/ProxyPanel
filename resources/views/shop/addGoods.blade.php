@extends('admin.layouts')
@section('css')
	<link rel="stylesheet" href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css">
	{{--<link rel="stylesheet" href="/theme/global/vendor/dropify/dropify.min.css">--}}
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">添加商品</h2>
			</div>
			@if (Session::has('successMsg'))
				<div class="alert alert-success">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					{{Session::get('successMsg')}}
				</div>
			@elseif ($errors->any())
				<div class="alert alert-danger">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					<strong>错误：</strong> {{$errors->first()}}
				</div>
			@else
				<div class="alert alert-info" role="alert">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					<strong>警告：</strong>用户购买新套餐则会覆盖所有已购但未过期的旧套餐并删除这些旧套餐对应的流量，所以设置商品时请务必注意类型和有效期，流量包则可叠加。
				</div>
			@endif
			<div class="panel-body">
				<form action="/shop/addGoods" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
					<div class="row">
						<div class="col-md-12 col-lg-6">
							<div class="form-group row">
								<label for="type" class="col-form-label col-md-2">类型</label>
								<ul class="col-md-9 list-unstyled list-inline">
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="type" value="1" checked>
											<label>流量包</label>
										</div>
									</li>
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="type" value="2">
											<label>套餐</label>
										</div>
									</li>
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="type" value="3">
											<label>充值</label>
										</div>
									</li>
								</ul>
								<span class="text-help offset-md-2"> 套餐与账号有效期有关，流量包只扣可用流量，不影响有效期 </span>
							</div>
							<div class="form-group row">
								<label for="name" class="col-form-label col-md-2">名称</label>
								<div class="col-md-7">
									<input type="text" class="form-control" name="name" value="{{Request::old('name')}}" id="name" placeholder="" required>
									<input type="hidden" name="_token" value="{{csrf_token()}}"/>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-form-label col-md-2">描述</label>
								<div class="col-md-8">
									<textarea class="form-control" rows="2" name="info" id="info" placeholder="商品的简单描述">{{Request::old('info')}}</textarea>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-form-label col-md-2">列表</label>
								<div class="col-md-8">
									<textarea class="form-control" rows="4" name="desc" id="desc" placeholder="商品的列表添加">{{Request::old('desc')}}</textarea>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-form-label col-md-2">售价</label>
								<div class="input-group col-md-6">
									<input type="text" class="form-control" name="price" value="{{Request::old('price')}}" id="price" placeholder="" required>
									<span class="input-group-text">元</span>
								</div>
							</div>
							<div class="form-group row package-money">
								<label for="traffic" class="col-form-label col-md-2">内含流量</label>
								<div class="input-group col-md-7">
									<input type="text" class="form-control" name="traffic" value="1024" id="traffic" placeholder="" required="">
									<span class="input-group-text">MB</span>
								</div>
								<span class="text-help offset-md-2"> 提交后不可修改 </span>
							</div>
							<div class="form-group row package-money">
								<label for="labels" class="col-md-2 col-form-label">标签</label>
								<div class="col-md-8">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control show-tick" id="labels" name="labels[]" multiple>
										@foreach($label_list as $label)
											<option value="{{$label->id}}">{{$label->name}}</option>
										@endforeach
									</select>
								</div>
								<span class="text-help offset-md-2"> 自动给购买此商品的用户打上相应的标签 </span>
							</div>
						</div>
						<div class="col-md-12 col-lg-6">
							<div class="form-group row package-money">
								<label for="days" class="col-form-label col-md-2">有效期</label>
								<div class="input-group col-md-3">
									<input type="text" class="form-control" name="days" value="30" id="days" required/>
									<span class="input-group-text">天</span>
								</div>
								<span class="text-help offset-md-3"> 到期后会自动从总流量扣减对应的流量，添加后不可修改 </span>
							</div>
							<div class="form-group row package-money">
								<label for="sort" class="col-form-label col-md-2">排序</label>
								<div class="col-md-6">
									<input type="text" class="form-control col-md-3" name="sort" value="{{Request::old('sort')}}" id="sort"/>
								</div>
								<span class="text-help offset-md-3"> 值越大排越前 </span>
							</div>
							<div class="form-group row package-money">
								<label for="color" class="col-md-2 col-form-label">颜色</label>
								<div class="col-md-3">
									<select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="color" id="color">
										<option class="bg-red-700 text-white" value="red">红</option>
										<option class="bg-pink-700 text-white" value="pink">粉红</option>
										<option class="bg-purple-700 text-white" value="purple">紫</option>
										<option class="bg-indigo-700 text-white" value="indigo">靛青</option>
										<option class="bg-blue-700 text-white" value="blue" selected>蓝</option>
										<option class="bg-cyan-700 text-white" value="cyan">青</option>
										<option class="bg-teal-700 text-white" value="teal">深藍綠</option>
										<option class="bg-green-700 text-white" value="green">绿</option>
										<option class="bg-light-green-700 text-white" value="light-green">浅绿</option>
										<option class="bg-yellow-700 text-white" value="yellow">黄</option>
										<option class="bg-orange-700 text-white" value="orange">橙</option>
										<option class="bg-brown-700 text-white" value="brown">棕</option>
										<option class="bg-grey-700 text-white" value="grey">灰</option>
										<option class="bg-blue-grey-700 text-white" value="blue-grey">蓝灰</option>
									</select>
								</div>
							</div>
							<div class="form-group row package-money">
								<label for="is_hot" class="col-md-2 col-form-label">热销</label>
								<ul class="col-md-9 list-unstyled list-inline">
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="is_hot" value="1">
											<label>是</label>
										</div>
									</li>
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="is_hot" value="0" checked>
											<label>否</label>
										</div>
									</li>
								</ul>
							</div>
							<div class="form-group row">
								<label for="is_limit" class="col-md-2 col-form-label">限购</label>
								<ul class="col-md-9 list-unstyled list-inline">
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="is_limit" value="1">
											<label>是</label>
										</div>
									</li>
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="is_limit" value="0" checked>
											<label>否</label>
										</div>
									</li>
								</ul>
							</div>
							<div class="form-group row last">
								<label for="status" class="col-form-label col-md-2">状态</label>
								<ul class="col-md-9 list-unstyled list-inline">
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="status" value="1" checked>
											<label>上架</label>
										</div>
									</li>
									<li class="list-inline-item">
										<div class="radio-custom radio-primary">
											<input type="radio" name="status" value="0">
											<label>下架</label>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="form-actions col-md-12">
						<button type="submit" class="btn btn-success"><i class="icon wb-check"></i> 提 交</button>
					</div>
				</form>
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
	<script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
	{{--<script src="/theme/global/vendor/dropify/dropify.min.js"></script>--}}
	{{--<script src="/theme/global/js/Plugin/dropify.js"></script>--}}

	<script type="text/javascript">
        // 选择商品类型
        $("input[name='type']").change(function () {
            var type = $(this).val();
            if (type == 3) {
                $(".package-money").hide();
            } else {
                $(".package-money").show();
            }
        });
	</script>
@endsection