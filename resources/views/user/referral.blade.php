@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title cyan-600"><i class="icon wb-star"></i>{{trans('user.menu.referrals')}}</h1>
    </div>
    <div class="page-content  container-fluid">
        <x-alert type="success" :message="trans('user.invite.promotion', ['traffic' => $referral_traffic, 'referral_percent' => $referral_percent * 100])"/>
        <div class="row">
            <div class="col-lg-5">
                <!-- 推广链接 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-link"></i>
                            {{trans('user.referral.link')}}
                        </h4>
                        <div class="card-text form">
                            <div class="mt-clipboard-container input-group">
                                <input type="text" id="mt-target-1" class="form-control" value="{{$aff_link}}"/>
                                <button class="btn btn-info mt-clipboard" data-clipboard-action="copy" data-clipboard-text="{{$aff_link}}">
                                    <i class="icon wb-copy"></i> {{trans('common.copy.attribute')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 邀请记录 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-emoticon"></i>
                            {{trans('user.invite.logs')}}
                        </h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('validation.attributes.email')}} </th>
                                <th> {{trans('user.registered_at')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralUserList as $user)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{str_replace(mb_substr($user->username, 3, 4), "****", $user->username)}}  </td>
                                    <td> {{$user->created_at}} </td>
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
                            <i class="icon wb-star-half"></i>{{trans('user.referral.logs')}}
                        </h3>
                        <div class="panel-actions">
                            <button type="submit" class="btn btn-danger" onclick="extractMoney()">
                                {{trans('user.withdraw')}}
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('user.consumer')}} </th>
                                <th> {{trans('user.referral.amount')}} </th>
                                <th> {{trans('user.referral.commission')}} </th>
                                <th> {{trans('common.created_at')}} </th>
                                <th> {{trans('common.status')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralLogList as $referralLog)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{empty($referralLog->invitee) ? '【'.trans('common.deleted_item', ['attribute' => trans('common.account')]).'】' : str_replace(mb_substr($referralLog->invitee->username, 3, 4), "****", $referralLog->invitee->username)}} </td>
                                    <td> ¥{{$referralLog->amount}} </td>
                                    <td> ¥{{$referralLog->commission}} </td>
                                    <td> {{$referralLog->created_at}} </td>
                                    <td>{!! $referralLog->status_label !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                {{trans('user.referral.total', ['amount' => $canAmount, 'total' => $referralLogList->total(), 'money' => $referral_money])}}
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
                                class="icon wb-star-outline"></i> {{trans('user.withdraw_logs')}}</h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                            <tr>
                                <th data-cell-style="cellStyle"> #</th>
                                <th> {{trans('user.withdraw_at')}} </th>
                                <th> {{trans('user.withdraw_commission')}} </th>
                                <th> {{trans('common.status')}} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralApplyList as $referralApply)
                                <tr>
                                    <td> {{$loop->iteration}} </td>
                                    <td> {{$referralApply->created_at}} </td>
                                    <td> ¥{{$referralApply->amount}} </td>
                                    <td>
                                        {!! $referralApply->status_label !!}
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
                    swal.fire(ret.title, ret.message, 'error');
                }
            });
        }

        const clipboard = new ClipboardJS('.mt-clipboard');
        clipboard.on('success', function() {
            swal.fire({
                title: '{{trans('common.copy.success')}}',
                icon: 'success',
                timer: 1300,
                showConfirmButton: false,
            });
        });
        clipboard.on('error', function() {
            swal.fire({
                title: '{{trans('common.copy.failed')}}',
                icon: 'error',
                timer: 1500,
                showConfirmButton: false,
            });
        });
    </script>
@endsection
