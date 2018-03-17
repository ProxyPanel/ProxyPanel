@extends('user.layouts')

@section('css')
    <style type="text/css">
        .ticker {
            background-color: #fff;
            margin-bottom: 20px;
            border: 1px solid #e7ecf1!important;
            border-radius: 4px;
            -webkit-border-radius: 4px;
        }
        .ticker ul {
            padding: 0;
        }
        .ticker li {
            list-style: none;
            padding: 15px;
        }
    </style>
@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        @if (Session::has('successMsg'))
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                {{Session::get('successMsg')}}
            </div>
        @endif
        @if($notice)
            <div class="alert alert-success">
                <i class="fa fa-bell-o"></i>
                <button class="close" data-close="alert"></button>
                <a href="{{url('user/article?id=') . $notice->id}}" class="alert-link" target="_blank"> {{$notice->title}} </a>
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <div class="well">
                    {{trans('home.ratio_tips')}}
                    <button class="btn btn-sm blue" onclick="subscribe()"> {{trans('home.subscribe_button')}} </button>
                </div>
                <div class="row widget-row">
                    @if(!$nodeList->isEmpty())
                        @foreach($nodeList as $node)
                            <div class="col-md-4">
                                <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 ">
                                    <h4 class="widget-thumb-heading">{{$node->name}}</h4>
                                    <div class="widget-thumb-wrap">
                                        <div style="float:left;display: inline-block;padding-right:15px;">
                                            @if($node->country_code)
                                                <img src="{{asset('assets/images/country/' . $node->country_code . '.png')}}"/>
                                            @else
                                                <img src="{{asset('/assets/images/country/un.png')}}"/>
                                            @endif
                                        </div>
                                        <div class="widget-thumb-body">
                                            <span class="widget-thumb-subtitle"><a data-toggle="modal" href="#txt_{{$node->id}}">{{$node->server}}</a></span>
                                            <span class="widget-thumb-body-stat">
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#link_{{$node->id}}"> <i class="fa fa-paper-plane"></i> </a>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#qrcode_{{$node->id}}"> <i class="fa fa-qrcode"></i> </a>
                                                <a class="btn btn-sm green btn-outline" href="javascript:show('结算比例：{{$node->traffic_rate}}');"> <i class="fa fa-exchange"></i> </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <ul class="list-group">
                    <li class="list-group-item">
                        {{trans('home.account_status')}}：{{$info['enable'] ? '正常' : '禁用'}}
                    </li>
                    <li class="list-group-item">
                        {{trans('home.account_balance')}}：￥{{$info['balance']}}
                        <span class="badge badge-danger">
                            <a href="javascript:;" data-toggle="modal" data-target="#charge_modal" style="color:#FFF;">充值</a>
                        </span>
                    </li>
                    @if($login_add_score)
                    <li class="list-group-item">
                        {{trans('home.account_score')}}：{{$info['score']}}
                        <span class="badge badge-info">
                            <a href="javascript:;" data-toggle="modal" data-target="#exchange_modal" style="color:#FFF;">兑换</a>
                        </span>
                    </li>
                    @endif
                    <li class="list-group-item">
                        {{trans('home.account_expire')}}：{{date('Y-m-d 0:0:0') > $info['expire_time'] ? '已过期' : $info['expire_time']}}
                    </li>
                    <li class="list-group-item">
                        {{trans('home.account_last_usage')}}：{{empty($info['t']) ? '从未使用' : date('Y-m-d H:i:s', $info['t'])}}
                    </li>
                    <li class="list-group-item">
                        {{trans('home.account_last_login')}}：{{empty($info['last_login']) ? '未登录' : date('Y-m-d H:i:s', $info['last_login'])}}
                    </li>
                    <li class="list-group-item">
                        {{trans('home.account_bandwidth_usage')}}：{{$info['usedTransfer']}}（{{$info['totalTransfer']}}）@if($info['traffic_reset_day']) &ensp;每月{{$info['traffic_reset_day']}}日自动重置流量 @endif
                        <div class="progress progress-striped active" style="margin-bottom:0;" title="{{trans('home.account_total_traffic')}} {{$info['totalTransfer']}}，{{trans('home.account_usage_traffic')}} {{$info['usedTransfer']}}">
                            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{$info['usedPercent'] * 100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$info['usedPercent'] * 100}}%">
                                <span class="sr-only"> {{$info['usedTransfer']}} / {{$info['totalTransfer']}} </span>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{trans('home.article_title')}}</h3>
                    </div>
                    <div class="panel-body">
                        @foreach($articleList as $k => $article)
                            <p class="text-muted">
                                [{{date('m/d', strtotime($article->created_at))}}] <a href="{{url('user/article?id=') . $article->id}}" target="_blank"> {{$article->title}} </a>
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div id="charge_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">余额充值</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display: none; text-align: center;" id="charge_msg"></div>
                        <form action="#" method="post" class="form-horizontal">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="charge_type" class="col-md-4 control-label">充值方式</label>
                                    <div class="col-md-6">
                                        <select class="form-control" name="charge_type" id="charge_type">
                                            <option value="1" selected>卡券</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="charge_coupon" class="col-md-4 control-label"> 券码 </label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="charge_coupon" id="charge_coupon" placeholder="请输入券码">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                        <button type="button" class="btn red btn-outline" onclick="return charge();">充值</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="exchange_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"> 兑换流量 </h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" id="msg">您有 {{$info['score']}} 积分，共计可兑换 {{$info['score']}}M 免费流量。</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                        <button type="button" class="btn red btn-outline" onclick="return exchange();">立即兑换</button>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($nodeList as $node)
            <div class="modal fade draggable-modal" id="txt_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">配置信息</h4>
                        </div>
                        <div class="modal-body">
                            <textarea class="form-control" rows="10" readonly="readonly">{{$node->txt}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade draggable-modal" id="link_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Scheme Links</h4>
                        </div>
                        <div class="modal-body">
                            <textarea class="form-control" rows="10" readonly="readonly">{{$node->ssr_scheme}}{{$node->ss_scheme ? "\n\n".$node->ss_scheme : ''}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="qrcode_{{$node->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog @if(!$node->compatible) modal-sm @endif">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">请使用客户端扫描二维码</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @if ($node->compatible)
                                    <div class="col-md-6">
                                        <div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="qrcode_ss_img_{{$node->id}}" style="text-align: center;"></div>
                                    </div>
                                @else
                                    <div class="col-md-12">
                                        <div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 充值
        function charge() {
            var _token = '{{csrf_token()}}';
            var charge_type = $("#charge_type").val();
            var charge_coupon = $("#charge_coupon").val();

            if (charge_type == '1' && (charge_coupon == '' || charge_coupon == undefined)) {
                $("#charge_msg").show().html("券码不能为空");
                $("#charge_coupon").focus();
                return false;
            }

            $.ajax({
                url:'{{url('user/charge')}}',
                type:"POST",
                data:{_token:_token, coupon_sn:charge_coupon},
                beforeSend:function(){
                    $("#charge_msg").show().html("充值中...");
                },
                success:function(ret){
                    if (ret.status == 'fail') {
                        $("#charge_msg").show().html(ret.message);
                        return false;
                    }

                    $("#charge_modal").modal("hide");
                    window.location.reload();
                },
                error:function(){
                    $("#charge_msg").show().html("请求错误，请重试");
                },
                complete:function(){}
            });
        }

        // 积分兑换流量
        function exchange() {
            $.ajax({
                type: "POST",
                url: "{{url('user/exchange')}}",
                async: false,
                data: {_token:'{{csrf_token()}}'},
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                }
            });

            return false;
        }
    </script>

    <script type="text/javascript">
        var UIModals = function () {
            var n = function () {
                @foreach($nodeList as $node)
                    $("#txt_{{$node->id}}").draggable({handle: ".modal-header"});
                    $("#qrcode_{{$node->id}}").draggable({handle: ".modal-header"});
                @endforeach
            };

            return {
                init: function () {
                    n()
                }
            }
        }();

        jQuery(document).ready(function () {
            UIModals.init()
        });

        // 循环输出节点scheme用于生成二维码
        @foreach ($nodeList as $node)
            $('#qrcode_ssr_img_{{$node->id}}').qrcode("{{$node->ssr_scheme}}");
            $('#qrcode_ss_img_{{$node->id}}').qrcode("{{$node->ss_scheme}}");
        @endforeach

        // 节点订阅
        function subscribe() {
            window.location.href = '{{url('/user/subscribe')}}';
        }

        // 显示加密、混淆、协议
        function show(txt) {
            layer.msg(txt);
        }
    </script>
@endsection
