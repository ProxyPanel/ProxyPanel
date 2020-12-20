@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">回调日志
                    <small>(在线支付)</small>
                </h2>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="number" class="form-control" name="out_trade_no" id="out_trade_no"
                               value="{{Request::input('out_trade_no')}}" placeholder="本地订单号" autocomplete="off"/>
                    </div>
                    <div class="form-group col-lg-3 col-sm-6">
                        <input type="number" class="form-control" name="trade_no" id="trade_no"
                               value="{{Request::input('trade_no')}}" placeholder="外部订单号" autocomplete="off"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="type" id="type" onChange="Search()">
                            <option value="" hidden>支付方式</option>
                            <option value="f2fpay">当面付</option>
                            <option value="codepay">码支付</option>
                            <option value="payjs">PayJs</option>
                            <option value="bitpayx">麻瓜宝</option>
                            <option value="paypal">PayPal</option>
                            <option value="epay">易支付</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4">
                        <select class="form-control" name="trade_status" id="trade_status" onChange="Search()">
                            <option value="" hidden>交易状态</option>
                            <option value="1">成功</option>
                            <option value="0">失败</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-4 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.payment.callback')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 支付方式</th>
                        <th> 平台订单号</th>
                        <th> 本地订单号</th>
                        <th> 交易金额</th>
                        <th> 状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td> {{$vo->type_label}} </td>
                            <td> {{$vo->trade_no}} </td>
                            <td>
                                @can('admin.order')
                                    <a href="{{route('admin.order', ['order_sn' => $vo->out_trade_no])}}" target="_blank"> {{$vo->out_trade_no}} </a>
                                @else
                                    {{$vo->out_trade_no}}
                                @endcan
                            </td>
                            <td> {{$vo->amount}}元</td>
                            <td> {!! $vo->trade_status_label !!} </td>
                            <td> {{$vo->created_at}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$list->total()}}</code> 个账号
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$list->links()}}
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
        $('#trade_status').val({{Request::input('trade_status')}});
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
        window.location.href = '{{route('admin.payment.callback')}}?out_trade_no=' + $('#trade_no').val() + '&trade_no=' +
            $('#out_trade_no').val() + '&type=' + $('#type option:selected').val() + '&trade_status=' + $('#trade_status option:selected').val();
      }
    </script>
@endsection
