@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.promotion.withdraw')" :theads="[
            '#',
            trans('model.referral.created_at'),
            trans('model.referral.user'),
            trans('model.referral.amount'),
            trans('common.status.attribute'),
            trans('model.aff.updated_at'),
            trans('common.action'),
        ]" :count="trans('admin.aff.apply_counts', ['num' => $applyList->total()])" :pagination="$applyList->links()">
            <x-slot:filters>
                <x-admin.filter.input class="col-lg-2 col-sm-4" name="username" :placeholder="trans('model.user.username')" />
                <x-admin.filter.selectpicker class="col-lg-2 col-sm-4" name="status" :title="trans('common.status.attribute')" :options="[
                    -1 => trans('common.status.rejected'),
                    0 => trans('common.status.review'),
                    1 => trans('common.status.reviewed'),
                    2 => trans('common.status.paid'),
                ]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($applyList as $apply)
                    <tr>
                        <td> {{ $apply->id }} </td>
                        <td> {{ $apply->created_at }} </td>
                        <td>
                            @if (empty($apply->user))
                                【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                            @else
                                @can('admin.user.index')
                                    <a href="{{ route('admin.user.index', ['id' => $apply->user_id]) }}" target="_blank">
                                        {{ $apply->user->username }}
                                    </a>
                                @else
                                    {{ $apply->user->username }}
                                @endcan
                            @endif
                        </td>
                        <td> {{ $apply->amount_tag }} </td>
                        <td>
                            @if ($apply->status === -1)
                                <span class="badge badge-lg badge-danger"> {{ trans('common.status.rejected') }} </span>
                            @elseif($apply->status === 0)
                                <span class="badge badge-lg badge-info"> {{ trans('common.status.review') }} </span>
                            @elseif($apply->status === 2)
                                <span class="badge badge-lg badge-success"> {{ trans('common.status.paid') }} </span>
                            @else
                                <span class="badge badge-lg badge-default"> {{ trans('common.status.payment_pending') }} </span>
                            @endif
                        </td>
                        <td> {{ $apply->created_at === $apply->updated_at ? '' : $apply->updated_at }} </td>
                        <td>
                            @canany(['admin.aff.setStatus', 'admin.aff.detail'])
                                <div class="btn-group">
                                    @can('admin.aff.setStatus')
                                        @if ($apply->status === 0)
                                            <a class="btn btn-sm btn-success" href="javascript:setStatus('{{ $apply->id }}','1')">
                                                <i class="icon wb-check" aria-hidden="true"></i>{{ trans('common.status.pass') }}
                                            </a>
                                            <a class="btn btn-sm btn-danger" href="javascript:setStatus('{{ $apply->id }}','-1')">
                                                <i class="icon wb-close" aria-hidden="true"></i>{{ trans('common.status.reject') }}
                                            </a>
                                        @elseif($apply->status === 1)
                                            @can('admin.user.updateCredit')
                                                <a class="btn btn-sm btn-success"
                                                   href="javascript:handleUserCredit('{{ $apply->user->id }}','{{ $apply->amount }}', '{{ $apply->id }}','2')">
                                                    <i class="icon wb-payment" id="makePayment_{{ $apply->id }}" aria-hidden="true"></i>
                                                    {{ trans('common.status.send_to_credit') }}
                                                </a>
                                            @endcan
                                            <a class="btn btn-sm btn-primary" href="javascript:setStatus('{{ $apply->id }}', '2')">
                                                <i class="icon wb-check-circle" aria-hidden="true"></i> {{ trans('common.status.paid') }}
                                            </a>
                                        @endif
                                    @endcan
                                    @can('admin.aff.detail')
                                        <a class="btn btn-sm btn-default" href="{{ route('admin.aff.detail', $apply->id) }}">
                                            <i class="icon wb-search"></i></a>
                                    @endcan
                                </div>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script>
        @can('admin.aff.setStatus')
            // 更改状态
            function setStatus(aff, status) {
                ajaxPut(jsRoute('{{ route('admin.aff.setStatus', 'PLACEHOLDER') }}', aff), {
                    status: status
                }, {
                    error: function(xhr) {
                        handleErrors(xhr)
                    }
                });
            }
        @endcan

        @can('admin.user.updateCredit')
            // 余额充值
            function handleUserCredit(uid, amount, aid, status) {
                ajaxPost(jsRoute('{{ route('admin.user.updateCredit', 'PLACEHOLDER') }}', uid), {
                    amount: amount,
                    description: '{{ trans('admin.aff.referral') }}'
                }, {
                    success: function(response) {
                        if (response.status === "success") {
                            showMessage({
                                title: response.message,
                                icon: "success",
                                callback: function() {
                                    setStatus(aid, status);
                                }
                            });
                        } else {
                            showMessage({
                                title: response.message,
                                icon: "error",
                                callback: function() {
                                    window.location.reload();
                                }
                            });
                        }
                    },
                    beforeSend: function() {
                        $("#makePayment_" + aid).removeClass("wb-payment").addClass("wb-loop icon-spin");
                    }
                });
            }
        @endcan
    </script>
@endpush
