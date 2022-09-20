@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">卡券列表</h1>
                @canany(['admin.coupon.export', 'admin.coupon.create'])
                    <div class="panel-actions btn-group">
                        @can('admin.coupon.export')
                            <button class="btn btn-info" onclick="exportCoupon()"><i class="icon wb-code"></i>批量导出</button>
                        @endcan
                        @can('admin.coupon.create')
                            <a href="{{route('admin.coupon.create')}}" class="btn btn-primary"><i class="icon wb-plus"></i>生成</a>
                        @endcan
                    </div>
                @endcanany
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-3 col-sm-4">
                        <input type="text" class="form-control" name="sn" value="{{Request::query('sn')}}" placeholder="券码" autocomplete="off"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-4">
                        <select class="form-control" name="type" id="type">
                            <option value="" hidden>类型</option>
                            <option value="1">现金券</option>
                            <option value="2">折扣券</option>
                            <option value="3">充值券</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3 col-sm-4">
                        <select class="form-control" name="status" id="status">
                            <option value="" hidden>状态</option>
                            <option value="0">生效中</option>
                            <option value="1">已使用</option>
                            <option value="2">已失效</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3 col-sm-4 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.coupon.index')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 名称</th>
                        <th> 券码</th>
                        <th> 图片</th>
                        <th> 类型</th>
                        <th> 权重</th>
                        <th> 使用次数</th>
                        <th> 优惠</th>
                        <th> 有效期</th>
                        <th> {{trans('common.status')}}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($couponList as $coupon)
                        <tr>
                            <td> {{$coupon->id}} </td>
                            <td> {{$coupon->name}} </td>
                            <td> {{$coupon->sn}} </td>
                            <td> @if($coupon->logo) <img src="{{asset($coupon->logo)}}" class="h-50" alt="优惠码logo"/> @endif </td>
                            <td>
                                {{ ['未知卡券','抵用券','折扣券','充值券'][$coupon->type] }}
                            </td>
                            <td> {{$coupon->priority}} </td>
                            <td> {{$coupon->type === 3 ? '一次性' : ($coupon->usable_times ?? '无限制')}} </td>
                            <td>
                                {{($coupon->type === 2 ?'减 ':'抵 ').$coupon->value.($coupon->type === 2 ?' %':' 元')}}
                            </td>
                            <td> {{$coupon->start_time}} ~ {{$coupon->end_time}} </td>
                            <td>
                                <span class="badge badge-lg @if($coupon->status) badge-default @else badge-success @endif">  {{['生效中','已使用','已失效'][$coupon->status]}} </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('admin.coupon.show')
                                        <a class="btn btn-info" href="{{route('admin.coupon.show', $coupon)}}" target="_blank">
                                            <i class="icon wb-eye"></i>
                                        </a>
                                    @endcan
                                    @if($coupon->status !== 1)
                                        @can('admin.coupon.destroy')
                                            <button class="btn btn-danger" onclick="delCoupon('{{$coupon->id}}','{{$coupon->name}}')">
                                                <i class="icon wb-close"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$couponList->total()}}</code> 张优惠券
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$couponList->links()}}
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

      @can('admin.coupon.export')
      // 批量导出卡券
      function exportCoupon() {
        swal.fire({
          title: '卡券导出',
          text: '确定导出所有卡券吗？',
          icon: 'question',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            window.location.href = '{{route('admin.coupon.export')}}';
          }
        });
      }
      @endcan

      @can('admin.coupon.destroy')
      // 删除卡券
      function delCoupon(id, name) {
        swal.fire({
          title: '确定删除卡券 【' + name + '】 吗？',
          icon: 'question',
          allowEnterKey: false,
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'DELETE',
              url: '{{route('admin.coupon.destroy', '')}}/' + id,
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
