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
                <div class="form-row">
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="invitee_email" id="invitee_email" value="{{Request::input('invitee_email')}}" placeholder="消费者"/>
                    </div>
                    <div class="form-group col-lg-4 col-sm-6">
                        <input type="text" class="form-control" name="inviter_email" id="inviter_email" value="{{Request::input('inviter_email')}}" placeholder="邀请人"/>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select name="status" id="status" class="form-control" onChange="Search()">
                            <option value="" hidden>状态</option>
                            <option value="0">未提现</option>
                            <option value="1">申请中</option>
                            <option value="2">已提现</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" onclick="Search()">搜 索</button>
                        <a href="{{route('admin.aff.rebate')}}" class="btn btn-danger">重 置</a>
                    </div>
                </div>
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
                        <th> 状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $vo)
                        <tr>
                            <td> {{$vo->id}} </td>
                            <td>
                                @if(empty($vo->invitee))
                                    【账号已删除】
                                @else
                                    <a href="{{route('admin.aff.rebate',['invitee_email' => $vo->invitee->email])}}"> {{$vo->invitee->email}} </a>
                                @endif
                            </td>
                            <td>
                                @if(empty($vo->inviter))
                                    【账号已删除】
                                @else
                                    <a href="{{route('admin.aff.rebate',['inviter_email' => $vo->inviter->email])}}"> {{$vo->inviter->email}} </a>
                                @endif
                            </td>
                            <td> {{$vo->order_id}} </td>
                            <td> {{$vo->amount}} </td>
                            <td> {{$vo->commission}} </td>
                            <td> {{$vo->created_at}} </td>
                            <td> {{$vo->updated_at}} </td>
                            <td>
                                @if ($vo->status === 1)
                                    <span class="badge badge-danger">申请中</span>
                                @elseif($vo->status === 2)
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
                        共 <code>{{$list->total()}}</code> 个申请
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
        $('#status').val({{Request::input('status')}});
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
        window.location.href = '{{route('admin.aff.rebate')}}?invitee_email=' + $('#invitee_email').val() +
            '&inviter_email=' + $('#inviter_email').val() + '&status=' + $('#status option:selected').val();
      }
    </script>
@endsection
