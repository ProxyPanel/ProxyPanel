@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title"><i class="icon wb-shopping-cart" aria-hidden="true"></i>商品列表</h1>
                <div class="panel-actions">
                    <a href="/shop/addGoods" class="btn btn-primary"><i class="icon wb-plus"></i>添加商品</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="type" name="type" onChange="Search()">
                            <option value="" @if(Request::get('type') == '') selected hidden @endif>类型</option>
                            <option value="1" @if(Request::get('type') == '1') selected hidden @endif>流量包</option>
                            <option value="2" @if(Request::get('type') == '2') selected hidden @endif>套餐</option>
                            <option value="3" @if(Request::get('type') == '3') selected hidden @endif>余额充值</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="status" name="status" onChange="Search()">
                            <option value="" @if(Request::get('status') == '') selected hidden @endif>状态</option>
                            <option value="1" @if(Request::get('status') == '1') selected hidden @endif>上架</option>
                            <option value="0" @if(Request::get('status') == '0') selected hidden @endif>下架</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜索</button>
                        <a href="/shop/goodsList" class="btn btn-danger">重置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 类型</th>
                        <th> 图片</th>
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
                            <td colspan="11">暂无数据</td>
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
                                <td>
                                    @if($goods->logo)
                                        <a href="{{$goods->logo}}" target="_blank"><img src="{{$goods->logo}}" alt="logo" class="h-50"/></a>
                                    @endif
                                </td>
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
                                        <a href="/shop/editGoods/{{$goods->id}}" class="btn btn-primary"><i class="icon wb-edit"></i></a>
                                        <button class="btn btn-danger" onclick="delGoods({{$goods->id}})"><i class="icon wb-trash"></i></button>
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
                    <div class="col-sm-4">
                        共 <code>{{$goodsList->total()}}</code> 个商品
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$goodsList->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        // 搜索
        function Search() {
            const type = $("#type option:selected").val();
            const status = $("#status option:selected").val();
            window.location.href = '/shop/goodsList?type=' + type + '&status=' + status;
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
    </script>
@endsection
