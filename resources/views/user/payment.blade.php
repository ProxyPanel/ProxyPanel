@extends('user.layouts')

@section('css')
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
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
                                @if($wepay_enabled)
                                    <label class="mt-radio">
                                        <input type="radio" class="type" data-type="wepay" name="type">微信
                                        <span></span>
                                    </label>
                                @endif
                                @if($alipay_enabled)
                                    <label class="mt-radio">
                                        <input type="radio"class="type" data-type="alipay" name="type">支付宝
                                        <span></span>
                                    </label>
                                @endif
                                @if($qqpay_enabled)
                                    <label class="mt-radio">
                                        <input type="radio" class="type" data-type="qqpay" name="type">QQ支付
                                        <span></span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="balance" class="col-md-3 control-label">充值金额</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="money" value="" id="money" placeholder="88.88" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class=" col-md-4">
                                <button id="submit" class="btn green"> 提 交 </button>
                            </div>
                        </div>
                    </div>
                    <h4>充值记录</h4>
                    <div class="table-scrollable">
                        <table class="table table-striped table-bordered table-hover table-checkable order-column">
                            <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 充值金额 </th>
                                    <th> 充值时间 </th>
                                </tr>
                            </thead>
                            <tbody>
                            @if($payment->isEmpty())
                                <tr>
                                    <td colspan="4">暂无数据</td>
                                </tr>
                            @else
                                @foreach($payment as $key => $p)
                                    <tr class="odd gradeX">
                                        <td> {{$key + 1}} </td>
                                        <td> {{$p->money}} </td>
                                        <td>{{$p->created_at}}</td>
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
@endsection
@section('script')
    <script src="/js/layer/layer.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var type;
        var pid = 0;
        $(".type").click(function(){
            type = $(this).data("type");
            console.log(type);
        });
        $("#submit").click(function(){
            $("#submit").button('loading')
            $.ajax({
                'url':"{{url("user/payment")}}",
                'data':{
                    '_token':"{{csrf_token()}}",
                    'type':type,
                    'price':$("#money").val(),
                },
                'type':"POST",
                'dataType':"json",
                success:function(data){
                    console.log(data);
                    if(data.errcode==0){
                        pid = data.pid;
                        if(type != "alipay"){
                            $("#charge_modal").modal();
                            $("#qrcode").qrcode(data.code);
                            setTimeout(f, 1000);
                        }else{
                            $("#charge_modal").modal();
                            $(".modal-body").html("跳转中...");
                            $("body").append(data.code);
                        }
                    }
                },
            });
        });
        function f(){
            $.ajax({
                type: "POST",
                url: "/payment/query",
                dataType: "json",
                data: {
                    _token:"{{csrf_token()}}",
                    pid:pid
                },
                success: function (data) {
                    if (data.status) {
                        clearTimeout(tid);
                        $("#result").modal();
                        $(".modal-body").html("充值成功!");
                        window.setTimeout("location.href=window.location.href",1000);
                    }
                }
            });
            tid = setTimeout(f, 1000);
        }

    </script>
@endsection
