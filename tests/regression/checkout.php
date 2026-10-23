<?php

use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Utils\Payments\JangoPay;
use Illuminate\Auth\Access\Gate;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

require __DIR__.'/bootstrap.php';
$app = new Application(sys_get_temp_dir());
$app->instance('config', new Repository(['app' => ['url' => 'https://proxy.test'],
    'common' => ['currency' => [['code' => 'USD', 'symbol' => '$']]],
]));
Facade::setFacadeApplication($app);
$db = new Manager($app);
$db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
$db->setAsGlobal();
$db->bootEloquent();
$files = new Filesystem;
$temp = sys_get_temp_dir().'/jangopay-view-'.bin2hex(random_bytes(8));
$files->ensureDirectoryExists($temp.'/user');
$files->ensureDirectoryExists($temp.'/cache');
$files->put($temp.'/user/layouts.blade.php', "@yield('content') @yield('javascript')");
$files->ensureDirectoryExists($temp.'/admin');
$files->put($temp.'/admin/layouts.blade.php', "@yield('content')");
$engines = new EngineResolver;
$engines->register('blade', fn () => new CompilerEngine(
    new BladeCompiler($files, $temp.'/cache'), $files));
$view = new Factory($engines, new FileViewFinder($files, [$temp, $source.'/resources/views']), new Dispatcher($app));
$view->setContainer($app);
$view->share('__env', $view);
$app->instance('view', $view);
$app->instance('translator', new Translator(new ArrayLoader, 'en'));
$router = new Router(new Dispatcher($app), $app);
$router->get('status', fn () => null)->name('orderStatus');
$router->get('invoices', fn () => null)->name('invoice.index');
$router->get('admin/payments/jangopay', fn () => null)->name('admin.jangopay.index');
$router->post('admin/payments/jangopay/{id}/check', fn () => null)->name('admin.jangopay.check');
$router->post('admin/payments/jangopay/{id}/resolve', fn () => null)->name('admin.jangopay.resolve');
$router->getRoutes()->refreshNameLookups();
$app->instance('url', new UrlGenerator($router->getRoutes(), Request::create('https://proxy.test')));
$session = new Store('regression', new ArraySessionHandler(120));
$session->put('currency', 'USD');
$session->put('_token', 'SYNTHETIC');
$app->instance('session', $session);
Carbon::setTestNow('2026-10-22 12:20:00');
$cases = [
    'active' => [0, 0, 'awaiting_payment', 10, true], 'expired' => [0, 0, 'awaiting_payment', -1, false],
    'boundary' => [0, 0, 'awaiting_payment', 0, false], 'closed_payment' => [-1, 0, 'awaiting_payment', 10, false],
    'closed_order' => [0, -1, 'awaiting_payment', 10, false], 'paid' => [1, 2, 'awaiting_payment', 10, false],
    'delivered' => [1, 2, 'delivered', 10, false], 'unknown' => [0, 0, 'quote_unknown', 10, false],
    'legacy_quote' => [0, 0, 'quoted', 10, false],
];
foreach ($cases as $name => [$paymentStatus, $orderStatus, $state, $minutes, $expected]) {
    $payment = new Payment(['id' => 9, 'order_id' => 8, 'user_id' => 1, 'status' => $paymentStatus, 'trade_no' => 'SYNTHETIC', 'amount' => 3]);
    $payment->setRelation('order', new Order(['id' => 8, 'user_id' => 1, 'pay_way' => 'jangopay', 'amount' => 3, 'status' => $orderStatus]));
    $attempt = new JangoPayAttempt(['payment_id' => 9, 'order_id' => 'SYNTHETIC', 'invoice_id' => 'I1', 'base_amount' => '3.00', 'amount_usd' => '3.00', 'base_currency' => 'USD', 'state' => $state,
        'selected_asset' => 'USDT:bsc', 'quote_amount' => '3', 'receive_address' => 'SYNTHETIC_ADDRESS', 'expires_at' => now()->addMinutes($minutes)]);
    check(JangoPay::canPay($payment, $attempt) === $expected, 'guarded hosted eligibility '.$name);
    $html = $view->make('user.components.payment.jangopay', compact('payment', 'attempt'))->render();
    check(! str_contains($html, 'SYNTHETIC_ADDRESS') && ! str_contains($html, '<form') && ! str_contains($html, '<script'), 'local status has no payer controls or scripts '.$name);
}
$gate = new Gate($app, fn () => new stdClass);
$gate->before(fn () => true);
$app->instance(Illuminate\Contracts\Auth\Access\Gate::class, $gate);
$app->instance('request', Request::create('https://proxy.test/admin/payments/jangopay'));
$view->addNamespace('pagination', dirname((new ReflectionClass(Paginator::class))->getFileName()).'/resources/views');
Paginator::viewFactoryResolver(fn () => $view);
$attempt = new JangoPayAttempt(['id' => 9, 'order_id' => 'TEST_ORDER', 'invoice_id' => 'I1', 'txn_id' => 'T1',
    'state' => 'manual_review', 'amount_usd' => '3.00', 'review_note' => '<script>alert(1)</script>']);
$list = new LengthAwarePaginator([$attempt], 1, 20);
$html = $view->make('admin.jangopay.index', ['attempts' => $list, 'actions' => []])->render();
check(str_contains($html, 'name="invoice_id"') && str_contains($html, 'name="txn_id"'), 'merchant IDs can be supplied for authenticated recheck');
check(str_contains($html, 'name="confirm"') && str_contains($html, 'name="note"'), 'resolution requires confirmation and note');
check(str_contains($html, '&lt;script&gt;') && ! str_contains($html, '<script>alert'), 'operator notes escaped');
// Use a new gate so the earlier allow callback cannot short-circuit denial.
$denied = new Gate($app, fn () => new stdClass);
$denied->before(fn () => false);
$app->instance(Illuminate\Contracts\Auth\Access\Gate::class, $denied);
Illuminate\Support\Facades\Gate::clearResolvedInstance(Illuminate\Contracts\Auth\Access\Gate::class);
$html = $view->make('admin.jangopay.index', ['attempts' => $list, 'actions' => []])->render();
check(! str_contains($html, 'name="note"') && ! str_contains($html, 'name="invoice_id"'), 'unauthorized action forms absent');
Carbon::setTestNow();
$files->deleteDirectory($temp);
echo "PASS {$checks} checkout rendering checks (real Blade; minimal layout)\n";

function sysConfig($name, $default = null)
{
    return ['standard_currency' => 'USD'][$name] ?? $default;
}
