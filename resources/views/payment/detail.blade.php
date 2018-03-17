@extends('user.layouts')

@section('css')

@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="alert alert-info" style="text-align: center;">
                    请使用<strong>支付宝</strong>或<strong>微信</strong>扫描如下二维码
                </div>
                <div class="row" style="text-align: center; font-size: 1.05em;">
                    <div class="col-md-12">
                        <div class="table-scrollable">
                            <table class="table table-hover table-light">
                                <tr>
                                    <td align="right" width="50%">服务名称：</td>
                                    <td align="left" width="50%">{{$payment->order->goods->name}}</td>
                                </tr>
                                <tr>
                                    <td align="right">应付金额：</td>
                                    <td align="left">{{$payment->amount / 100}} 元</td>
                                </tr>
                                <tr>
                                    <td align="right">有效期：</td>
                                    <td align="left">{{$payment->order->goods->days}} 天</td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <img src="{{$payment->qr_code}}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            setInterval("getStatus()", 3000);
        });

        // 检查支付单状态
        function getStatus () {
            var sn = '{{$payment->sn}}';

            $.get("{{url('payment/getStatus')}}", {sn:sn}, function (ret) {
                console.log(ret);
                if (ret.status == 'success') {
                    layer.msg(ret.message, {time:1500}, function() {
                        window.location.href = '{{url('user/orderList')}}';
                    });
                }
            });
        }
    </script>
@endsection