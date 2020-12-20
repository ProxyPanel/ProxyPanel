@extends('user.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600">
                    <i class="icon wb-payment"></i>{{sysConfig('website_name')}}{{trans('home.online_pay')}}
                </h1>
            </div>
            <div class="panel-body border-primary ml-auto mr-auto w-p75">
                <div class="alert alert-info text-center">
                    请使用<strong class="red-600">{{$pay_type}}</strong>扫描二维码进行支付
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-dividered">
                            <li class="list-group-item">服务名称：{{$name}}</li>
                            <li class="list-group-item">支付金额：{{$payment->amount}}元</li>
                            @if($days != 0)
                                <li class="list-group-item">有效期：{{$days}} 天</li>
                            @endif
                            <li class="list-group-item"> 请在<code>15分钟</code>内完成支付，否者订单将会自动关闭</li>
                        </ul>
                    </div>
                    <div class="col-auto mx-auto">
                        @if($payment->qr_code && $payment->url)
                            <div id="qrcode" class=" w-p100 h-p100"></div>
                        @else
                            <img class="h-250 w-250" src="{{$payment->qr_code}}" alt="支付二维码">
                        @endif
                    </div>
                </div>
                <div class="alert alert-danger text-center mt-10">
                    <strong>手机用户</strong>：长按二维码 -> 保存图片 ->打开支付软件 -> 扫一扫 -> 选择相册 进行付款
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    @if($payment->qr_code && $payment->url)
        <script src="/assets/custom/easy.qrcode.min.js"></script>
        <script>
          // Options
          var options = {
            text: @json($payment->url),
            backgroundImage: '{{asset($pay_type_icon)}}',
            autoColor: true,
          };

          // Create QRCode Object
          new QRCode(document.getElementById('qrcode'), options);
        </script>
    @endif

    <script>
      // 检查支付单状态
      const r = window.setInterval(function() {
        $.ajax({
          method: 'GET',
          url: '{{route('orderStatus')}}',
          data: {trade_no: '{{$payment->trade_no}}'},
          dataType: 'json',
          success: function(ret) {
            window.clearInterval();
            if (ret.status === 'success') {
              swal.fire({title: ret.message, icon: 'success', timer: 1500, showConfirmButton: false}).then(() => {
                window.location.href = '{{route('invoice')}}';
              });
            } else if (ret.status === 'error') {
              swal.fire({title: ret.message, icon: 'error', timer: 1500, showConfirmButton: false}).then(() => {
                window.location.href = '{{route('invoice')}}';
              });
            }
          },
        });
      }, 3000);
    </script>
@endsection
