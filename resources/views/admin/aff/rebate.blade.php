@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.menu.promotion.rebate_flow') }}</h2>
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-lg-4 col-sm-6">
                        <input class="form-control" name="invitee_username" type="text" value="{{ Request::query('invitee_username') }}"
                               placeholder="{{ trans('model.aff.invitee') }}" />
                    </div>
                    <div class="form-group col-lg-4 col-sm-6">
                        <input class="form-control" name="inviter_username" type="text" value="{{ Request::query('inviter_username') }}"
                               placeholder="{{ trans('model.user.inviter') }}" />
                    </div>
                    <div class="form-group col-lg-2 col-sm-6">
                        <select class="form-control" id="status" name="status" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('common.status.attribute') }}">
                            <option value="0">{{ trans('common.status.withdrawal_pending') }}</option>
                            <option value="1">{{ trans('common.status.applying') }}</option>
                            <option value="2">{{ trans('common.status.withdrawn') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-sm-6 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.aff.invitee') }}</th>
                            <th> {{ trans('model.user.inviter') }}</th>
                            <th> {{ trans('model.order.id') }}</th>
                            <th> {{ trans('model.aff.amount') }}</th>
                            <th> {{ trans('model.aff.commission') }}</th>
                            <th> {{ trans('model.aff.created_at') }}</th>
                            <th> {{ trans('model.aff.updated_at') }}</th>
                            <th> {{ trans('common.status.attribute') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referralLogs as $referralLog)
                            <tr>
                                <td> {{ $referralLog->id }} </td>
                                <td>
                                    @if (empty($referralLog->invitee))
                                        【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                                    @else
                                        <a href="{{ route('admin.aff.rebate', ['invitee_username' => $referralLog->invitee->username]) }}">
                                            {{ $referralLog->invitee->username }} </a>
                                    @endif
                                </td>
                                <td>
                                    @if (empty($referralLog->inviter))
                                        【{{ trans('common.deleted_item', ['attribute' => trans('common.account')]) }}】
                                    @else
                                        <a href="{{ route('admin.aff.rebate', ['inviter_username' => $referralLog->inviter->username]) }}">
                                            {{ $referralLog->inviter->username }} </a>
                                    @endif
                                </td>
                                <td> {{ $referralLog->order_id }} </td>
                                <td> {{ $referralLog->amount }} </td>
                                <td> {{ $referralLog->commission }} </td>
                                <td> {{ $referralLog->created_at }} </td>
                                <td> {{ $referralLog->updated_at }} </td>
                                <td> {!! $referralLog->status_label !!} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.aff.counts', ['num' => $referralLogs->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $referralLogs->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    <script>
        $(document).ready(function() {
            $("#status").selectpicker("val", @json(Request::query('status')));
        });
    </script>
@endpush
