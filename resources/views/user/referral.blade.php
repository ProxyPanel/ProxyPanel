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
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user/referral')}}">推广返利</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    通过您的推广链接注册的账号可以获得 <code>{{$referral_traffic}}MiB流量</code> 奖励。您可以获得他们每笔消费的<code>{{$referral_percent * 100}}%现金返利</code>。累计满 <code>{{$referral_money}}元</code>，就可以申请提现至微信或者支付宝。
                </div>
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-diamond font-dark"></i>
                            <span class="caption-subject bold uppercase"> 推广返利 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group btn-group-devided" data-toggle="buttons" onclick="extractMoney()">
                                <a href="javascript:;" class="btn btn-danger"><i class="fa fa-money"></i> 申请提现 </a>
                            </div>
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
                                    <th> 日期 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($referralLogList->isEmpty())
                                    <tr>
                                        <td colspan="5">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($referralLogList as $key => $referralLog)
                                        <tr class="odd gradeX">
                                            <td> {{$key + 1}} </td>
                                            <td> {{$referralLog->user_id}} </td>
                                            <td> {{$referralLog->amount}} </td>
                                            <td> {{$referralLog->ref_amount}} </td>
                                            <td> {{$referralLog->created_at}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$referralLogList->total()}} 条记录，合计可提现金额 <code>{{$totalAmount}}元</code></div>
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
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 申请提现
        function extractMoney() {
            bootbox.alert('开发中');
        }
    </script>
@endsection
