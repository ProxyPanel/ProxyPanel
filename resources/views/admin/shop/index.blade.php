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
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="type" name="type" onChange="Search()">
                            <option value="" hidden>类型</option>
                            <option value="1">流量包</option>
                            <option value="2">套餐</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" id="status" name="status" onChange="Search()">
                            <option value="" hidden>状态</option>
                            <option value="1">上架</option>
                            <option value="0">下架</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.goods.index')}}" class="btn btn-danger">重 置</a>
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
                        <th> 限购数</th>
                        <th> 状态</th>
                        <th> 操作</th>
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
                            <td>
                                @if($goods->logo)
                                    <a href="{{asset($goods->logo)}}" target="_blank">
                                        <img src="{{asset($goods->logo)}}" class="h-50" alt="logo"/>
                                    </a>
                                @endif
                            </td>
                            <td> {{$goods->traffic_label}} </td>
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
                                {{$goods->limit_num}}
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
        $('#type').val({{Request::input('type')}});
        $('#status').val({{Request::input('status')}});
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.goods.index')}}?type=' + $('#type option:selected').val() + '&status=' +
            $('#status option:selected').val();
      }

      @can('admin.goods.destroy')
      // 删除商品
      function delGoods(url, name) {
        swal.fire({
          title: '警告',
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
