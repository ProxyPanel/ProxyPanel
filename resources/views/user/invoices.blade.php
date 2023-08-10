@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading p-20">
                <h1 class="panel-title cyan-600"><i class="icon wb-bookmark"></i>{{trans('user.menu.invoices')}}</h1>
                @if($prepaidPlan)
                    <div class="panel-actions">
                        <button onclick="closePlan()" class="btn btn-primary"> {{trans('common.active_item', ['attribute' => trans('common.order.status.prepaid')])}}</button>
                    </div>
                @endif
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{trans('model.order.id')}} </th>
                        <th> {{trans('user.shop.service')}} </th>
                        <th> {{trans('user.payment_method')}} </th>
                        <th> {{trans('user.invoice.amount')}} </th>
                        <th> {{trans('user.bought_at')}} </th>
                        <th> {{trans('common.expired_at')}} </th>
                        <th> {{trans('common.status.attribute')}} </th>
                        <th> {{trans('common.action')}} </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orderList as $order)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td><a href="/invoice/{{$order->sn}}" target="_blank">{{$order->sn}}</a></td>
                            <td>{{$order->goods->name ?? trans('user.recharge_credit')}}</td>
                            <td>{{$order->pay_way === 1 ? trans('user.shop.pay_credit') : trans('user.shop.pay_online')}}</td>
                            <td>{{$order->amount_tag}}</td>
                            <td>{{$order->created_at}}</td>
                            <td>{{empty($order->goods) || $order->goods_id === null || $order->status === 3 ? '' : $order->expired_at}}</td>
                            <td>{!! $order->status_label !!}</td>
                            <td>
                                <div class="btn-group">
                                    @if($order->status === 0 && $order->pay_way !== 1)
                                        @if ($order->payment)
                                            @if($order->payment->qr_code)
                                                <a href="{{route('orderDetail', $order->payment->trade_no)}}" target="_blank" class="btn btn-primary">{{trans('user.pay')}}</a>
                                            @elseif($order->payment->url)
                                                <a href="{{$order->payment->url}}" target="_blank" class="btn btn-primary">{{trans('user.pay')}}</a>
                                            @endif
                                        @endif
                                        <button onclick="closeOrder('{{route('closeOrder', $order)}}')" class="btn btn-danger">{{trans('common.cancel')}}</button>
                                    @elseif ($order->status === 1)
                                        <button onClick="window.location.reload();" class="btn btn-primary">
                                            <i class="icon wb-refresh" aria-hidden="true"></i></button>
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
                    <div class="col-12">
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
    <script>
      function closePlan() {
        swal.fire({
          title: '{{trans('user.invoice.active_prepaid_question')}}',
          html: @json(trans('user.invoice.active_prepaid_tips')),
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'POST',
              url: '{{route('cancelPlan')}}',
              dataType: 'json',
              data: {_token: '{{csrf_token()}}'},
              success: function(ret) {
                if (ret.status === 'success') {
                  swal.fire({
                    title: ret.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false,
                  }).then(() => window.location.reload());
                } else {
                  swal.fire({title: ret.message, icon: 'error'});
                }
              },
            });
          }
        });
      }

      function closeOrder(url) {
        swal.fire({
          title: '{{trans('common.close_item', ['attribute' => trans('user.invoice.attribute')])}}？',
          icon: 'warning',
          showCancelButton: true,
          cancelButtonText: '{{trans('common.close')}}',
          confirmButtonText: '{{trans('common.confirm')}}',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              method: 'PUT',
              url: url,
              dataType: 'json',
              data: {_token: '{{csrf_token()}}'},
              success: function(ret) {
                if (ret.status === 'success') {
                  swal.fire({
                    title: ret.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false,
                  }).then(() => window.location.reload());
                } else {
                  swal.fire({title: ret.message, icon: 'error'});
                }
              },
            });
          }
        });
      }
    </script>
@endsection
