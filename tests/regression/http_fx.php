<?php

use App\Services\JangoPay\Api;
use App\Services\JangoPay\Assets;
use App\Services\JangoPay\Fx;
use App\Services\JangoPay\Money;
use Illuminate\Cache\ArrayStore;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Facade;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

require __DIR__.'/bootstrap.php';
$app = new Application(sys_get_temp_dir());
$app->instance('config', new Repository(['jangopay' => ['api_key' => 'JangoPay_synthetic_only',
    'fx_reference_usd_per_cny' => '0.14', 'fx_reference_verified_at' => gmdate('Y-m-d\TH:i:s\Z')]]));
Facade::setFacadeApplication($app);
$http = new Factory;
$app->instance(Factory::class, $http);
$app->instance('cache', new Illuminate\Cache\Repository(new ArrayStore));
$app->instance('log', new Logger('regression', [new NullHandler]));
$http->preventStrayRequests();
$requests = [];
$fxRate = 0.14;
$fxTimestamp = time();
$fxUnavailable = false;
$http->fake(function ($request) use ($http, &$requests, &$fxRate, &$fxTimestamp, &$fxUnavailable) {
    $path = parse_url($request->url(), PHP_URL_PATH);
    $requests[] = $request;
    if ($path === '/v6/latest/CNY' && $fxUnavailable) {
        return $http->response(['result' => 'error'], 503);
    }

    return $http->response(match ($path) {
        '/api/v1/auth/apikey/token' => ['data' => ['accessToken' => 'SYNTHETIC']],
        '/api/v1/invoice/payment-assets' => ['items' => [['code' => 'USDT', 'chain' => 'bsc', 'enabled' => true, 'invoicePaymentEnabled' => true]]],
        '/api/v1/invoice/create' => ['invoice' => ['invoiceId' => 'I1', 'orderId' => 'ORDER1']],
        '/v6/latest/CNY' => ['result' => 'success', 'time_last_update_unix' => $fxTimestamp, 'rates' => ['USD' => $fxRate]],
        default => throw new RuntimeException('Unexpected mock endpoint'),
    });
});
$api = new Api;
check(count($api->assets()) === 1, 'merchant assets');
check($api->createInvoice('ORDER1', '3.00', ['USDT:bsc'])['invoiceId'] === 'I1', 'invoice');
check(count($requests) === 3 && $requests[0]->body() === '{}', 'one auth with object body');
check($requests[0]->hasHeader('Authorization', 'Basic JangoPay_synthetic_only'), 'auth credential stays on server');
check($requests[2]->hasHeader('Authorization', 'Bearer SYNTHETIC'), 'invoice uses bearer token');
check(! method_exists($api, 'session') && ! method_exists($api, 'confirmCoin'), 'merchant adapter has no local payer session or coin POST');
foreach ([1.005, 1.035, 9.995, 3.0, 100.999] as $base) {
    $stored = Money::fromMajor($base);
    $actual = Fx::quote($base, 'CNY');
    check($actual['usd'] === Money::fromMajor((float) $stored * 0.14), 'FX follows normalized base '.$base);
}
check(Fx::quote(1.035, 'CNY')['usd'] === '0.15', 'half-cent regression');
check(Fx::quote(1.035, 'USD')['usd'] === '1.04', 'USD normalizes without FX');
check(count(array_filter($requests, fn ($r) => str_contains($r->url(), '/v6/latest/CNY'))) === 1, 'CNY quote cache avoids per-order HTTP requests');
foreach ([0.014, 0.5, null, 0.07] as $fxRate) {
    $app['cache']->forget('jangopay.fx.cny.usd');
    $rejected = false;
    try {
        Fx::quote(100, 'CNY');
    } catch (RuntimeException) {
        $rejected = true;
    }
    check($rejected && $app['cache']->get('jangopay.fx.cny.usd') === null, 'out-of-bound or missing FX rate is not used or cached');
}
$fxRate = 0.14;
$fxTimestamp = time() - 172801;
$rejected = false;
try {
    Fx::quote(100, 'CNY');
} catch (RuntimeException) {
    $rejected = true;
}
check($rejected, 'stale upstream rate rejected');
$fxTimestamp = time();
$fxUnavailable = true;
$rejected = false;
try {
    Fx::quote(100, 'CNY');
} catch (RuntimeException) {
    $rejected = true;
}
check($rejected, 'FX outage does not fall back to an unverified or expired price');
$fxUnavailable = false;
check(Fx::quote(100, 'CNY')['usd'] === '14.00', 'FX service recovery restores valid quote');
$app['config']->set('jangopay.fx_min_usd_per_cny', 0.20);
$rejected = false;
try {
    Fx::quote(100, 'CNY');
} catch (RuntimeException) {
    $rejected = true;
}
check($rejected && $app['cache']->get('jangopay.fx.cny.usd') === null, 'updated operator bounds also validate cached rates');
$app['config']->set('jangopay.fx_min_usd_per_cny', 0.05);
foreach (['', gmdate('Y-m-d\TH:i:s\Z', time() - 86401), gmdate('Y-m-d\TH:i:s\Z', time() + 3600), '2026-02-30T00:00:00Z'] as $referenceTime) {
    $app['config']->set('jangopay.fx_reference_verified_at', $referenceTime);
    $before = count($requests);
    $rejected = false;
    try {
        Fx::quote(100, 'CNY');
    } catch (RuntimeException) {
        $rejected = true;
    }
    check($rejected && count($requests) === $before, 'missing, expired, future or invalid approval blocks CNY before HTTP');
}
$before = count($requests);
check(Fx::quote(100, 'USD')['usd'] === '100.00' && count($requests) === $before, 'USD needs neither reference approval nor FX HTTP');
$app['config']->set('jangopay.fx_reference_verified_at', gmdate('Y-m-d\TH:i:s\Z'));
$approved = Fx::quote(100, 'CNY');
$app['config']->set('jangopay.fx_reference_usd_per_cny', '0.15');
check($approved['reference']['usd_per_cny'] === '0.14' && $approved['usd'] === '14.00', 'approved quote snapshot is independent of subsequent configuration changes');
$app['config']->set('jangopay.fx_reference_usd_per_cny', '0.14');
$app['cache']->put('jangopay.fx.cny.usd', ['rates' => ['USD' => 0.07], 'time_last_update_unix' => time()], 300);
$rejected = false;
try {
    Fx::quote(100, 'CNY');
} catch (RuntimeException) {
    $rejected = true;
}
check($rejected && $app['cache']->get('jangopay.fx.cny.usd') === null, 'deviation protection also rejects and clears a bad cached rate');
$long = 'USDT:'.str_repeat('a', 32);
check(Assets::parse($long) === [$long] && strlen($long) === 37, 'maximum asset pair length');
echo "PASS {$checks} HTTP/FX/asset checks (synthetic HTTP only)\n";
