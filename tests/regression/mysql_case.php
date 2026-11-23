<?php

use App\Console\Commands\TaskAuto;
use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Services\JangoPay\Reconciliation;
use App\Utils\Library\PaymentHelper;
use Illuminate\Database\Capsule\Manager as Capsule;

if ($mysqlCase === 'seed') {
    JangoPayAttempt::where('invoice_id', 'I1')->delete();
    $o = Order::create(['sn' => uniqid('PARALLEL'), 'user_id' => 1, 'origin_amount' => 3, 'amount' => 3, 'pay_type' => 4, 'pay_way' => 'jangopay']);
    $p = PaymentHelper::createPayment(1, $o->id, 3);
    $state = getenv('JANGOPAY_MYSQL_STATE') ?: 'quoted';
    $a = JangoPayAttempt::create(['payment_id' => $p->id, 'order_id' => $p->trade_no, 'invoice_id' => 'I1', 'txn_id' => 'T1',
        'base_currency' => 'USD', 'base_amount' => '3.00', 'amount_usd' => '3.00', 'fx_rate' => '1', 'allowed_assets' => 'USDT:bsc',
        'selected_asset' => 'USDT:bsc', 'quote_amount' => '3', 'receive_address' => 'SYNTHETIC_ADDRESS',
        'state' => in_array($state, ['closed', 'grace'], true) ? 'quoted' : $state, 'expires_at' => now()->addMinutes(10)]);
    if ($state === 'grace') {
        $o->updateQuietly(['created_at' => now()->subMinutes(16)]);
        $a->update(['created_at' => now()->subMinutes(16), 'expires_at' => now()->subMinute()]);
    }
    if ($state === 'closed') {
        $o->close();
    }
    if ($state === 'checkout') {
        $a->update(['state' => 'awaiting_payment', 'txn_id' => null, 'selected_asset' => null, 'quote_amount' => null, 'receive_address' => null]);
    }
    echo json_encode(['attempt' => $a->id, 'credit_entries_before' => Capsule::table('user_credit_log')->count(), 'expected_credit_increment' => in_array($state, ['closed', 'checkout'], true) ? 0 : 1]);
} elseif ($mysqlCase === 'inspect') {
    $a = JangoPayAttempt::findOrFail((int) getenv('JANGOPAY_MYSQL_ATTEMPT'));
    echo json_encode(['state' => $a->state, 'credit_entries' => Capsule::table('user_credit_log')->count(),
        'payment_status' => Payment::find($a->payment_id)->status, 'order_status' => Payment::find($a->payment_id)->order->status]);
} else {
    $a = JangoPayAttempt::findOrFail((int) getenv('JANGOPAY_MYSQL_ATTEMPT'));
    $trade = $a->order_id;
    $mode = 'healthy';
    if ($a->expires_at->isPast()) {
        $paidAt = now()->subMinutes(2)->toIso8601String();
    }
    $barrier = getenv('JANGOPAY_MYSQL_BARRIER');
    file_put_contents($barrier.'.'.$mysqlCase.'.ready', 'ready');
    $deadline = microtime(true) + 20;
    while (! is_file($barrier.'.go')) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('Worker barrier timeout');
        } usleep(10000);
    }
    if ($mysqlCase === 'auto_close') {
        $settings['tasks_close.orders'] = '-15 minutes';
        $settings['tasks_close.confirmation_orders'] = '-12 hours';
        (new ReflectionMethod(TaskAuto::class, 'orderTimer'))
            ->invoke((new ReflectionClass(TaskAuto::class))->newInstanceWithoutConstructor());
        $result = 'close_task_finished';
    } elseif (str_starts_with($mysqlCase, 'checkout_')) {
        $result = $gateway->checkout($trade)->getTargetUrl();
        check($result === 'https://app.jangopay.org/pay/I1', 'concurrent hosted redirect is exact');
    } else {
        $result = $mysqlCase === 'callback' ? $gateway->notify(callbackRequest())->getStatusCode() : (new Reconciliation)->check($a->id);
    }
    echo json_encode(['worker' => $mysqlCase, 'result' => $result, 'quote_posts' => $quotePosts]);
}
