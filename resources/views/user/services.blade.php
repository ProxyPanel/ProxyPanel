@extends('user.layouts')
@section('css')
    <link rel="stylesheet" href="assets/global/vendor/ionrangeslider/ionrangeslider.min.css">
@endsection
@section('content')
    <div class="page-content">
        <div class="row">
            <div class="col-xxl-2 col-lg-3">
                <div class="card card-shadow">
                    <div class="card-block p-20">
                        <button type="button" class="btn btn-floating btn-sm btn-pure">
                            <i class="icon wb-payment green-500"></i>
                        </button>
                        <span class="font-weight-400">{{trans('home.account_credit')}}</span>
                        <div class="content-text text-center mb-0">
                            <span class="font-size-40 font-weight-100">{{Auth::getUser()->credit}}</span>
                            <br/>
                            <button class="btn btn-danger float-right mr-15" data-toggle="modal" data-target="#charge_modal">{{trans('home.recharge')}}</button>
                        </div>
                    </div>
                </div>
                @if($renewTraffic)
                    <div class="card card-shadow">
                        <div class="card-block p-20">
                            <button type="button" class="btn btn-floating btn-sm btn-pure">
                                <i class="icon wb-payment green-500"></i>
                            </button>
                            <span class="font-weight-400">流量重置</span>
                            <div class="content-text text-center mb-0">
                                <span class="font-size-20 font-weight-100">需要 <code>{{$renewTraffic}}</code> 元</span>
                                <br/>
                                <button class="btn btn-danger mt-10" onclick="resetTraffic()">重置</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-xxl-10 col-lg-9">
                <div class="panel">
                    <div class="panel-heading p-20">
                        <h1 class="panel-title cyan-700">
                            <i class="icon wb-shopping-cart"></i>{{trans('home.services')}}
                        </h1>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            @foreach($goodsList as $goods)
                                <div class="col-md-6 col-xl-4 col-xxl-3 pb-30">
                                    <div class="pricing-list text-left">
                                        <div class="pricing-header text-white" style="background-color: {{$goods->color}}">
                                            <div class="pricing-title font-size-20">{{$goods->name}}</div>
                                            @if($goods->limit_num)
                                                <div class="ribbon ribbon-vertical ribbon-bookmark ribbon-reverse ribbon-primary mr-10">
                                                    <span class="ribbon-inner h-auto">限<br>购</span>
                                                </div>
                                            @elseif($goods->is_hot)
                                                <div class="ribbon ribbon-vertical ribbon-bookmark ribbon-reverse ribbon-danger mr-10">
                                                    <span class="ribbon-inner h-auto">热<br>销</span>
                                                </div>
                                            @endif
                                            <div class="pricing-price text-white @if($goods->type === 1) text-center @endif">
                                                <span class="pricing-currency">¥</span>
                                                <span class="pricing-amount">{{$goods->price}}</span>
                                                @if($goods->type === 2)
                                                    <span class="pricing-period">/ {{$goods->days}}{{trans('home.day')}}</span>
                                                @endif
                                            </div>
                                            @if($goods->info)
                                                <p class="px-30 pb-25 text-center">{{$goods->description}}</p>
                                            @endif
                                        </div>
                                        <ul class="pricing-features">
                                            <li>
                                                <strong>{{$goods->traffic_label}}</strong>{{trans('home.bandwidth')}}
                                                {!!$goods->type === 1? ' <code>'.$dataPlusDays.'</code> '.trans('home.day'):'/'.trans('home.month')!!}
                                            </li>
                                            <li>
                                                <strong>{{trans('home.service_unlimited')}}</strong> {{trans('home.service_device')}}
                                            </li>
                                            {!!$goods->info!!}
                                        </ul>
                                        <div class="pricing-footer text-center bg-blue-grey-100">
                                            <a href="{{route('buy', $goods)}}" class="btn btn-lg btn-primary"> {{trans('home.service_buy_button')}}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="charge_modal" class="modal fade" aria-labelledby="charge_modal" role="dialog" tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-simple modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{trans('home.recharge_credit')}}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="charge_msg" style="display: none;"></div>
                    <form action="#" method="post">
                        @if(sysConfig('is_onlinePay') || sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
                            <div class="mb-15 w-p50">
                                <select class="form-control" name="charge_type" id="charge_type">
                                    @if(sysConfig('is_onlinePay'))
                                        <option value="1">{{trans('home.online_pay')}}</option>
                                    @endif
                                    @if(sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
                                        <option value="2">二维码</option>
                                    @endif
                                    <option value="3">{{trans('home.coupon_code')}}</option>
                                </select>
                            </div>
                        @endif
                        @if(sysConfig('is_onlinePay'))
                            <div class="form-group row charge_credit">
                                <label for="amount" class="offset-md-1 col-md-2 col-form-label">充值金额</label>
                                <div class="col-md-8">
                                    <input type="text" name="amount" id="amount" data-plugin="ionRangeSlider" data-min=1 data-max=300 data-from=40 data-prefix="￥"/>
                                </div>
                            </div>
                        @endif
                        @if(sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
                            <div class="text-center" id="charge_qrcode">
                                <div class="row">
                                    <p class="col-md-12 mb-10">付款时，请
                                        <mark>备注邮箱账号</mark>
                                        ，充值会在<code>24</code>小时内受理!
                                    </p>
                                    @if(sysConfig('wechat_qrcode'))
                                        <div class="col-md-6">
                                            <img class="w-p75 mb-10" src="{{sysConfig('wechat_qrcode')}}" alt=""/>
                                            <p>微 信 | WeChat</p>
                                        </div>
                                    @endif
                                    @if(sysConfig('alipay_qrcode'))
                                        <div class="col-md-6">
                                            <img class="w-p75 mb-10" src="{{sysConfig('alipay_qrcode')}}" alt=""/>
                                            <p>支 付 宝 | AliPay</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="form-group row" id="charge_coupon_code">
                            <label for="charge_coupon"
                                   class="offset-md-2 col-md-2 col-form-label"> {{trans('home.coupon_code')}} </label>
                            <div class="col-md-6">
                                <input type="text" class="form-control round" name="charge_coupon" id="charge_coupon" placeholder="{{trans('home.please_input_coupon')}}">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="charge_credit">
                        @include('user.components.purchase')
                    </div>
                    <button type="button" class="btn btn-primary" id="change_btn" onclick="pay()">{{trans('home.recharge')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="assets/global/vendor/ionrangeslider/ion.rangeSlider.min.js"></script>
    <script src="assets/global/js/Plugin/ionrangeslider.js"></script>
    <script>
      function itemControl(value) {
        if (value === 1) {
          $('.charge_credit').show();
          $('#change_btn').hide();
          $('#charge_qrcode').hide();
          $('#charge_coupon_code').hide();
        } else if (value === 2) {
          $('.charge_credit').hide();
          $('#change_btn').hide();
          $('#charge_qrcode').show();
          $('#charge_coupon_code').hide();
        } else {
          $('.charge_credit').hide();
          $('#charge_qrcode').hide();
          $('#charge_coupon_code').show();
          $('#change_btn').show();
        }
      }

      $(document).ready(function() {
        let which_selected = 3;
          @if(sysConfig('is_onlinePay'))
              which_selected = 1;
          @elseif(sysConfig('alipay_qrcode') || sysConfig('wechat_qrcode'))
              which_selected = 2;
          @endif

          itemControl(which_selected);
        $('charge_type').val(which_selected);
      });

      // 切换充值方式
      $('#charge_type').change(function() {
        itemControl(parseInt($(this).val()));
      });

      // 重置流量
      function resetTraffic() {
        swal.fire({
          title: '重置流量',
          text: '本次重置流量将扣除余额 {{$renewTraffic}} 元？',
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('home.ticket_close')}}',
          confirmButtonText: '{{trans('home.ticket_confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.post('{{route('resetTraffic')}}', {_token: '{{csrf_token()}}'}, function(ret) {
              if (ret.status === 'success') {
                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
              } else {
                swal.fire({
                  title: ret.message,
                  text: ret.data,
                  icon: 'error',
                }).then(() => window.location.reload());
              }
            });
          }
        });
      }

      // 充值
      function pay(method, pay_type) {
        const paymentType = parseInt($('#charge_type').val() ?? 3);
        const charge_coupon = $('#charge_coupon').val().trim();
        const amount = parseInt($('#amount').val());
        if (paymentType === 1) {
          if (amount <= 0) {
            swal.fire({title: '错误', text: '充值余额不合规', icon: 'warning', timer: 1000, showConfirmButton: false});
            return false;
          }

          $.ajax({
            method: 'POST',
            url: '{{route('purchase')}}',
            data: {_token: '{{csrf_token()}}', amount: amount, method: method, pay_type: pay_type},
            dataType: 'json',
            beforeSend: function() {
              $('#charge_msg').show().html('创建支付单中...');
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                return false;
              } else {
                $('#charge_msg').show().html(ret.message);
                if (ret.data) {
                  window.location.href = '{{route('orderDetail' , '')}}/' + ret.data;
                } else if (ret.url) {
                  window.location.href = ret.url;
                }
              }
            },
            error: function() {
              $('#charge_msg').show().html("{{trans('home.error_response')}}");
            },
          });
        } else if (paymentType === 3) {
          if (charge_coupon === '') {
            $('#charge_msg').show().html("{{trans('home.coupon_not_empty')}}");
            $('#charge_coupon').focus();
            return false;
          }

          $.ajax({
            method: 'POST',
            url: '{{route('recharge')}}',
            data: {_token: '{{csrf_token()}}', coupon_sn: charge_coupon},
            beforeSend: function() {
              $('#charge_msg').show().html("{{trans('home.recharging')}}");
            },
            success: function(ret) {
              if (ret.status === 'fail') {
                $('#charge_msg').show().html(ret.message);
                return false;
              }

              $('#charge_modal').modal('hide');
              window.location.reload();
            },
            error: function() {
              $('#charge_msg').show().html("{{trans('home.error_response')}}");
            },
          });
        }
      }
    </script>
@endsection
