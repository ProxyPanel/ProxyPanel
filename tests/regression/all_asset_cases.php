<?php

use App\Models\JangoPayAttempt;
use Illuminate\Database\Capsule\Manager as Capsule;

$mode = 'healthy';
$invoiceStatus = 'paid';
$settings['standard_currency'] = 'USD';
$merchantAssets = [['code' => 'BTC', 'chain' => 'bitcoin', 'enabled' => true, 'invoicePaymentEnabled' => true]];
foreach (range(1, 23) as $n) {
    $merchantAssets[] = ['code' => 'ASSET'.$n, 'chain' => 'network'.$n, 'enabled' => true, 'invoicePaymentEnabled' => true];
}
$merchantAssets[] = ['code' => 'DISABLED', 'chain' => 'network', 'enabled' => false, 'invoicePaymentEnabled' => true];
$expectedPairs = array_map(fn ($row) => $row['code'].':'.$row['chain'], array_slice($merchantAssets, 0, 24));
foreach (['*', ''] as $setting) {
    $app['cache']->flush();
    JangoPayAttempt::where('invoice_id', 'I1')->delete();
    $settings['jangopay_assets'] = $setting;
    $beforePosts = $invoicePosts;
    $created = $buy()->getData(true);
    check($created['status'] === 'success', 'all-assets setting creates a hosted invoice: '.json_encode($created));
    $allAttempt = JangoPayAttempt::where('invoice_id', 'I1')->firstOrFail();
    check(explode(',', $allAttempt->allowed_assets) === $expectedPairs && ! str_contains($allAttempt->allowed_assets, '*'), 'merchant-enabled assets are frozen as explicit invoice evidence');
    check($invoicePosts === $beforePosts + 1 && $quotePosts === 0, 'all-assets purchase creates exactly one invoice and no coin POST');
}
$settlementPair = ['BTC', 'bitcoin'];
$settlementAmount = '0.00003000';
$settings['jangopay_assets'] = 'USDT:bsc';
$credits = Capsule::table('user_credit_log')->count();
check($gateway->notify(callbackRequest())->getStatusCode() === 200 && $allAttempt->fresh()->state === 'delivered', 'nonstablecoin payment uses saved USD invoice and asset snapshot after settings change');
check($allAttempt->fresh()->selected_asset === 'BTC:bitcoin' && $allAttempt->fresh()->quote_amount === $settlementAmount
    && Capsule::table('user_credit_log')->count() === $credits + 1, 'exact token proof persists with one native credit');
check($gateway->notify(callbackRequest())->getStatusCode() === 200 && Capsule::table('user_credit_log')->count() === $credits + 1, 'all-assets settlement retry cannot duplicate credit');
