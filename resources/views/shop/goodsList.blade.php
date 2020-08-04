@extends('admin.layouts')

@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">商品列表</h3>
                <div class="panel-actions">
                    <button class="btn btn-primary" onclick="addGoods()"><i class="icon wb-plus"></i>添加商品</button>
                </div>
            </div>
            <div class="panel-body">
                <table class="text-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 类型</th>
                        <!-- <th> 图片 </th> -->
                        <th> 内含流量</th>
                        <th> 售价</th>
                        <th> 排序</th>
                        <th> 热销</th>
                        <th> 限购</th>
                        <th> 状态</th>
                        <th> 操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($goodsList->isEmpty())
                        <tr>
                            <td colspan="10">暂无数据</td>
                        </tr>
                    @else
                        @foreach($goodsList as $goods)
                            <tr>
                                <td> {{$goods->id}} </td>
                                <td> {{$goods->name}} </td>
                                <td>
                                    @if($goods->type == 1)
                                        流量包
                                    @elseif($goods->type == 2)
                                        套餐
                                    @else
                                        充值
                                    @endif
                                </td>
                            <!-- <td> @if($goods->logo) <a href="{{$goods->logo}}" class="fancybox"><img src="{{$goods->logo}}"/></a> @endif </td> -->
                                <td> {{$goods->type == 3 ? '' : $goods->traffic_label}} </td>
                                <td> {{$goods->price}}元</td>
                                <td> {{$goods->sort}} </td>
                                <td>
                                    @if($goods->is_hot)
                                        <span class="badge badge-lg badge-danger">是</span>
                                    @else
                                        <span class="badge badge-lg badge-default">否</span>
                                    @endif
                                </td>
                                <td>
                                    @if($goods->is_limit)
                                        <span class="badge badge-lg badge-danger">是</span>
                                    @else
                                        <span class="badge badge-lg badge-default">否</span>
                                    @endif
                                </td>
                                <td>
                                    @if($goods->status)
                                        <span class="badge badge-lg badge-success">上架</span>
                                    @else
                                        <span class="badge badge-lg badge-default">下架</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-primary" onclick="editGoods('{{$goods->id}}')"><i class="icon wb-edit"></i></button>
                                        <button class="btn btn-danger" onclick="delGoods('{{$goods->id}}')"><i class="icon wb-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4 col-sm-4">
                        共 <code>{{$goodsList->total()}}</code> 个商品
                    </div>
                    <div class="col-md-8 col-sm-8">
                        <div class="Page navigation float-right">
                            {{ $goodsList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script type="text/javascript">
        function addGoods() {
            window.location.href = '/shop/addGoods';
        }

        // 编辑商品
        function editGoods(id) {
            window.location.href = '/shop/editGoods?id=' + id;
        }

        // 删除商品
        function delGoods(id) {
            swal.fire({
                title: '警告',
                text: '确定删除该商品?',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonText: '确定',
            }).then((result) => {
                if (result.value) {
                    $.post("/shop/delGoods", {id: id, _token: '{{csrf_token()}}'}, function (ret) {
                        swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false,})
                            .then(() => {
                                window.location.reload();
                            })
                    });
                }
            });
        }

        // 查看商品图片
        $(document).ready(function () {
            $('.fancybox').fancybox({
                openEffect: 'elastic',
                closeEffect: 'elastic'
            })
        });
    </script>
@endsection
