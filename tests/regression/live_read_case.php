<?php

// Explicit opt-in: real historical merchant GETs, but only an isolated in-memory customer/database.
use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Services\JangoPay\Api;
use App\Services\JangoPay\Money;
use App\Services\JangoPay\Reconciliation;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Carbon;

$apiKey = getenv('JANGOPAY_LIVE_KEY');
$invoiceId = getenv('JANGOPAY_LIVE_INVOICE');
$txnId = getenv('JANGOPAY_LIVE_TRANSACTION');
check(is_string($apiKey) && str_starts_with($apiKey, 'JangoPay_') && is_string($invoiceId) && is_string($txnId), 'explicit historical merchant inputs');
$calls = [];
$http->globalRequestMiddleware(function ($request) use (&$calls) {
    $url = $request->getUri();
    $path = $url->getPath();
    $method = $request->getMethod();
    if ($url->getScheme() !== 'https' || $url->getHost() !== 'api.jangopay.org'
        || ! ($method === 'GET' || ($method === 'POST' && $path === '/api/v1/auth/apikey/token'))) {
        throw new RuntimeException('Business writes forbidden in historical read regression');
    }
    $calls[] = ['method' => $method, 'path' => $path];

    return $request;
});
$app['config']->set('jangopay.api_key', $apiKey);
$api = new Api($apiKey);
$app->instance(Api::class, $api);
$invoice = $api->invoice($invoiceId);
$txn = $api->transaction($txnId);
$orderId = $invoice['orderId'];
$amount = $invoice['amount'];
check($invoice['invoiceId'] === $invoiceId && $txn['invoiceId'] === $invoiceId && $txn['txnId'] === $txnId, 'historical invoice/transaction binding');
$o = Order::create(['sn' => 'HISTORICAL_READ_FIXTURE', 'user_id' => 1, 'origin_amount' => (float) $amount,
    'amount' => (float) $amount, 'pay_type' => 4, 'pay_way' => 'jangopay']);
$p = Payment::create(['user_id' => 1, 'order_id' => $o->id, 'trade_no' => $orderId, 'amount' => (float) $amount]);
$pair = $txn['payCurrency'].':'.$txn['chain'];
$recoveryMode = getenv('JANGOPAY_LIVE_RECOVERY_MODE') ?: 'fulfilling';
check(in_array($recoveryMode, ['fulfilling', 'unknown_creation', 'quote_unknown'], true), 'explicit recovery scenario');
$a = JangoPayAttempt::create(['payment_id' => $p->id, 'order_id' => $orderId,
    'invoice_id' => $recoveryMode === 'unknown_creation' ? null : $invoiceId,
    'txn_id' => $recoveryMode === 'fulfilling' ? $txnId : null,
    'base_currency' => 'USD', 'base_amount' => Money::fromMajor((float) $amount), 'amount_usd' => Money::fromMajor((float) $amount),
    'fx_rate' => '1', 'allowed_assets' => $pair, 'selected_asset' => $pair, 'quote_amount' => $txn['payAmount'],
    'state' => $recoveryMode,
    'created_at' => Carbon::parse($invoice['createdAt'])->setTimezone(date_default_timezone_get()),
    'expires_at' => Carbon::parse($invoice['expiresAt'])->setTimezone(date_default_timezone_get())]);
$recovery = new Reconciliation;
check($recovery->check($a->id) === 'delivered', 'current recovery accepts authenticated actual net-of-fee record');
check($o->fresh()->status === 2 && $p->fresh()->status === 1 && Capsule::table('user_credit_log')->count() === 1, 'isolated native order delivered exactly once');
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === 1, 'historical record replay cannot duplicate native credit');
echo json_encode(['source' => 'real authenticated historical merchant reads', 'assertions' => $checks,
    'recovery_mode' => $recoveryMode,
    'merchant_business_writes' => 0, 'local_database' => 'isolated in-memory SQLite', 'new_payment' => false,
    'invoice_status' => $invoice['status'], 'transaction_status' => $txn['status'],
    'gross_usd' => $txn['amount'], 'fee_usd' => $txn['feeAmount'], 'net_usd' => $txn['netAmount'],
    'invoice_payed_usd' => $invoice['payedAmount'], 'native_credit_entries' => Capsule::table('user_credit_log')->count(),
    'requests' => $calls], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
