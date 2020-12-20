@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">生成卡券</h1>
                <div class="panel-actions">
                    <a href="{{route('admin.coupon.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="{{route('admin.coupon.store')}}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">@csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="name">卡券名称</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="name" id="name" value="{{Request::old('name')}}" required/>
                            <span class="text-help"> 会用于前端显示 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sn">使用券码</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="sn" id="sn" value="{{Request::old('sn')}}"/>
                            <span class="text-help"> 提供给用户使用卡券的卡券，留空则默认为8位随机码 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="logo">卡券图片</label>
                        <div class="col-md-6">
                            <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="{{asset('/assets/images/default.png')}}"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">类型</label>
                        <div class="col-md-10 align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="voucher" value="1" checked/>
                                <label for="voucher">抵用券</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="discount" value="2"/>
                                <label for="discount">折扣券</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="charge" value="3"/>
                                <label for="charge">充值券</label>
                            </div>
                            <span class="text-help"> 抵用：抵扣商品金额，折扣：商品百分比打折，充值：充值用户账号余额 </span>
                        </div>
                    </div>
                    <div class="form-group row usage">
                        <label class="col-md-2 col-form-label" for="usable_times">使用次数</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="usable_times" id="usable_times" value="{{Request::old('usable_times')}}"/>
                            <span class="input-group-text">次</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="value">优惠额度</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="value" id="value" value="{{Request::old('value')}}" required/>
                            <span class="input-group-text amount">元</span>
                            <span class="input-group-text discount" style="display: none;">%</span>
                            <span class="text-help discount" style="display: none;"> 范围为 1~99折，即 1% ~ 99% </span>
                        </div>
                    </div>
                    <div class="form-group row usage">
                        <label class="col-md-2 col-form-label" for="rule">条件</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="rule" id="rule" value="{{Request::old('rule')}}" step="0.01" required/>
                            <span class="input-group-text">元</span>
                            <span class="text-help"> 当套餐超过N值时，才能使用本优惠劵；0即使用无限制 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="num">数量</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="num" id="num" value="{{Request::old('num')}}" required/>
                            <span class="input-group-text">张</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">有效期</label>
                        <div class="col-md-7 input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <label for="start_time"></label>
                            <input type="text" class="form-control" name="start_time" id="start_time" value="{{Request::old('start_time') ?? date("Y-m-d")}}" required/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <label for="end_time"></label>
                            <input type="text" class="form-control" name="end_time" id="end_time" value="{{Request::old('end_time') ?? date("Y-m-d",strtotime("+1 month"))}}"
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
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script>
      $('.input-daterange>input').datepicker({
        format: 'yyyy-mm-dd',
      });

      $('input[name=\'type\']').change(function() {
        if ($(this).val() === '2') {
          $('#rule').attr('required', true);
          $('.discount').show();
          $('.usage').show();
          $('.amount').hide();
        } else if ($(this).val() === '3') {
          $('#rule').attr('required', false);
          $('.discount').hide();
          $('.usage').hide();
          $('.amount').show();
        } else {
          $('#rule').attr('required', true);
          $('.discount').hide();
          $('.usage').show();
          $('.amount').show();
        }
      });
    </script>
@endsection
