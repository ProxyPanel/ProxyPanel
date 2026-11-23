<?php

use App\Models\JangoPayAttempt;
use App\Models\PaymentCallback;
use App\Utils\Payments\JangoPay;
use Illuminate\Database\Capsule\Manager as Capsule;

check(! method_exists($gateway, 'choose'), 'gateway exposes no local coin-selection handler');
$mode = 'healthy';
foreach (['missing_invoice', 'path_invoice', 'query_invoice', 'long_invoice', 'trade', 'payment_binding', 'owner', 'gateway', 'order_amount', 'payment_amount'] as $case) {
    [$a, $o, $p] = $seed('awaiting_payment');
    if ($case === 'missing_invoice') {
        $a->invoice_id = null;
    } elseif ($case === 'path_invoice') {
        $a->invoice_id = '../OTHER';
    } elseif ($case === 'query_invoice') {
        $a->invoice_id = 'I1?redirect=evil';
    } elseif ($case === 'long_invoice') {
        $a->invoice_id = str_repeat('a', 121);
    } elseif ($case === 'trade') {
        $p->trade_no = 'OTHER';
    } elseif ($case === 'payment_binding') {
        $a->payment_id = $p->id + 1;
    } elseif ($case === 'owner') {
        $p->user_id = 2;
    } elseif ($case === 'gateway') {
        $o->pay_way = 'other';
    } elseif ($case === 'order_amount') {
        $o->amount = 4;
    } else {
        $p->amount = 4;
    }
    $p->setRelation('order', $o);
    check(! JangoPay::canPay($p, $a), $case.' cannot obtain a provider redirect');
}
[$a, $o, $p] = $seed('awaiting_payment');
$before = [$invoicePosts, $quotePosts];
check($gateway->checkout($trade)->getTargetUrl() === 'https://app.jangopay.org/pay/I1'
    && $gateway->checkout($trade)->getTargetUrl() === 'https://app.jangopay.org/pay/I1'
    && [$invoicePosts, $quotePosts] === $before, 'reopening hosted entry neither creates invoice nor selects a coin');

$mode = 'quote-missing-address';
[$a, $o, $p] = $seed('quote_unknown');
$a->update(['txn_id' => 'T1', 'quote_amount' => null, 'receive_address' => null]);
$before = $quotePosts;
check($recovery->check($a->id) === 'quote_instructions_unrecoverable' && $a->fresh()->state === 'quote_unknown', 'real-shaped pending detail cannot reconstruct receiving address');
$mode = 'healthy';
$credits = Capsule::table('user_credit_log')->count();
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $credits + 1,
    'missing quote instructions do not block later authenticated settlement');
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $credits + 1
    && $quotePosts === $before, 'settlement replay neither duplicates credit nor resends coin POST');

$settings['standard_currency'] = 'CNY';
$app['config']->set('common.currency', [['code' => 'USD', 'symbol' => '$'], ['code' => 'CNY', 'symbol' => 'CNY']]);
$app['config']->set('jangopay.fx_reference_usd_per_cny', '0.14');
$app['config']->set('jangopay.fx_reference_verified_at', gmdate('Y-m-d\TH:i:s\Z'));
$mode = 'cny-snapshot';
check($buy()->getData(true)['status'] === 'success', 'CNY purchase accepts approved independent reference');
$priced = JangoPayAttempt::where('invoice_id', 'I_CNY')->firstOrFail();
check($priced->fx_reference['usd_per_cny'] === '0.14' && $priced->amount_usd === '0.42', 'invoice persists reference and locked conversion');
$app['config']->set('jangopay.fx_reference_usd_per_cny', '0.15');
check($priced->fresh()->fx_reference['usd_per_cny'] === '0.14' && $priced->fresh()->amount_usd === '0.42', 'new operator approval cannot reprice existing invoice');
$settings['standard_currency'] = 'USD';
$mode = 'healthy';

[$a, $o, $p] = $seed();
$mode = 'wrong-amount';
$traceFailure = true;
PaymentCallback::updating(function () use (&$traceFailure) {
    if ($traceFailure) {
        throw new RuntimeException('Synthetic callback audit write failure');
    }
});
$request = callbackRequest();
$request->headers->set('X-JangoPay-Event', 'SYNTHETIC_EVENT');
check($gateway->notify($request)->getStatusCode() === 503, 'audit failure inside catch remains a retryable response instead of throwing');
$traceFailure = false;
check($gateway->notify($request)->getStatusCode() === 503
    && json_decode(PaymentCallback::find($request->attributes->get('payment_callback_id'))->payload, true)['event_type'] === 'SYNTHETIC_EVENT',
    'callback audit records observed event header rather than inventing a completed event');
$mode = 'healthy';
