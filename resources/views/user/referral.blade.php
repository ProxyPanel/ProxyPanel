@extends('user.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-header">
        <h1 class="page-title cyan-600"><i class="icon wb-star"></i>{{ trans('user.menu.promotion') }}</h1>
    </div>
    <div class="page-content container-fluid">
        <x-alert :message="trans('user.invite.promotion.base', ['traffic' => $referral_traffic]) .
            trans('user.invite.promotion.bonus.' . $referral_reward_mode, ['referral_percent' => $referral_percent])" />
        <div class="row">
            <div class="col-lg-5">
                <!-- 推广链接 -->
                <div class="card card-inverse card-shadow bg-white node">
                    <div class="card-block p-30 row">
                        <div class="col-auto" id="qrcode"></div>
                        <div class="col text-break">
                            <h4 class="card-title cyan-600"><i class="icon wb-link"></i>
                                {{ trans('user.referral.link') }}
                            </h4>
                            <div class="input-group">
                                <input class="form-control" id="mt-target-1" type="text" value="{{ $aff_link }}" />
                            </div>
                            <div class="btn-group float-right pt-4">
                                <button class="btn btn-outline-primary" onclick="Download()">
                                    <i class="icon wb-download"></i> {{ trans('common.download') }}
                                </button>
                                <button class="btn btn-info mt-clipboard">
                                    <i class="icon wb-copy"></i> {{ trans('common.copy.attribute') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 邀请记录 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-emoticon"></i>
                            {{ trans('user.invite.logs') }}
                        </h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th data-cell-style="cellStyle"> #</th>
                                    <th> {{ trans('model.user.username') }} </th>
                                    <th> {{ trans('user.registered_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referralUserList as $user)
                                    <tr>
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ str_replace(mb_substr($user->username, 3, 4), '****', $user->username) }} </td>
                                        <td> {{ $user->created_at }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer card-footer-transparent">
                        <nav class="Page navigation float-right">
                            {{ $referralUserList->appends(Arr::except(Request::query(), 'user_page'))->links() }}
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <!-- 佣金记录 -->
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title cyan-600">
                            <i class="icon wb-star-half"></i>{{ trans('user.referral.logs') }}
                        </h3>
                        <div class="panel-actions">
                            <button class="btn btn-danger" type="submit" onclick="extractMoney()">
                                {{ trans('user.withdraw') }}
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th data-cell-style="cellStyle"> #</th>
                                    <th> {{ trans('model.aff.invitee') }} </th>
                                    <th> {{ trans('model.aff.amount') }} </th>
                                    <th> {{ trans('model.aff.commission') }} </th>
                                    <th> {{ trans('common.created_at') }} </th>
                                    <th> {{ trans('common.status.attribute') }} </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referralLogList as $referralLog)
                                    <tr>
                                        <td> {{ $loop->iteration + ($referralLogList->currentPage() - 1) * $referralLogList->perPage() }} </td>
                                        <td> {{ empty($referralLog->invitee) ? '【' . trans('common.deleted_item', ['attribute' => trans('common.account')]) . '】' : str_replace(mb_substr($referralLog->invitee->username, 3, 4), '****', $referralLog->invitee->username) }}
                                        </td>
                                        <td> {{ $referralLog->amount_tag }} </td>
                                        <td> {{ $referralLog->commission_tag }} </td>
                                        <td> {{ $referralLog->created_at }} </td>
                                        <td>{!! $referralLog->status_label !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                {{ trans('user.referral.total', ['amount' => $canAmount, 'total' => $referralLogList->total(), 'money' => $referral_money]) }}
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <nav class="Page navigation float-right">
                                    {{ $referralLogList->appends(Arr::except(Request::query(), 'log_page'))->links() }}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 提现记录 -->
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title cyan-600"><i class="icon wb-star-outline"></i> {{ trans('user.withdraw_logs') }}</h4>
                        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                            <thead class="thead-default">
                                <tr>
                                    <th data-cell-style="cellStyle"> #</th>
                                    <th> {{ trans('user.withdraw_at') }} </th>
                                    <th> {{ trans('user.withdraw_commission') }} </th>
                                    <th> {{ trans('common.status.attribute') }} </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referralApplyList as $referralApply)
                                    <tr>
                                        <td> {{ $loop->iteration + ($referralApplyList->currentPage() - 1) * $referralApplyList->perPage() }} </td>
                                        <td> {{ $referralApply->created_at }} </td>
                                        <td> {{ $referralApply->amount_tag }} </td>
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
                            {{ $referralApplyList->appends(Arr::except(Request::query(), 'apply_page'))->links() }}
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
    <script src="/assets/custom/easy.qrcode.min.js"></script>
    <script>
        // Options
        const options = {
            text: @json($aff_link),
            dotScale: 0.9,
            width: 144,
            height: 144,
            backgroundImage: "/assets/images/logo_original.png",
            backgroundImageAlpha: 1,
            PO_TL: "#007bff",
            PI_TL: "#17a2b8",
            PI_TR: "#fd7e14",
            PO_TR: "#28a745",
            PI_BL: "#ffc107",
            PO_BL: "#17a2b8",
            AO: "#fd7e14",
            AI: "#20c997",
            autoColor: true
        };

        // Create QRCode Object
        new QRCode(document.getElementById("qrcode"), options);

        function Download() {
            const canvas = document.getElementsByTagName("canvas")[0];
            canvas.toBlob((blob) => {
                let link = document.createElement("a");
                link.download = "qr.png";

                let reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onload = () => {
                    link.href = reader.result;
                    link.click();
                };
            }, "image/png");
        }

        $(document).on('click', '.mt-clipboard', function() {
            copyToClipboard($('#mt-target-1').val());
        });

        // 申请提现
        function extractMoney() {
            ajaxPost('{{ route('referral.withdraw') }}');
        }
    </script>
@endsection
