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
                <form class="form-row">
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="用户账号"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <input type="number" class="form-control" name="sn" value="{{Request::query('sn')}}" placeholder="订单号"/>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <input type="text" class="form-control" name="start" value="{{Request::query('start')}}" autocomplete="off"/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">至</span>
                            </div>
                            <input type="text" class="form-control" name="end" value="{{Request::query('end')}}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" id="is_expire" name="is_expire">
                            <option value="" hidden>是否过期</option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" id="is_coupon" name="is_coupon">
                            <option value="" hidden>是否使用优惠券</option>
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" id="pay_way" name="pay_way">
                            <option value="" hidden>支付方式</option>
                            @foreach(config('common.payment.labels') as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" name="status" id="status">
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
                                <input type="radio" name="sort" value="1"/>
                                <label for="type">升序</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.order')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 用户账号</th>
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
                    @foreach($orders as $order)
                        <tr>
                            <td> {{$order->id}} </td>
                            <td>
                                @if(empty($order->user) )
                                    【账号不存在】
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
                            <td> {{$order->is_expire ? '已过期' : $order->expired_at}} </td>
                            <td> {{$order->coupon ? $order->coupon->name . ' - ' . $order->coupon->sn : ''}} </td>
                            <td> ¥{{$order->origin_amount}} </td>
                            <td> ¥{{$order->amount}} </td>
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
                        共 <code>{{$orders->total()}}</code> 个订单
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
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script>
        $(document).ready(function() {
            $('#is_expire').val({{Request::query('is_expire')}});
            $('#is_coupon').val({{Request::query('is_coupon')}});
            $('#pay_way').val({{Request::query('pay_way')}});
            $('#status').val({{Request::query('status')}});
            $("input[name='sort'][value='{{Request::query('sort')}}']").click();

            $('select').on('change', function() { this.form.submit(); });
        });

        // 有效期
        $('.input-daterange').datepicker({format: 'yyyy-mm-dd'});
    </script>
@endsection
