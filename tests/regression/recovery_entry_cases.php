<?php

use App\Models\PaymentCallback;
use App\Services\JangoPay\WebhookEnvelope;
use App\Utils\Payments\JangoPay;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

$postCounts = [$invoicePosts, $quotePosts];
$mode = 'quote-empty';
[$a, $o, $p] = $seed('unknown_creation');
check($p->url === null, 'ambiguous invoice starts without payment URL');
check($recovery->check($a->id) === 'awaiting_settlement'
    && $a->fresh()->state === 'awaiting_payment' && $a->fresh()->invoice_id === 'I1', 'authenticated pending invoice restores its mapping');
$url = route('jangopay.checkout', ['trade_no' => $p->trade_no]);
check($p->fresh()->url === $url && JangoPay::canPay($p->fresh(), $a->fresh()), 'recovery persists canonical local checkout URL while payable');
$p->update(['url' => null]);
$recovery->check($a->id);
check($p->fresh()->url === $url, 'already bound legacy attempt heals missing payment URL');

// Render the unmodified upstream payment-actions fragment and the actual authenticated cashier.
$router->get('invoices', fn () => null)->name('invoice.index');
$router->get('status', fn () => null)->name('orderStatus');
$router->getRoutes()->refreshNameLookups();
$files = new Filesystem;
$temp = sys_get_temp_dir().'/jangopay-recovery-entry-'.bin2hex(random_bytes(8));
$files->ensureDirectoryExists($temp.'/user');
$files->ensureDirectoryExists($temp.'/cache');
$files->put($temp.'/user/layouts.blade.php', "@yield('content') @yield('javascript')");
$engine = new EngineResolver;
$engine->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $temp.'/cache'), $files));
$views = new Factory($engine, new FileViewFinder($files, [$temp, $source.'/resources/views']), $app['events']);
$views->setContainer($app);
$views->share('__env', $views);
$app->instance('view', $views);
$template = file_get_contents($upstream.'/resources/views/user/invoices.blade.php');
$start = strpos($template, '<x-admin.row-actions>') + strlen('<x-admin.row-actions>');
$fragment = substr($template, $start, strpos($template, '</x-admin.row-actions>', $start) - $start);
$files->put($temp.'/payment-actions.blade.php', $fragment);
try {
    $html = $views->make('payment-actions', ['order' => $o->fresh()])->render();
    check(str_contains($html, 'href="'.$url.'"'), 'native invoice payment action renders recovered checkout entry');
    check($gateway->checkout($p->trade_no)->getTargetUrl() === 'https://app.jangopay.org/pay/I1', 'owner follows recovered entry to provider hosted checkout');
    $p->update(['user_id' => 2]);
    try {
        $gateway->checkout($p->trade_no);
        check(false, 'another user must not open recovered cashier');
    } catch (ModelNotFoundException) {
        check(true, 'recovered cashier retains authenticated ownership check');
    }
    $p->update(['user_id' => 1]);
} finally {
    $files->deleteDirectory($temp);
}
foreach (['closed', 'expired', 'paid', 'order_amount', 'payment_amount', 'gateway', 'owner'] as $case) {
    [$a, $o, $p] = $seed('unknown_creation');
    if ($case === 'closed') {
        $o->close();
    } elseif ($case === 'expired') {
        $a->update(['expires_at' => now()->subSecond()]);
    } elseif ($case === 'paid') {
        $o->updateQuietly(['status' => 2]);
        $p->update(['status' => 1]);
    } elseif ($case === 'order_amount') {
        $o->updateQuietly(['amount' => 4]);
    } elseif ($case === 'payment_amount') {
        $p->update(['amount' => 4]);
    } elseif ($case === 'gateway') {
        $o->updateQuietly(['pay_way' => 'other']);
    } else {
        $p->update(['user_id' => 2]);
    }
    $recovery->check($a->id);
    check($p->fresh()->url === null, $case.' cannot gain a recovered payment entry');
}
$mode = 'wrong-amount';
[$a, $o, $p] = $seed('unknown_creation');
check($recovery->check($a->id) === 'invoice_identity_mismatch'
    && $p->fresh()->url === null && $a->fresh()->invoice_id === null, 'unverified invoice cannot gain mapping or entry');

$mode = 'healthy';
[$a, $o, $p] = $seed();
check($recovery->check($a->id) === 'delivered', 'duplicate-hash fixture delivers original payment');
$credits = Capsule::table('user_credit_log')->count();
$emissions = count($GLOBALS['jangopay_test_emissions']);
$app->instance(WebhookEnvelope::class, new class extends WebhookEnvelope
{
    public $hash = '0xDIFFERENT_HASH';

    public function decrypt(Request $request): array
    {
        global $trade;

        return ['event_type' => 'payment.completed', 'invoiceId' => 'I1', 'orderId' => $trade, 'txnId' => 'T1', 'txHash' => $this->hash];
    }
});
$request = callbackRequest();
$result = $gateway->notify($request);
$log = PaymentCallback::find($request->attributes->get('payment_callback_id'));
check($result->getStatusCode() === 503 && $result->getData(true)['status'] === 'delivered_event_hash_mismatch', 'different duplicate hash is rejected');
check($log->status !== PaymentCallback::STATUS_FULFILLED
    && json_decode($log->payload, true)['result'] === 'delivered_event_hash_mismatch', 'mismatched duplicate audit is not marked fulfilled');
check($a->fresh()->state === 'delivered' && $a->fresh()->tx_hash === '0xSYNTHETIC_HASH'
    && Capsule::table('user_credit_log')->count() === $credits
    && count($GLOBALS['jangopay_test_emissions']) === $emissions, 'rejected replay preserves proof, delivery, credit and notifications');
$app->make(WebhookEnvelope::class)->hash = '0xSYNTHETIC_HASH';
check($gateway->notify(callbackRequest())->getStatusCode() === 200, 'matching delivered hash is acknowledged');
foreach (['', false, str_repeat('a', 161)] as $invalidHash) {
    $app->make(WebhookEnvelope::class)->hash = $invalidHash;
    $request = callbackRequest();
    check($gateway->notify($request)->getStatusCode() === 422
        && PaymentCallback::find($request->attributes->get('payment_callback_id'))->status !== PaymentCallback::STATUS_FULFILLED,
        'malformed supplied hash is rejected instead of silently treated as absent');
}
check($recovery->check($a->id, 'WRONG', '0xSYNTHETIC_HASH', 'I1') === 'delivered_event_identity_mismatch'
    && $recovery->check($a->id, 'T1', '0xSYNTHETIC_HASH', 'WRONG') === 'delivered_event_identity_mismatch', 'direct delivered checks compare provided invoice and transaction');
$p->delete();
$app->make(WebhookEnvelope::class)->hash = '0xDIFFERENT_HASH';
check($gateway->notify(callbackRequest())->getStatusCode() === 503, 'retained delivery snapshot rejects wrong hash after native payment cleanup');
$app->make(WebhookEnvelope::class)->hash = '0xSYNTHETIC_HASH';
check($gateway->notify(callbackRequest())->getStatusCode() === 200, 'retained delivery snapshot acknowledges matching hash without repeat credit');
$app->instance(WebhookEnvelope::class, $envelope);
check([$invoicePosts, $quotePosts] === $postCounts, 'recovery entry and delivered replay checks perform no business POST');
