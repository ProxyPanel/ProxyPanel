@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" type="text/css"
            rel="stylesheet">
    <style type="text/css">
        .text-help {
            padding-left: 1.0715rem;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">生成卡券</h1>
                <div class="panel-actions">
                    <a href="/coupon/couponList" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    {{Session::get('successMsg')}}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <span> {{$errors->first()}} </span>
                </div>
            @endif
            <div class="panel-body">
                <form action="/coupon/addCoupon" method="post" enctype="multipart/form-data" class="form-horizontal"
                        role="form">
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="name">卡券名称</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="name" id="name"
                                    value="{{Request::old('name')}}" required/>
                            {{csrf_field()}}
                        </div>
                        <span class="text-help"> 会用于前端显示 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sn">使用券码</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="sn" id="sn" value="{{Request::old('sn')}}"/>
                        </div>
                        <span class="text-help"> 提供给用户使用卡券的卡券，留空则默认为8位随机码 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="logo">卡券图片</label>
                        <div class="col-md-6">
                            <input type="file" id="logo" name="logo" data-plugin="dropify"
                                    data-default-file="/assets/images/default.png"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="type">类型</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1" checked/>
                                <label for="type">抵用券</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2"/>
                                <label for="type">折扣券</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="3"/>
                                <label for="type">充值券</label>
                            </div>
                        </div>
                        <span class="offset-md-2 text-help"> 抵用：抵扣商品金额，折扣：商品百分比打折，充值：充值用户账号余额 </span>
                    </div>
                    <div class="form-group row usage">
                        <label class="col-md-2 col-form-label" for="usage">用途</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="usage" id="usage1" value="1" checked/>
                                <label for="usage">一次性</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="usage" id="usage2" value="2"/>
                                <label for="usage">重复使用</label>
                            </div>
                        </div>
                        <span class="offset-md-2 text-help"> 一次性：任一用户使用后，卡券即可失效；重复使用：任何用户都可无限次使用直到有效期为止 </span>
                    </div>
                    <div class="form-group row discount" style="display: none;">
                        <label class="col-md-2 col-form-label" for="discount">折扣</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="discount" id="discount"
                                    value="{{Request::old('discount')}}" step="0.1"/>
                            <span class="input-group-text">折</span>
                        </div>
                        <span class="text-help"> 范围为 1 ~ 9.9折，即 10% ~ 99% </span>
                    </div>
                    <div class="form-group row amount">
                        <label class="col-md-2 col-form-label" for="amount">金额</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="amount" id="amount"
                                    value="{{Request::old('amount')}}" step="0.01" required/>
                            <span class="input-group-text">元</span>
                        </div>
                    </div>
                    <div class="form-group row usage">
                        <label class="col-md-2 col-form-label" for="rule">条件</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="rule" id="rule"
                                    value="{{Request::old('rule')}}" step="0.01" required/>
                            <span class="input-group-text">元</span>
                        </div>
                        <span class="text-help"> 当套餐超过N值时，才能使用本优惠劵；0即使用无限制 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="num">数量</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="num" id="num"
                                    value="{{Request::old('num')}}" required/>
                            <span class="input-group-text">张</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">有效期</label>
                        <div class="col-md-7 input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar"
                                            aria-hidden="true"></i></span>
                            </div>
                            <input type="text" class="form-control" name="available_start" id="available_start"
                                    value="{{Request::old('available_start')?Request::old('available_start'):date("Y-m-d")}}"
                                    required/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="available_end" id="available_end"
                                    value="{{Request::old('available_end')?Request::old('available_end'):date("Y-m-d",strtotime("+1 month"))}}"
                                    required/>
                        </div>
                    </div>
                    <div class="form-actions col-12 text-right">
                        <button type="submit" class="btn btn-success">提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"
            type="text/javascript"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/dropify.js" type="text/javascript"></script>

    <script type="text/javascript">
		$('.input-daterange>input').datepicker({
			format: "yyyy-mm-dd"
		});

		$("input[name='type']").change(function () {
			if ($(this).val() === '2') {
				$("#discount").attr("required", true);
				$("#amount").attr("required", false);
				$("#rule").attr("required", true);
				$(".discount").show();
				$(".usage").show();
				$(".amount").hide();
			} else if ($(this).val() === '3') {
				$("#discount").attr("required", false);
				$("#amount").attr("required", true);
				$("#rule").attr("required", false);
				$(".discount").hide();
				$(".usage").hide();
				$(".amount").show();
			} else {
				$("#discount").attr("required", false);
				$("#amount").attr("required", true);
				$("#rule").attr("required", true);
				$(".discount").hide();
				$(".usage").show();
				$(".amount").show();
			}
		});
    </script>
@endsection
