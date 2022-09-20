@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet">
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
                        <div class="col-md-10">
                            <input type="text" class="form-control col-md-4" name="name" id="name" value="{{old('name')}}" required/>
                            <span class="text-help"> 会用于前端显示 </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sn">使用券码</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control col-md-4" name="sn" id="sn" value="{{old('sn')}}"/>
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
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="value">优惠额度</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <input type="number" class="form-control col-md-3" min="1" max="99" name="value" id="value" value="{{old('value')}}" required/>
                                <span class="input-group-text" id="amount">元</span>
                                <span class="input-group-text discount" style="display: none;">%</span>
                            </div>
                            <span class="text-help discount" style="display: none;"> 范围为 1% ~ 99% </span>
                        </div>
                    </div>
                    <div class="usage">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="priority"> 权 重 </label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="number" class="form-control col-md-2" min="0" max="255" name="priority" id="priority" value="{{old('priority')}}"/>
                                </div>
                                <span class="text-help"> 同【使用券码】下，符合条件的高权重码将会被优先使用。最高为 255 </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="usable_times">使用次数</label>
                            <div class="col-md-4 input-group">
                                <input type="number" class="form-control" min="1" name="usable_times" id="usable_times" value="{{old('usable_times', 1)}}"/>
                                <span class="input-group-text">次</span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="minimum">满减条件</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="number" class="form-control col-md-3" name="minimum" id="minimum" value="{{old('minimum')}}" step="0.01"/>
                                    <span class="input-group-text">元</span>
                                </div>
                                <span class="text-help"> 当支付金额超过N值时，才能使用本优惠劵；不设置/0，即为无限制 </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="used">个人限用</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="number" class="form-control col-md-3" name="used" id="used" value="{{old('used')}}" step="1"/>
                                    <span class="input-group-text">次</span>
                                </div>
                                <span class="text-help"> 符合条件的用户可以使用本券N次；不设置/0，即为无限制 </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="levels" class="col-md-2 col-form-label">等级限定</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="levels" name="levels[]"
                                        multiple>
                                    @foreach($levels as $key => $level)
                                        <option value="{{$key}}">{{$level}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> 用户等级在选定等级内，方可使用本券</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="groups" class="col-md-2 col-form-label">分组限定</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="groups" name="groups[]"
                                        multiple>
                                    @foreach($userGroups as $key => $group)
                                        <option value="{{$key}}">{{$group}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> 选定的用户分组，方可使用本券</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_whitelist">专属用户</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-6" data-plugin="tokenfield" id="users_whitelist" name="users_whitelist"
                                       value="{{old('users_whitelist')}}" placeholder="输入用户ID, 再回车"/>
                                <span class="text-help"> 涉及用户均可使用本券，留空为不使用此条件</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_blacklist">禁用用户</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-6" data-plugin="tokenfield" id="users_blacklist" name="users_blacklist"
                                       value="{{old('users_blacklist')}}" placeholder="输入用户ID, 再回车"/>
                                <span class="text-help"> 涉及用户均不可使用本券，空为不使用此条件</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_whitelist">许可商品</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-4" data-plugin="tokenfield" id="services_whitelist" name="services_whitelist"
                                       value="{{old('services_whitelist')}}" placeholder="输入商品ID, 再回车"/>
                                <span class="text-help"> 涉及商品方可使用本券，留空为不使用此条件</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_blacklist">禁用商品</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-4" data-plugin="tokenfield" id="services_blacklist" name="services_blacklist"
                                       value="{{old('services_blacklist')}}" placeholder="输入商品ID, 再回车"/>
                                <span class="text-help"> 涉及商品不可使用本券，留空为不使用此条件</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newbie" class="col-md-2 col-form-label">新人专属</label>
                            <div class="col-md-10">
                                <ul class="list-unstyled">
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="coupon" name="coupon" {{ old('coupon') ? 'checked' : '' }}/>
                                            <label for="coupon">首次用任意券</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="order" name="order" {{ old('order') ? 'checked' : '' }}/>
                                            <label for="order">首单</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item pb-0 pl-0">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="days">创号</label>
                                            </div>
                                            <input type="number" class="form-control col-md-3" name="days" id="days" value="{{old('days')}}"/>
                                            <div class="input-group-append">
                                                <span class="input-group-text">天</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <span class="text-help"> 本项各条件为 <strong>并且</strong> 关系，请自行搭配使用 </span>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="num">数量</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="num" id="num" value="{{old('num')}}" required/>
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
                            <input type="text" class="form-control" name="start_time" id="start_time"
                                   value="{{old('start_time') ?? date("Y-m-d")}}" required/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <label for="end_time"></label>
                            <input type="text" class="form-control" name="end_time" id="end_time"
                                   value="{{old('end_time') ?? date("Y-m-d",strtotime("+1 month"))}}" required/>
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
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
    <script>
        @if(old())
        $(document).ready(function() {
          $("input[name='type'][value='{{old('type')}}']").click();
          $('#levels').selectpicker('val', @json(old('levels')));
          $('#groups').selectpicker('val', @json(old('groups')));
        });
        @endif

        $('.input-daterange>input').datepicker({
          format: 'yyyy-mm-dd',
        });

        $('input[name=\'type\']').change(function() {
          if ($(this).val() === '2') {
            $('.discount').show();
            $('.usage').show();
            $('#amount').hide();
          } else if ($(this).val() === '3') {
            $('.discount').hide();
            $('.usage').hide();
            $('#amount').show();
          } else {
            $('.discount').hide();
            $('.usage').show();
            $('#amount').show();
          }
        });
    </script>
@endsection
