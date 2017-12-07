@extends('user.layouts')

@section('css')
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-4">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <span class="caption-subject bold uppercase"> 充值余额 </span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="form-group">
                                <label for="usage" class="col-md-3 control-label">充值方式</label>
                                <div class="col-md-8">
                                    <div class="mt-radio-inline">
                                        @if($dmf_wepay || $dmf_alipay || $dmf_qqpay)
                                            @if($dmf_wepay)
                                                <label class="mt-radio">
                                                    <input type="radio" class="pay_type" data-type="wepay" name="pay_type">微信
                                                    <span></span> </label>
                                            @endif
                                            @if($dmf_alipay)
                                                <label class="mt-radio">
                                                    <input type="radio" class="pay_type" data-type="alipay" name="pay_type">支付宝
                                                    <span></span> </label>
                                            @endif
                                            @if($dmf_qqpay)
                                                <label class="mt-radio">
                                                    <input type="radio" class="pay_type" data-type="qqpay" name="pay_type">QQ支付
                                                    <span></span> </label>
                                            @endif
                                        @else
                                            <label class="mt-radio">
                                                <input type="radio" class="pay_type" data-type="" name="pay_type" disabled>系统未启用在线支付
                                                <span></span> </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="balance" class="col-md-3 control-label">充值金额</label>
                                <div class="col-md-8">
                                    @if($dmf_wepay || $dmf_alipay || $dmf_qqpay)
                                        <input type="text" class="form-control" name="money" value="" id="money" placeholder="88.88">
                                    @else
                                        <input type="text" class="form-control" name="money" value="" id="money" placeholder="88.88" disabled>
                                    @endif
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-4" style="padding-left: 30px;">
                                        @if($dmf_wepay || $dmf_alipay || $dmf_qqpay)
                                            <button id="submit" class="btn blue"> 支付</button>
                                        @else
                                            <button id="submit" class="btn blue" disabled> 支付</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold uppercase">充值记录</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-scrollable table-scrollable-borderless">
                                <table class="table table-hover table-light">
                                    <thead>
                                    <tr>
                                        <th> ID</th>
                                        <th> 充值金额</th>
                                        <th> 充值时间</th>
                                        <th> 状态</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if($paymentList->isEmpty())
                                        <tr>
                                            <td colspan="4">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($paymentList as $key => $payment)
                                            <tr class="odd gradeX">
                                                <td> {{$key + 1}} </td>
                                                <td> {{$payment->money}} </td>
                                                <td> {{$payment->created_at}} </td>
                                                <td> {{$payment->status > 0 ? '充值成功' : '充值失败'}} </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="charge_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"> 支付 </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 col-md-offset-3" id="qrcode">

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-md-offset-4 text-center">
                                <h3>请扫码支付</h3>
                            </div>
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
    <script src="/assets/global/plugins/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var pay_type = '';
        var pid = 0;

        $("#submit").click(function () {
            var pay_type = $("input:radio[name='pay_type']:checked").data('type');
            if (pay_type == '' || pay_type == undefined) {
                layer.msg('请选择支付类型');
                return ;
            }

            var money = $("#money").val();
            if (money == '' || money <= 0 || money == undefined) {
                layer.msg('充值金额不正确');
                return ;
            }

            $.ajax({
                'url': "{{url("user/payment")}}",
                'data': {
                    '_token': "{{csrf_token()}}",
                    'type': pay_type,
                    'price': money,
                },
                'type': "POST",
                'dataType': "json",
                success: function (ret) {
                    $("#submit").button("reset");
                    console.log(ret);
                    if (ret.errcode == 0) {
                        pid = ret.pid;
                        if (type != "alipay") {
                            $("#charge_modal").modal();
                            $("#qrcode").qrcode(ret.code);
                            setTimeout(f, 1000);
                        } else {
                            $("#charge_modal").modal();
                            $(".modal-body").html("跳转中...");
                            $("body").append(ret.code);
                        }
                    } else {
                        layer.msg(ret.errmsg);
                    }
                },
                error: function (ret) {
                    console.log(ret);
                    $("#submit").button("reset");
                }
            });
        });

        function f() {
            $.ajax({
                type: "POST",
                url: "/payment/query",
                dataType: "json",
                data: {
                    _token: "{{csrf_token()}}",
                    pid: pid
                },
                success: function (ret) {
                    if (ret.status) {
                        clearTimeout(tid);
                        $("#result").modal();
                        $(".modal-body").html("充值成功!");

                        window.setTimeout("location.href=window.location.href", 1000);
                    }
                }
            });

            tid = setTimeout(f, 1000);
        }
    </script>
@endsection
