@extends('admin.layouts')
@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered">
        <div class="panel-heading"><h1 class="panel-title">JangoPay · {{ trans('user.payment.jangopay.reconciliation') }}</h1></div>
        <div class="panel-body">
            <div class="alert alert-info">{{ trans('user.payment.jangopay.recovery_help') }}</div>
            <form method="get" action="{{ route('admin.jangopay.index') }}" class="form-inline">
                <label for="jp-order">{{ trans('user.payment.jangopay.order_reference') }}</label>
                <input class="form-control" id="jp-order" name="order" value="{{ request('order') }}" maxlength="120">
                <label for="jp-state">{{ trans('user.payment.jangopay.state') }}</label>
                <select class="form-control" id="jp-state" name="state">
                    <option value="">{{ trans('user.payment.jangopay.all_states') }}</option>
                    @foreach (['reserved','creating','unknown_creation','awaiting_payment','quoting','quote_unknown','quoted','fulfilling','manual_review','delivered','resolved'] as $state)
                        <option value="{{ $state }}" @selected(request('state') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">{{ trans('user.payment.jangopay.search') }}</button>
            </form>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>{{ trans('user.payment.jangopay.order_reference') }}</th><th>USD</th><th>{{ trans('user.payment.jangopay.state') }}</th><th>{{ trans('user.payment.jangopay.remote_state') }}</th><th>{{ trans('user.payment.jangopay.action') }}</th></tr></thead>
                    <tbody>
                    @foreach ($attempts as $attempt)
                        <tr>
                            <td>{{ $attempt->id }}</td>
                            <td>{{ $attempt->order_id }}<br>{{ $attempt->invoice_id }}<br>{{ $attempt->txn_id }}<br>{{ $attempt->tx_hash }}</td>
                            <td>{{ $attempt->amount_usd }}</td>
                            <td>{{ $attempt->state }}<br>{{ $attempt->failure_code }}<br>{{ $attempt->review_note }}</td>
                            <td>{{ $attempt->remote_invoice_status }} / {{ $attempt->remote_txn_status }}<br>{{ $attempt->last_checked_at }}</td>
                            <td>
                                @can('admin.jangopay.check')
                                <form method="post" action="{{ route('admin.jangopay.check', ['id'=>$attempt->id]) }}">@csrf
                                    <label for="jp-invoice-{{ $attempt->id }}">{{ trans('user.payment.jangopay.invoice_hint') }}</label>
                                    <input id="jp-invoice-{{ $attempt->id }}" class="form-control" name="invoice_id" maxlength="120">
                                    <label for="jp-txn-{{ $attempt->id }}">{{ trans('user.payment.jangopay.transaction_hint') }}</label>
                                    <input id="jp-txn-{{ $attempt->id }}" class="form-control" name="txn_id" maxlength="120">
                                    <button type="submit" class="btn btn-default">{{ trans('user.payment.jangopay.recheck') }}</button>
                                </form>
                                @endcan
                                @if (in_array($attempt->state, \App\Models\JangoPayAttempt::RESOLVABLE_STATES, true))
                                @can('admin.jangopay.resolve')
                                <form method="post" action="{{ route('admin.jangopay.resolve', ['id'=>$attempt->id]) }}">@csrf
                                    <label for="jp-note-{{ $attempt->id }}">{{ trans('user.payment.jangopay.resolution_note') }}</label>
                                    <textarea class="form-control" id="jp-note-{{ $attempt->id }}" name="note" minlength="10" maxlength="1000" required></textarea>
                                    <label><input type="checkbox" name="confirm" value="1" required> {{ trans('user.payment.jangopay.resolve_confirm') }}</label>
                                    <button type="submit" class="btn btn-warning">{{ trans('user.payment.jangopay.resolve') }}</button>
                                </form>
                                @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $attempts->links() }}
            <h2>{{ trans('user.payment.jangopay.operator_log') }}</h2>
            <ul>@foreach ($actions as $action)<li>{{ $action->created_at }} · #{{ $action->attempt_id }} · {{ $action->user_id }} · {{ $action->action }} · {{ $action->note }}</li>@endforeach</ul>
        </div>
    </div>
</div>
@endsection
