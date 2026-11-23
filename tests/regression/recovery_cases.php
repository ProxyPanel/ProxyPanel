<?php

// Included only by native_flow.php; these cases share its actual upstream lifecycle and isolated DB.
use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\UserCreditLog;
use App\Services\JangoPay\Reconciliation;
use App\Services\JangoPay\WebhookEnvelope;
use App\Utils\Library\PaymentHelper;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;

$recovery = new Reconciliation;
$postCounts = [$invoicePosts, $quotePosts];
$seed = function (string $state = 'quoted') use (&$trade) {
    JangoPayAttempt::where('invoice_id', 'I1')->delete();
    $o = Order::create(['sn' => uniqid('RECOVERY'), 'user_id' => 1, 'origin_amount' => 3, 'amount' => 3, 'pay_type' => 4, 'pay_way' => 'jangopay']);
    $p = PaymentHelper::createPayment(1, $o->id, 3);
    $trade = $p->trade_no;
    $a = JangoPayAttempt::create(['payment_id' => $p->id, 'order_id' => $trade, 'invoice_id' => $state === 'unknown_creation' ? null : 'I1',
        'txn_id' => in_array($state, ['quote_unknown', 'unknown_creation'], true) ? null : 'T1',
        'base_currency' => 'USD', 'base_amount' => '3.00', 'amount_usd' => '3.00', 'fx_rate' => '1', 'allowed_assets' => 'USDT:bsc',
        'selected_asset' => 'USDT:bsc', 'quote_amount' => '3', 'receive_address' => 'SYNTHETIC_ADDRESS',
        'state' => $state, 'expires_at' => now()->addMinutes(10)]);

    return [$a, $o, $p];
};
$mode = 'healthy';
[$a,$o,$p] = $seed();
$o->close();
$before = Capsule::table('user_credit_log')->count();
check($recovery->check($a->id) === 'manual_review', 'closed order enters review');
check($o->fresh()->status === -1 && $p->fresh()->status === -1 && Capsule::table('user_credit_log')->count() === $before, 'closed order never reactivated or credited');
check($a->fresh()->remote_invoice_status === 'paid' && $a->fresh()->tx_hash === '0xSYNTHETIC_HASH', 'late/closed merchant evidence queryable');
$r = callbackRequest();
check($gateway->notify($r)->getStatusCode() === 200 && $r->attributes->get('payment_callback_id')
    && Capsule::table('user_credit_log')->count() === $before, 'verified closed callback accepted for review without fulfillment');
[$a,$o,$p] = $seed();
$a->update(['created_at' => now()->subMinutes(20), 'expires_at' => now()->subMinutes(5)]);
check($recovery->check($a->id) === 'manual_review' && $o->fresh()->status === 0, 'actual late payment held');
[$a,$o,$p] = $seed();
$a->update(['created_at' => now()->subMinutes(20), 'expires_at' => now()->subMinutes(5)]);
$paidAt = now()->subMinutes(6)->toIso8601String();
check($recovery->check($a->id) === 'delivered', 'timely payment with delayed callback recovered using merchant paidAt');
$paidAt = null;
foreach (['unknown_creation', 'quote_unknown', 'fulfilling'] as $state) {
    [$a,$o,$p] = $seed($state);
    $before = Capsule::table('user_credit_log')->count();
    check($recovery->check($a->id) === 'delivered', $state.' recovered from merchant GET');
    check(Capsule::table('user_credit_log')->count() === $before + 1, $state.' credits once');
    check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $before + 1, $state.' repeated recovery is harmless');
}
[$a,$o,$p] = $seed('fulfilling');
PaymentHelper::paymentReceived($trade);
$before = Capsule::table('user_credit_log')->count();
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $before, 'old interrupted marker repaired without second native fulfillment');
[$a,$o,$p] = $seed();
$crashOnce = true;
UserCreditLog::created(function () use (&$crashOnce) {
    if ($crashOnce) {
        $crashOnce = false;
        throw new RuntimeException('Synthetic interruption during native delivery');
    }
});
$before = Capsule::table('user_credit_log')->count();
check($recovery->check($a->id) === 'reconciliation_unavailable', 'injected delivery interruption reported');
check($o->fresh()->status === 0 && $p->fresh()->status === 0 && $a->fresh()->state === 'quoted'
    && Capsule::table('user_credit_log')->count() === $before, 'delivery and marker roll back together');
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $before + 1, 'interrupted transaction safely retried once');
[$a,$o,$p] = $seed('unknown_creation');
$mode = 'bad-pagination';
check($recovery->check($a->id) === 'reconciliation_unavailable' && $o->fresh()->status === 0, 'incomplete merchant scan cannot deliver');
$mode = 'healthy';
check([$invoicePosts, $quotePosts] === $postCounts, 'all recovery paths issue no create/confirm POSTs');
[$a,$o,$p] = $seed();
$app->instance(WebhookEnvelope::class, new WebhookEnvelope);
$bad = Request::create('/notify', 'POST', [], [], [], [
    'CONTENT_TYPE' => 'application/json', 'HTTP_X_JANGOPAY_EVENT' => 'payment.completed',
    'HTTP_X_JANGOPAY_ENCRYPTION_ALG' => 'AES-256-GCM', 'HTTP_X_JANGOPAY_KEY_ENCRYPTION' => 'RSA-OAEP-SHA256',
], json_encode(['event_type' => 'payment.completed', 'algorithm' => 'AES-256-GCM', 'key_algorithm' => 'RSA-OAEP-SHA256',
    'encrypted_key' => base64_encode('wrong_RSA_key'), 'nonce' => base64_encode(random_bytes(12)),
    'ciphertext' => base64_encode(random_bytes(48))]));
check($gateway->notify($bad)->getStatusCode() === 503 && $a->fresh()->state === 'quoted', 'actual envelope decryption failure does not mutate payment state');
$before = Capsule::table('user_credit_log')->count();
check($recovery->check($a->id) === 'delivered' && Capsule::table('user_credit_log')->count() === $before + 1, 'authenticated reconciliation delivers even when encrypted notification is unusable');
$app->instance(WebhookEnvelope::class, $envelope);
