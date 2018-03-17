@extends('admin.layouts')

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
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 商品列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addGoods()"> 添加商品 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> # </th>
                                    <th> 名称 </th>
                                    <th> 类型 </th>
                                    <th> 图片 </th>
                                    <th> 内含流量 </th>
                                    <th> 售价 </th>
                                    <th> 所需积分 </th>
                                    <th> 状态 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($goodsList->isEmpty())
                                    <tr>
                                        <td colspan="9">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($goodsList as $goods)
                                        <tr class="odd gradeX">
                                            <td> {{$goods->id}} </td>
                                            <td> {{$goods->name}} </td>
                                            <td> {{$goods->type == 1 ? '流量包' : '套餐'}} </td>
                                            <td> @if($goods->logo) <a href="{{$goods->logo}}" class="fancybox"><img src="{{$goods->logo}}"/></a> @endif </td>
                                            <td> {{$goods->traffic}} </td>
                                            <td> {{$goods->price}} </td>
                                            <td> {{$goods->score}} </td>
                                            <td>
                                                @if($goods->status)
                                                    <span class="label label-success">上架</span>
                                                @else
                                                    <span class="label label-default">下架</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm blue btn-outline" onclick="editGoods('{{$goods->id}}')">编辑</button>
                                                <button type="button" class="btn btn-sm red btn-outline" onclick="delGoods('{{$goods->id}}')">删除</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$goodsList->total()}} 个商品</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $goodsList->links() }}
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
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        function addGoods() {
            window.location.href = '{{url('shop/addGoods')}}';
        }

        // 编辑商品
        function editGoods(id) {
            window.location.href = '{{url('shop/editGoods?id=')}}' + id;
        }

        // 删除商品
        function delGoods(id) {
            layer.confirm('确定删除该商品？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('shop/delGoods')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
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
