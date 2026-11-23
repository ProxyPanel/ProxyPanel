@extends('user.layouts')
@section('content')
<div class="page-content container">
    <div class="panel panel-bordered">
        <div class="panel-heading"><h1 class="panel-title">JangoPay</h1></div>
        <div class="panel-body">
            <p>{{ trans('user.payment.jangopay.local_amount') }}: <strong>{{ $payment->amount_tag }}</strong></p>
            <p>{{ trans('user.payment.jangopay.usd_amount') }}: <strong>USD {{ $attempt->amount_usd }}</strong></p>
            @if ($attempt->base_currency !== 'USD')
                <p class="text-muted">{{ trans('user.payment.jangopay.rate') }}: {{ $attempt->fx_rate }} USD / {{ $attempt->base_currency }}</p>
            @endif
            @if ($attempt->state === 'delivered')
                <div class="alert alert-success">{{ trans('user.payment.jangopay.completed') }}</div>
            @else
                <div class="alert alert-warning">{{ trans('user.payment.jangopay.review') }}</div>
            @endif
            <p><a href="{{ route('invoice.index') }}">{{ trans('user.payment.jangopay.orders') }}</a></p>
        </div>
    </div>
</div>
@endsection
