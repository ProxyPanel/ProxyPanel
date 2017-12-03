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

    <style type="text/css">
        #lottery{width:574px;height:584px;margin:20px auto;background:url(/assets/images/bg.jpg) no-repeat;padding:50px 55px;}
        #lottery table td{width:142px;height:142px;text-align:center;vertical-align:middle;font-size:24px;color:#333;font-index:-999}
        #lottery table td a{width:284px;height:284px;line-height:150px;display:block;text-decoration:none;}
        #lottery table td.active{background-color:#ea0000;}

    </style>
@endsection
@section('title', '控制面板')
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
                <div class="alert alert-danger">
                    <strong>结算比例：</strong> 1表示用100M就结算100M，0.1表示用100M结算10M，5表示用100M结算500M。
                    <button class="btn btn-sm red" onclick="subscribe()"> 订阅节点 </button>
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
                                                <a class="btn btn-sm green btn-outline" href="javascript:show('{{$node->ssr_scheme . '<br><br>' . $node->ss_scheme}}');"> <i class="fa fa-paper-plane"></i> </a>
                                                <a class="btn btn-sm green btn-outline" data-toggle="modal" href="#qrcode_{{$node->id}}"> <i class="fa fa-qrcode"></i> </a>
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
                <div class="portlet box red">
                    <div class="portlet-title">
                        <div class="caption">账号信息</div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="" title="折叠"> </a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <p class="text-muted"> 等级：{{$info['levelName']}} </p>
                        <p class="text-muted">
                            余额：{{$info['balance']}}
                            <span class="badge badge-danger">
                                <a href="javascript:;" data-toggle="modal" data-target="#charge_modal" style="color:#FFF;">充值</a>
                            </span>
                            &ensp;&ensp;积分：{{$info['score']}}
                            <span class="badge badge-danger">
                                <a href="javascript:;" data-toggle="modal" data-target="#excharge_modal" style="color:#FFF;">兑换</a>
                            </span>
                        </p>
                        <p class="text-muted"> 账号到期：{{date('Y-m-d 0:0:0') > $info['expire_time'] ? '已过期' : $info['expire_time']}} </p>
                        <p class="text-muted"> 最后使用：{{empty($info['t']) ? '从未使用' : date('Y-m-d H:i:s', $info['t'])}} </p>
                        <p class="text-muted"> 最后登录：{{empty($info['last_login']) ? '未登录' : date('Y-m-d H:i:s', $info['last_login'])}} </p>
                        <p class="text-muted">
                            已用流量：{{$info['usedTransfer']}} （{{$info['totalTransfer']}}）
                            <div class="progress progress-striped active" style="margin-bottom:0;" title="共有流量{{$info['totalTransfer']}}，已用{{$info['usedTransfer']}}">
                                <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{$info['usedPercent'] * 100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$info['usedPercent'] * 100}}%">
                                    <span class="sr-only"> {{$info['usedTransfer']}} / {{$info['totalTransfer']}} </span>
                                </div>
                            </div>
                        </p>
                    </div>
                </div>
                <div class="portlet box blue">
                    <div class="portlet-title">
                        <div class="caption">文章</div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="" title="折叠"> </a>
                        </div>
                    </div>
                    <div class="portlet-body">
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
                        <h4 class="modal-title"> 充值 </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <span>微信</span>
                                <br />
                                <img src="{{$wechat_qrcode}}" alt="" style="width:200px; height:200px;" />
                            </div>
                            <div class="col-md-6">
                                <span>支付宝</span>
                                <br />
                                <img src="{{$alipay_qrcode}}" alt="" style="width:200px; height:200px;" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-4">
                                <span>付款时请备注您的账号，以便及时到账</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="excharge_modal" class="modal fade" tabindex="-1" data-focus-on="input:first" data-keyboard="false">
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
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">取消</button>
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
                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SSR</span></div>
                                        <div id="qrcode_ssr_img_{{$node->id}}"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SS</span></div>
                                        <div id="qrcode_ss_img_{{$node->id}}"></div>
                                    </div>
                                @else
                                    <div class="col-md-12">
                                        <div style="font-size:16px;text-align:center;padding-bottom:10px;"><span>SSR</span></div>
                                        <div id="qrcode_ssr_img_{{$node->id}}"></div>
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
