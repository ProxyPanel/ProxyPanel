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
    <div class="page-content">
        <!-- BEGIN PAGE BASE CONTENT -->
        @if(!$articleList->isEmpty())
        <div class="row">
            <div class="col-md-12">
                <div class="ticker">
                    <ul>
                        @foreach($articleList as $k => $article)
                            <li>
                                <i class="fa fa-bell-o"></i>
                                <a href="{{url('user/article?id=') . $article->id}}" class="alert-link" target="_blank"> {{$article->title}} </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
        @if (Session::has('successMsg'))
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                {{Session::get('successMsg')}}
            </div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <!-- BEGIN PORTLET -->
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption caption-md">
                            <i class="icon-globe theme-font hide"></i>
                            <span class="caption-subject font-blue-madison bold uppercase"> 账号信息 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        等级：{{$user_level[$info['level']]}}
                                    </li>
                                    <li class="list-group-item">
                                        余额：{{$info['balance']}}
                                        <span class="badge badge-danger">
                                            <a href="javascript:;" data-toggle="modal" data-target="#charge_modal" style="color:#FFF;">充值</a>
                                        </span>
                                    </li>
                                    <li class="list-group-item">
                                        积分：{{$info['score']}}
                                        <span class="badge badge-info">
                                            <a href="javascript:;" data-toggle="modal" data-target="#excharge_modal" style="color:#FFF;">兑换流量</a>
                                        </span>
                                    </li>
                                    <li class="list-group-item">
                                        已用流量：{{$info['usedTransfer']}} （{{$info['totalTransfer']}}）
                                        <div class="progress progress-striped active" style="margin-bottom:0;" title="共有流量{{$info['totalTransfer']}}，已用{{$info['usedTransfer']}}">
                                            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{{$info['usedPercent'] * 100}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$info['usedPercent'] * 100}}%">
                                                <span class="sr-only"> {{$info['usedTransfer']}} / {{$info['totalTransfer']}} </span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        账号到期：{{date('Y-m-d 0:0:0') > $info['expire_time'] ? '已过期' : $info['expire_time']}}
                                    </li>
                                    <li class="list-group-item">
                                        最后登录：{{empty($info['last_login']) ? '未登录' : date('Y-m-d H:i:s', $info['last_login'])}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- BEGIN PORTLET -->
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption caption-md">
                            <i class="icon-globe theme-font hide"></i>
                            <span class="caption-subject font-blue-madison bold uppercase"> SS(R)信息 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        端口：{{$info['port']}}
                                    </li>
                                    <li class="list-group-item">
                                        加密方式：{{$info['method']}}
                                        <span class="badge badge-success"><a href="{{url('user/profile#tab_2')}}" style="color:#FFF;">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        连接密码：{{$info['passwd']}}
                                        <span class="badge badge-success"><a href="{{url('user/profile#tab_2')}}" style="color:#FFF;">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        协议：{{$info['protocol']}}
                                        <span class="badge badge-success"><a href="{{url('user/profile#tab_2')}}" style="color:#FFF;">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        混淆：{{$info['obfs']}}
                                        <span class="badge badge-success"><a href="{{url('user/profile#tab_2')}}" style="color:#FFF;">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        最后使用：{{empty($info['t']) ? '从未使用' : date('Y-m-d H:i:s', $info['t'])}}
                                    </li>
                                </ul>
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
                        <div class="alert alert-info" id="msg">您有 {{$info['score']}} 积分，共计可兑换 {{$info['score']}}M 流量。</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn dark btn-outline">取消</button>
                        <button type="button" class="btn red btn-outline" onclick="return exchange();">立即兑换</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/jquery-knob/js/jquery.knob.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-easyticker/test/jquery.easing.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-easyticker/jquery.easy-ticker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script>
        // 流量饼图
        $(function() {
            $(".knob").knob({
                'readOnly':true,
                'angleoffset':0,
                'width':150,
                'height':150
            });
        });

        // 公告
        $(function(){
            $('.ticker').easyTicker({
                direction: 'up',
                easing: 'easeInOutBack',
                speed: 'slow',
                interval: 2000,
                height: 'auto',
                visible: 1,
                mousePause: 1,
                controls: {
                    up: '.up',
                    down: '.down',
                    toggle: '.toggle',
                    stopText: 'Stop !!!'
                }
            }).data('easyTicker');
        });

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
@endsection
