@extends('user.layouts')

@section('css')
    <link href="/assets/pages/css/pricing.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        .fancybox > img {
            width: 75px;
            height: 75px;
        }
    </style>
@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light table-checkable order-column">
                                <thead>
                                <tr>
                                    <th style="width:35%;"> {{trans('home.service_name')}} </th>
                                    <th style="text-align: center;"> {{trans('home.service_price')}} </th>
                                    <th> </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($goodsList->isEmpty())
                                    <tr>
                                        <td colspan="3">{{trans('home.services_none')}}</td>
                                    </tr>
                                @else
                                    @foreach($goodsList as $key => $goods)
                                        <tr class="odd gradeX">
                                            <td>
                                                <!--@if($goods->logo) <a href="{{$goods->logo}}" class="fancybox"><img src="{{$goods->logo}}"/></a> @endif -->
                                                <strong><span style="color:#dc9700">{{$goods->name}}</span></strong>
                                                <br>
                                                {{trans('home.service_traffic')}}：{{$goods->traffic}}
                                                <br>
                                                {{trans('home.service_days')}}：{{$goods->days}} {{trans('home.day')}}
                                            </td>
                                            <td style="text-align: center;"> ￥{{$goods->price}} </td>
                                            <td style="text-align: center;">
                                                <a href="javascript:buy('{{$goods->id}}');" class="btn btn-sm blue"> {{trans('home.service_buy_button')}} </a>
                                                <!--<button type="button" class="btn btn-sm blue btn-outline" onclick="exchange('{{$goods->id}}')">兑换</button>-->
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$goodsList->total()}} 个服务</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $goodsList->links() }}
                                </div>
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
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.js" type="text/javascript"></script>

    <script type="text/javascript">
        function buy(goods_id) {
            window.location.href = '{{url('user/addOrder?goods_id=')}}' + goods_id;
        }

        // 编辑商品
        function exchange(id) {
            //
        }

        // 查看商品图片
        $(document).ready(function () {
            $('.fancybox').fancybox({
                openEffect: 'elastic',
                closeEffect: 'elastic'
            })
        })
    </script>
@endsection
