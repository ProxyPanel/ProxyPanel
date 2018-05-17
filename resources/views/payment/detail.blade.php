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
                    请使用<strong style="color:red;">支付宝、QQ、微信</strong>扫描如下二维码
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
                                    <td align="left">{{$payment->amount}} 元</td>
                                </tr>
                                <tr>
                                    <td align="right">有效期：</td>
                                    <td align="left">{{$payment->order->goods->days}} 天</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        长按下图并点击弹出的“识别图中二维码”进行付款
                                        <br>
                                        请于30分钟内支付，到期未支付订单将自动关闭
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <img src="{{$payment->qr_local_url}}"/>
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
        // 每2秒查询一次订单状态
        $(document).ready(function(){
            setInterval("getStatus()", 2000);
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