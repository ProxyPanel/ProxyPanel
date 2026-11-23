<?php

use App\Console\Commands\TaskAuto;
use App\Models\JangoPayAttempt;
use App\Models\PaymentCallback;
use App\Utils\Payments\JangoPay;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$mode = 'healthy';
[$a, $o, $p] = $seed();
$o->updateQuietly(['created_at' => now()->subMinutes(16)]);
$a->update(['created_at' => now()->subMinutes(16), 'expires_at' => now()->subMinute()]);
$settings['tasks_close.orders'] = '-15 minutes';
$settings['tasks_close.confirmation_orders'] = '-12 hours';
$timer = new ReflectionMethod(TaskAuto::class, 'orderTimer');
$timer->invoke((new ReflectionClass(TaskAuto::class))->newInstanceWithoutConstructor());
check($o->fresh()->status === 0 && $p->fresh()->status === 0, 'actual automatic close task respects confirmation grace');
$paidAt = now()->subMinutes(2)->toIso8601String();
check($recovery->check($a->id) === 'delivered', 'timely payment confirmed during grace delivered');
check(! $o->close(true) && $o->fresh()->status === 2, 'stale automatic close snapshot cannot cancel a paid order');
$paidAt = null;
[$a, $o, $p] = $seed();
$a->update(['expires_at' => now()->subMinutes(JangoPayAttempt::CONFIRMATION_GRACE_MINUTES)]);
check($o->close(true) && $o->fresh()->status === -1, 'automatic close allowed at grace boundary');
[$a, $o, $p] = $seed();
check($o->close() && $o->fresh()->status === -1, 'explicit operator or user close is still available during grace');
[$a, $o, $p] = $seed();
$o->updateQuietly(['pay_way' => 'other']);
check($o->close(true) && $p->fresh()->status === -1, 'other gateway automatic cancellation unchanged');

$postCounts = [$invoicePosts, $quotePosts];
foreach (['quote-pending', 'quote-empty', 'quote-multiple', 'quote-missing-address', 'quote-mismatch'] as $mode) {
    [$a, $o, $p] = $seed('quote_unknown');
    $a->update(['quote_amount' => null, 'receive_address' => null]);
    $result = $recovery->check($a->id);
    check($a->fresh()->state === 'quote_unknown' && $a->fresh()->failure_code === 'quote_instructions_unrecoverable'
        && ! JangoPay::canPay($p->fresh(), $a->fresh()), $mode.' stays available for settlement checks but cannot restore unsupported instructions');
}
$mode = 'quote-pending';
[$a, $o, $p] = $seed('quote_unknown');
$a->update(['expires_at' => now()->subSecond()]);
check($recovery->check($a->id) === 'quote_instructions_unrecoverable' && $a->fresh()->state === 'quote_unknown', 'expired pending quote not revived');
[$a, $o, $p] = $seed('quote_unknown');
$o->close();
check($recovery->check($a->id) === 'quote_instructions_unrecoverable' && $o->fresh()->status === -1, 'closed pending quote not revived');
check([$invoicePosts, $quotePosts] === $postCounts, 'pending quote recovery never repeats business POST');

$mode = 'healthy';
[$a, $o, $p] = $seed();
$GLOBALS['jangopay_test_emissions'] = [];
DB::beginTransaction();
check($recovery->check($a->id) === 'delivered' && $GLOBALS['jangopay_test_emissions'] === [], 'nested successful fulfillment sends nothing before outer commit');
DB::commit();
check($GLOBALS['jangopay_test_emissions'] === [['notification', 0], ['broadcast', 0]], 'notification and broadcast emitted once after outer commit '.json_encode($GLOBALS['jangopay_test_emissions']));
check($recovery->check($a->id) === 'delivered' && count($GLOBALS['jangopay_test_emissions']) === 2, 'recovery replay does not resend messages');
[$a, $o, $p] = $seed();
$GLOBALS['jangopay_test_emissions'] = [];
$credits = Capsule::table('user_credit_log')->count();
DB::beginTransaction();
check($recovery->check($a->id) === 'delivered', 'nested fulfillment can reach durable marker before caller rollback');
DB::rollBack();
check($GLOBALS['jangopay_test_emissions'] === [] && $o->fresh()->status === 0 && $a->fresh()->state === 'quoted'
    && Capsule::table('user_credit_log')->count() === $credits, 'outer rollback discards both financial changes and queued success messages');
check($recovery->check($a->id) === 'delivered' && count($GLOBALS['jangopay_test_emissions']) === 2, 'retry after rollback delivers and notifies once');

[$a, $o, $p] = $seed();
$mode = 'wrong-amount';
$r = callbackRequest();
check($gateway->notify($r)->getStatusCode() === 503, 'mismatched amount remains retryable, not acknowledged as success');
$log = PaymentCallback::find($r->attributes->get('payment_callback_id'));
$audit = json_decode($log->payload, true);
check($log->trade_no === $trade && $log->status === 0 && $audit['invoice_id'] === 'I1' && $audit['txn_id'] === 'T1'
    && $audit['result'] === 'invoice_identity_mismatch' && strlen($audit['body_sha256']) === 64, 'failed callback preserves traceable redacted identifiers without fulfilled marker');
$mode = 'healthy';
$o->close();
$r = callbackRequest();
$response = $gateway->notify($r);
check($response->getStatusCode() === 200 && $response->getData(true)['status'] === 'accepted_for_review', 'verified persisted manual review stops callback retries');
check(PaymentCallback::find($r->attributes->get('payment_callback_id'))->status === 0 && $o->fresh()->status === -1, 'accepted review does not mark payment fulfilled or reopen closed order');

[$a, $o, $p] = $seed();
$a->update(['created_at' => Carbon::parse('2026-10-16T05:54:09.030216Z')->setTimezone(date_default_timezone_get()),
    'expires_at' => Carbon::parse('2026-10-16T06:09:09Z')->setTimezone(date_default_timezone_get())]);
$paidAt = '2026-10-16T06:01:32.150365Z';
$timeResult = $recovery->check($a->id);
check($timeResult === 'delivered', 'actual historical UTC fractional paidAt shape accepted '.json_encode([$timeResult, $a->fresh()->failure_code, $a->fresh()->created_at, $a->fresh()->expires_at]));
$paidAt = null;
