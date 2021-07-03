@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">返利流水记录</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="invitee_username" value="{{Request::query('invitee_username')}}" placeholder="消费者"/>
                    </div>
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="inviter_username" value="{{Request::query('inviter_username')}}" placeholder="邀请人"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                            <option value="" hidden>状态</option>
                            <option value="0">未提现</option>
                            <option value="1">申请中</option>
                            <option value="2">已提现</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button type="submit" class="btn btn-primary">搜 索</button>
                        <a href="{{route('admin.aff.rebate')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 消费者</th>
                        <th> 邀请者</th>
                        <th> 订单号</th>
                        <th> 消费金额</th>
                        <th> 返利金额</th>
                        <th> 生成时间</th>
                        <th> 处理时间</th>
                        <th> {{trans('common.status')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($referralLogs as $referralLog)
                        <tr>
                            <td> {{$referralLog->id}} </td>
                            <td>
                                @if(empty($referralLog->invitee))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    <a href="{{route('admin.aff.rebate',['invitee_username' => $referralLog->invitee->username])}}"> {{$referralLog->invitee->username}} </a>
                                @endif
                            </td>
                            <td>
                                @if(empty($referralLog->inviter))
                                    【{{trans('common.deleted_item', ['attribute' => trans('common.account')])}}】
                                @else
                                    <a href="{{route('admin.aff.rebate',['inviter_username' => $referralLog->inviter->username])}}"> {{$referralLog->inviter->username}} </a>
                                @endif
                            </td>
                            <td> {{$referralLog->order_id}} </td>
                            <td> {{$referralLog->amount}} </td>
                            <td> {{$referralLog->commission}} </td>
                            <td> {{$referralLog->created_at}} </td>
                            <td> {{$referralLog->updated_at}} </td>
                            <td>
                                @if ($referralLog->status === 1)
                                    <span class="badge badge-danger">申请中</span>
                                @elseif($referralLog->status === 2)
                                    <span class="badge badge-default">已提现</span>
                                @else
                                    <span class="badge badge-info">未提现</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        共 <code>{{$referralLogs->total()}}</code> 个申请
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$referralLogs->links()}}
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
            $('#status').val({{Request::query('status')}});
        });
    </script>
@endsection
