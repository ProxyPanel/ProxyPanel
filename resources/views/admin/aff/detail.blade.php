@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">提现申请详情</h2>
                <div class="panel-actions">
                    @if($basic->status === -1)
                        <span class="badge badge-lg badge-danger"> 已驳回 </span>
                    @elseif($basic->status === 2)
                        <span class="badge badge-lg badge-success"> 已打款 </span>
                    @endif
                    <a href="{{route('admin.aff.index')}}" class="btn btn-danger"> 返 回</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="example">
                    <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                        <thead class="thead-default">
                        <tr>
                            <th colspan="6">
                                申请单ID：{{$basic->id}} | 申请人：{{$basic->user->email}} | 申请提现金额：￥{{$basic->amount}} | 申请时间：{{$basic->created_at}}
                            </th>
                        </tr>
                        <tr>
                            <th> #</th>
                            <th> 关联人</th>
                            <th> 关联订单</th>
                            <th> 订单金额</th>
                            <th> 佣金</th>
                            <th> 下单时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($commissions as $commission)

                            <tr>
                                <td> {{$commission->id}} </td>
                                <td> {{$commission->invitee->email ?? '【账号已删除】'}} </td>
                                <td>
                                    @can('admin.order')
                                        <a href="{{route('admin.order', ['id' => $commission->order->id])}}" target="_blank">
                                            {{$commission->order->goods->name}}
                                        </a>
                                    @else
                                        {{$commission->order->goods->name}}
                                    @endcan
                                </td>
                                <td> ￥{{$commission->amount}} </td>
                                <td> ￥{{$commission->commission}} </td>
                                <td> {{$commission->created_at}} </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        本申请共涉及 <code>{{$commissions->total()}}</code> 单
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$commissions->links()}}
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
@endsection
