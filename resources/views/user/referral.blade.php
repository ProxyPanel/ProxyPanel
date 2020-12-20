@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title cyan-600"><i class="icon wb-star"></i>{{trans('home.referrals')}}</h1>
    </div>
    <div class="page-content  container-fluid">
        <x-alert type="success" :message="trans('home.promote_link', ['traffic' => $referral_traffic, 'referral_percent' => $referral_percent * 100])"/>
        <div class="row">
            <div class="col-lg-5">
                <!-- 推广链接 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-link"></i>
                            {{trans('home.referral_my_link')}}
                        </h4>
                        <div class="card-text form">
                            <div class="mt-clipboard-container input-group">
                                <input type="text" id="mt-target-1" class="form-control" value="{{$aff_link}}"/>
                                <button class="btn btn-info mt-clipboard" data-clipboard-action="copy" data-clipboard-text="{{$aff_link}}">
                                    <i class="icon wb-copy"></i> {{trans('home.referral_button')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 邀请记录 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-emoticon"></i>
                            {{trans('home.invite_user_title')}}
                        </h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('home.invite_user_email')}} </th>
                                <th> {{trans('home.invite_user_created_at')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralUserList as $vo)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{str_replace(mb_substr($vo->email, 3, 4), "****", $vo->email)}}  </td>
                                    <td> {{$vo->created_at}} </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer card-footer-transparent">
                        <nav class="Page navigation float-right">
                            {{$referralUserList->appends(Arr::except(Request::query(), 'user_page'))->links()}}
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <!-- 佣金记录 -->
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title cyan-600">
                            <i class="icon wb-star-half"></i>{{trans('home.referral_title')}}
                        </h3>
                        <div class="panel-actions">
                            <button type="submit" class="btn btn-danger" onclick="extractMoney()">
                                {{trans('home.referral_table_apply')}}
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('home.referral_table_date')}} </th>
                                <th> {{trans('home.referral_table_user')}} </th>
                                <th> {{trans('home.referral_table_amount')}} </th>
                                <th> {{trans('home.referral_table_commission')}} </th>
                                <th> {{trans('home.referral_table_status')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralLogList as $referralLog)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{$referralLog->created_at}} </td>
                                    <td> {{empty($referralLog->invitee) ? '【账号已删除】' : str_replace(mb_substr($referralLog->invitee->email, 3, 4), "****", $referralLog->invitee->email)}} </td>
                                    <td> ￥{{$referralLog->amount}} </td>
                                    <td> ￥{{$referralLog->commission}} </td>
                                    <td>
                                        @if ($referralLog->status === 1)
                                            <span class="badge badge-sm badge-info">申请中</span>
                                        @elseif($referralLog->status === 2)
                                            <span>已提现</span>
                                        @else
                                            <span class="badge badge-sm badge-success">未提现</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                {{trans('home.referral_summary', ['total' => $referralLogList->total(), 'amount' => $canAmount, 'money' => $referral_money])}}
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <nav class="Page navigation float-right">
                                    {{$referralLogList->appends(Arr::except(Request::query(), 'user_page'))->links()}}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 提现记录 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i
                                    class="icon wb-star-outline"></i> {{trans('home.referral_apply_title')}}</h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('home.referral_apply_table_date')}} </th>
                                <th> {{trans('home.referral_apply_table_amount')}} </th>
                                <th> {{trans('home.referral_apply_table_status')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralApplyList as $vo)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{$vo->created_at}} </td>
                                    <td> ￥{{$vo->amount}} </td>
                                    <td>
                                        @if ($vo->status === 0)
                                            <span class="badge badge-sm badge-warning">待审核</span>
                                        @elseif($vo->status === 1)
                                            <span class="badge badge-sm badge-info">审核通过 - 待打款</span>
                                        @elseif($vo->status === 2)
                                            <span>已打款</span>
                                        @else
                                            <span class="badge badge-sm badge-dark">驳回</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer card-footer-transparent">
                        <nav class="Page navigation float-right">
                            {{$referralApplyList->appends(Arr::except(Request::query(), 'user_page'))->links()}}
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
    <script src="/assets/custom/clipboardjs/clipboard.min.js"></script>
    <script>
      // 申请提现
      function extractMoney() {
        $.post('{{route('applyCommission')}}', {_token: '{{csrf_token()}}'}, function(ret) {
          if (ret.status === 'success') {
            swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => {
              window.location.reload();
            });
          } else {
            swal.fire('申请失败', ret.message, 'error');
          }
        });
      }

      const clipboard = new ClipboardJS('.mt-clipboard');
      clipboard.on('success', function() {
        swal.fire({
          title: '复制成功',
          icon: 'success',
          timer: 1300,
          showConfirmButton: false,
        });
      });
      clipboard.on('error', function() {
        swal.fire({
          title: '复制失败，请手动复制',
          icon: 'error',
          timer: 1500,
          showConfirmButton: false,
        });
      });
    </script>
@endsection
