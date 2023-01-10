@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title"><i class="icon wb-shopping-cart" aria-hidden="true"></i>商品列表</h1>
                @can('admin.goods.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.goods.create')}}" class="btn btn-primary"><i class="icon wb-plus"></i>添加商品</a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="type" name="type">
                            <option value="" hidden>类型</option>
                            <option value="1">流量包</option>
                            <option value="2">套餐</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="status" name="status">
                            <option value="" hidden>状态</option>
                            <option value="1">上架</option>
                            <option value="0">下架</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.goods.index')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
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
                        <th> 使用 / 销售</th>
                        <th> 热销</th>
                        <th> 限购数</th>
                        <th> {{trans('common.status')}}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($goodsList as $goods)
                        <tr>
                            <td> {{$goods->id}} </td>
                            <td> {{$goods->name}} </td>
                            <td>
                                @if($goods->type === 1)
                                    流量包
                                @elseif($goods->type === 2)
                                    套餐
                                @else
                                    充值
                                @endif
                            </td>
                            <td style="background-color: {{$goods->color ?? 'white'}}" @if($goods->color)class="text-white"@endif>
                                @if($goods->logo)
                                    <a href="{{asset($goods->logo)}}" target="_blank">
                                        <img src="{{asset($goods->logo)}}" class="h-50" alt="logo"/>
                                    </a>
                                @elseif($goods->color)
                                    无 LOGO
                                @endif
                            </td>
                            <td> {{$goods->traffic_label}} </td>
                            <td> {{$goods->price_tag}}</td>
                            <td> {{$goods->sort}} </td>
                            <td><code>{{$goods->use_count}} / {{$goods->total_count}}</code></td>
                            <td>
                                @if($goods->is_hot)
                                    🔥
                                @endif
                            </td>
                            <td>
                                {{$goods->limit_num ?: '无限制'}}
                            </td>
                            <td>
                                @if($goods->status)
                                    <span class="badge badge-lg badge-success">上架</span>
                                @else
                                    <span class="badge badge-lg badge-default">下架</span>
                                @endif
                            </td>
                            <td>
                                @canany(['admin.goods.edit', 'admin.goods.destroy'])
                                    <div class="btn-group">
                                        @can('admin.goods.edit')
                                            <a href="{{route('admin.goods.edit', $goods)}}" class="btn btn-primary">
                                                <i class="icon wb-edit"></i>
                                            </a>
                                        @endcan
                                        @can('admin.goods.destroy')
                                            <button onclick="delGoods('{{route('admin.goods.destroy', $goods)}}','{{$goods->name}}')" class="btn btn-danger">
                                                <i class="icon wb-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                @endcanany
                            </td>
                        </tr>
                    @endforeach
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
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#type').val({{Request::query('type')}});
            $('#status').val({{Request::query('status')}});

            $('select').on('change', function() { this.form.submit(); });
        });

        @can('admin.goods.destroy')
        // 删除商品
        function delGoods(url, name) {
            swal.fire({
                title: '{{trans('common.warning')}}',
                text: '确定删除商品 【' + name + '】 ?',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonText: '确定',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: {_token: '{{csrf_token()}}'},
                        dataType: 'json',
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                            } else {
                                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                            }
                        },
                    });
                }
            });
        }
        @endcan
    </script>
@endsection
