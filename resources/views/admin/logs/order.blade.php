@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        .table a {
            color: #76838f;
            text-decoration: none;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.logs.order.title') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('common.account') }}"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="sn" value="{{Request::query('sn')}}" placeholder="{{ trans('model.order.id') }}"/>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <input type="text" class="form-control" name="start" value="{{Request::query('start')}}" autocomplete="off"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ trans('common.to') }}</span>
                            </div>
                            <input type="text" class="form-control" name="end" value="{{Request::query('end')}}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select data-plugin="selectpicker" class="form-control show-tick" name="is_expire" id="is_expire" data-style="btn-outline btn-primary" title="{{ trans('admin.logs.order.is_expired') }}">
                            <option value="0"> {{ trans('admin.no') }}</option>
                            <option value="1"> {{ trans('admin.yes') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select data-plugin="selectpicker" class="form-control show-tick" name="is_coupon" id="is_coupon" data-style="btn-outline btn-primary"
                                title="{{ trans('admin.logs.order.is_coupon') }}">
                            <option value="0"> {{ trans('admin.no') }}</option>
                            <option value="1"> {{ trans('admin.yes') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select data-plugin="selectpicker" class="form-control show-tick" name="pay_way" id="pay_way" data-style="btn-outline byn-primary" title="{{ trans('model.order.pay_way') }}">
                            @foreach(config('common.payment.labels') as $key => $value)
                                <option value="{{$key}}">{{$key.' - '.$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select data-plugin="selectpicker" class="form-control show-tick" name="status[]" id="status" data-style="btn-outline btn-primary" title="{{ trans('model.order.status') }}"
                                multiple>
                            <option value="-1">{{ trans('common.order.status.cancel') }}</option>
                            <option value="0">{{ trans('common.payment.status.wait') }}</option>
                            <option value="1">{{ trans('common.order.status.review') }}</option>
                            <option value="2">{{ trans('common.order.status.complete').'/'.trans('common.status.expire').'/'.trans('common.order.status.ongoing') }}</option>
                            <option value="3">{{ trans('common.order.status.prepaid') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.order')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> @sortablelink('id', '#')</th>
                        <th> {{ trans('common.account') }}</th>
                        <th> @sortablelink('sn', trans('model.order.id'))</th>
                        <th> {{ trans('model.goods.attribute') }}</th>
                        <th> {{ trans('model.coupon.attribute') }}</th>
                        <th> {{ trans('model.order.original_price') }}</th>
                        <th> {{ trans('model.order.price') }}</th>
                        <th> {{ trans('model.order.pay_way') }}</th>
                        <th> {{ trans('model.order.status') }}</th>
                        <th> @sortablelink('expired_at', trans('common.expired_at'))</th>
                        <th> @sortablelink('created_at', trans('common.created_at'))</th>
                        @can(['admin.order.edit'])
                            <th> {{ trans('common.action') }}</th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td> {{$order->id}} </td>
                            <td>
                                @if(empty($order->user) )
                                    【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                                @else
                                    @can('admin.user.index')
                                        <a href="{{route('admin.user.index', ['id'=>$order->user->id])}}" target="_blank">{{$order->user->username}} </a>
                                    @else
                                        {{$order->user->username}}
                                    @endcan
                                @endif
                            </td>
                            <td> {{$order->sn}}</td>
                            <td> {{$order->goods->name  ?? trans('user.recharge_credit')}} </td>
                            <td> {{$order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : ''}} </td>
                            <td> {{$order->origin_amount_tag}} </td>
                            <td> {{$order->amount_tag}} </td>
                            <td>
                                {{$order->pay_way_label}}
                            </td>
                            <td>
                                {!! $order->status_label !!}
                            </td>
                            <td> {{$order->is_expire ? trans('common.status.expire') : $order->expired_at}} </td>
                            <td> {{$order->created_at}} </td>
                            @can(['admin.order.edit'])
                                <td>
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" aria-expanded="false">
                                        <i class="icon wb-wrench" aria-hidden="true"></i>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        @if ($order->status !== -1)
                                            <a class="dropdown-item" href="javascript:changeStatus('{{$order->id}}', -1)" role="menuitem">
                                                <i class="icon wb-close" aria-hidden="true"></i>
                                                {{ trans('admin.set_to', ['attribute' => $order->statusTags(-1, 0, false)]) }}
                                            </a>
                                        @endif
                                        @if ($order->status !== 2)
                                            <a class="dropdown-item" href="javascript:changeStatus('{{$order->id}}', 2)" role="menuitem">
                                                <i class="icon wb-check" aria-hidden="true"></i>
                                                {{ trans('admin.set_to', ['attribute' => $order->statusTags(2, 0, false)]) }}
                                            </a>
                                        @endif
                                        @if ($order->status !== 3)
                                            <a class="dropdown-item" href="javascript:changeStatus('{{$order->id}}', 3)" role="menuitem">
                                                <i class="icon wb-check-circle" aria-hidden="true"></i>
                                                {{ trans('admin.set_to', ['attribute' => $order->statusTags(3, 0, false)]) }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.logs.counts', ['num' => $orders->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$orders->links()}}
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
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
      $(document).ready(function() {
        $('#is_coupon').selectpicker('val', @json(Request::query('is_coupon')));
        $('#is_expire').selectpicker('val', @json(Request::query('is_expire')));
        $('#pay_way').selectpicker('val', @json(Request::query('pay_way')));
        $('#status').selectpicker('val', @json(Request::query('status')));
        $('select').on('change', function() {
          this.form.submit();
        });
      });

      // 有效期
      $('.input-daterange').datepicker({format: 'yyyy-mm-dd'});

      @can('admin.order.edit')
      // 重置流量
      function changeStatus(id, status) {
        $.post('{{route('admin.order.edit')}}', {_token: '{{csrf_token()}}', oid: id, status: status}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({
              title: ret.message,
              icon: 'success',
              timer: 1000,
              showConfirmButton: false,
            }).then(() => window.location.reload());
          } else {
            swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
          }
        });
      }
        @endcan
    </script>
@endsection
