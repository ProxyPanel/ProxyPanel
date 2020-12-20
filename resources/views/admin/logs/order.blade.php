@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">订单列表</h2>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="text" class="form-control" name="email" id="email" value="{{Request::input('email')}}" placeholder="用户名"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="order_sn" id="order_sn" value="{{Request::input('order_sn')}}" placeholder="订单号"/>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <input type="text" class="form-control" name="start" id="start" placeholder="{{date("Y-m-d")}}"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="end" id="end" placeholder="{{date("Y-m-d",strtotime("+1 month"))}}"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" name="is_expire" id="is_expire" onChange="Search()">
                            <option value="" hidden>是否过期</option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" name="is_coupon" id="is_coupon" onChange="Search()">
                            <option value="" hidden>是否使用优惠券</option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" name="pay_way" id="pay_way" onChange="Search()">
                            <option value="" hidden>支付方式</option>
                            <option value="credit">余额</option>
                            <option value="youzan">有赞云</option>
                            <option value="f2fpay">当面付</option>
                            <option value="codepay">码支付</option>
                            <option value="payjs">PayJs</option>
                            <option value="bitpayx">麻瓜宝</option>
                            <option value="paypal">PayPal</option>
                            <option value="epay">易支付</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" name="status" id="status" onChange="Search()">
                            <option value="" hidden>订单状态</option>
                            <option value="-1">已关闭</option>
                            <option value="0">待支付</option>
                            <option value="1">已支付待确认</option>
                            <option value="2">已完成</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="sort" value="0" checked/>
                                <label for="type">降序</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="sort" value="1" @if(Request::input('sort') === '1') checked @endif/>
                                <label for="type">升序</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.order')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户名</th>
                        <th> 订单号</th>
                        <th> 商品</th>
                        <th> 过期时间</th>
                        <th> 优惠券</th>
                        <th> 原价</th>
                        <th> 实价</th>
                        <th> 支付方式</th>
                        <th> 订单状态</th>
                        <th> 创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orderList as $order)
                        <tr>
                            <td> {{$order->id}} </td>
                            <td>
                                @if(empty($order->user) )
                                    【账号不存在】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id'=>$order->user->id])}}" target="_blank">{{$order->user->email}} </a>
                                    @else
                                        {{$order->user->email}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$order->order_sn}}</td>
                            <td> {{empty($order->goods) ? ($order->goods_id === 0 ? '余额充值' : trans('home.invoice_table_goods_deleted')) : $order->goods->name}} </td>
                            <td> {{$order->is_expire ? '已过期' : $order->expired_at}} </td>
                            <td> {{$order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : ''}} </td>
                            <td> ￥{{$order->origin_amount}} </td>
                            <td> ￥{{$order->amount}} </td>
                            <td>
                                <span class="badge badge-lg badge-info"> {{$order->pay_way_label}} </span>
                            </td>
                            <td>
                                @if($order->status === -1)
                                    <span class="badge badge-lg badge-danger"> 已关闭 </span>
                                @elseif ($order->status === 0)
                                    <span class="badge badge-lg badge-default"> 待支付 </span>
                                @elseif ($order->status === 1)
                                    <span class="badge badge-lg badge-default"> 已支付待确认 </span>
                                @else
                                    <span class="badge badge-lg badge-success"> 已完成 </span>
                                @endif
                            </td>
                            <td> {{$order->created_at}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$orderList->total()}}</code> 个订单
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$orderList->links()}}
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
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
      $(document).ready(function() {
        $('#is_expire').val({{Request::input('is_expire')}});
        $('#is_coupon').val({{Request::input('is_coupon')}});
        $('#pay_way').val({{Request::input('pay_way')}});
        $('#status').val({{Request::input('status')}});
      });

      // 有效期
      $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd',
      });
      //回车检测
      $(document).on('keypress', 'input', function(e) {
        if (e.which === 13) {
          Search();
          return false;
        }
      });

      // 搜索
      function Search() {
        window.location.href = '{{route('admin.order')}}?email=' + $('#email').val() + '&order_sn=' + $('#order_sn').val() +
            '&is_expire=' + $('#is_expire').val() + '&is_coupon=' + $('#is_coupon').val() + '&pay_way=' +
            $('#pay_way').val() + '&status=' + $('#status').val() + '&sort=' +
            $('input:radio[name=\'sort\']:checked').val() + '&range_time=' + [$('#start').val(), $('#end').val()];
      }
    </script>
@endsection
