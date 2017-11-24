@extends('user.layouts')

@section('css')
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        .fancybox > img {
            width: 75px;
            height: 75px;
        }
    </style>
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    通过您的推广链接注册的账号可以获得 <code>{{$referral_traffic}}流量</code> 奖励。您可以获得他们每笔消费的<code>{{$referral_percent * 100}}%现金</code>返利。累计满 <code>{{$referral_money}}元</code>，就可以申请提现至微信或者支付宝。
                </div>
                <div class="portlet light form-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-link font-blue"></i>
                            <span class="caption-subject font-blue bold uppercase">我的推广链接</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="mt-clipboard-container">
                            <input type="text" id="mt-target-1" class="form-control" value="{{$link}}" />
                            <a href="javascript:;" class="btn blue mt-clipboard" data-clipboard-action="copy" data-clipboard-target="#mt-target-1">
                                <i class="icon-note"></i> 复制链接
                            </a>
                        </div>
                    </div>
                </div>
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-diamond font-dark"></i>
                            <span class="caption-subject bold uppercase"> 推广返利 </span>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn red" onclick="extractMoney()"><i class="fa fa-money"></i> 申请提现</button>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                <thead>
                                <tr>
                                    <th> ID </th>
                                    <th> 消费者 </th>
                                    <th> 消费金额 </th>
                                    <th> 返利金额 </th>
                                    <th> 状态 </th>
                                    <th> 返利时间 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($referralLogList->isEmpty())
                                    <tr>
                                        <td colspan="6">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($referralLogList as $key => $referralLog)
                                        <tr class="odd gradeX">
                                            <td> {{$key + 1}} </td>
                                            <td> {{$referralLog->user->username}} </td>
                                            <td> {{$referralLog->amount}} </td>
                                            <td> {{$referralLog->ref_amount}} </td>
                                            <td>
                                                @if ($referralLog->status == 1)
                                                    <span class="label label-sm label-danger">申请中</span>
                                                @elseif($referralLog->status == 2)
                                                    <span class="label label-sm label-default">已提现</span>
                                                @else
                                                    <span class="label label-sm label-info">未提现</span>
                                                @endif
                                            <td> {{$referralLog->created_at}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$referralLogList->total()}} 条记录，合计返利<code>{{$canAmount}}元</code></div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $referralLogList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/clipboardjs/clipboard.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-clipboard.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 申请提现
        function extractMoney() {
            $.post("{{url('user/extractMoney')}}", {_token:'{{csrf_token()}}'}, function (ret) {
                layer.msg(ret.message, {time:1000});
            });
        }
    </script>
@endsection
