<?php

namespace {
    require __DIR__.'/bootstrap.php';
}

namespace App\Models {
    use App\Casts\money;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;

    // Only the user account boundary is substituted. All order, coupon, payment, observer,
    // service and ledger code below is the actual pinned upstream implementation.
    class User extends Model
    {
        protected $table = 'user';

        protected $guarded = [];

        protected $casts = ['credit' => money::class];

        public function orders()
        {
            return $this->hasMany(Order::class);
        }

        public function updateCredit(float $amount): bool
        {
            $changed = static::whereKey($this->id)->increment('credit', (int) round($amount * 100));
            $this->credit += $amount;

            return $changed > 0;
        }

        public function notify($notification): void
        {
            $GLOBALS['jangopay_test_emissions'][] = ['notification', DB::transactionLevel()];
        }
    }
}

namespace App\Utils\Payments {
    class PaymentManager
    {
        public static function discover(): array
        {
            return ['jangopay' => ['class' => JangoPay::class]];
        }
    }
}

namespace {
    use App\Http\Controllers\PaymentController;
    use App\Models\Coupon;
    use App\Models\JangoPayAttempt;
    use App\Models\Order;
    use App\Models\Payment;
    use App\Models\PaymentCallback;
    use App\Models\User;
    use App\Observers\OrderObserver;
    use App\Services\JangoPay\WebhookEnvelope;
    use App\Utils\Payments\JangoPay;
    use Illuminate\Cache\ArrayStore;
    use Illuminate\Config\Repository;
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\DatabaseTransactionsManager;
    use Illuminate\Events\Dispatcher;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Redirector;
    use Illuminate\Routing\ResponseFactory;
    use Illuminate\Routing\Router;
    use Illuminate\Routing\UrlGenerator;
    use Illuminate\Session\ArraySessionHandler;
    use Illuminate\Session\Store;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Facade;
    use Illuminate\Translation\ArrayLoader;
    use Illuminate\Translation\Translator;
    use Illuminate\Validation\Factory;
    use Monolog\Handler\NullHandler;
    use Monolog\Logger;

    $settings = ['standard_currency' => 'USD', 'jangopay_assets' => 'USDT:bsc', 'is_onlinePay' => true];
    function sysConfig($key, $default = null)
    {
        global $settings;

        return $settings[$key] ?? $default;
    }
    function broadcast($event): void
    {
        $GLOBALS['jangopay_test_emissions'][] = ['broadcast', DB::transactionLevel()];
    }
    $GLOBALS['jangopay_test_emissions'] = [];
    $app = new Application(sys_get_temp_dir());
    $rsa = openssl_pkey_new(['private_key_bits' => 2048]);
    openssl_pkey_export($rsa, $pem);
    $key = tempnam(sys_get_temp_dir(), 'jangopay-regression-');
    file_put_contents($key, $pem);
    register_shutdown_function(fn () => is_file($key) && unlink($key));
    $app->instance('config', new Repository([
        'jangopay' => ['enable_payments' => true, 'webhook_tag_format' => 'appended16',
            'final_invoice_statuses' => 'paid', 'private_key_path' => $key, 'api_key' => 'JangoPay_synthetic_only'],
        'database' => ['default' => 'default'],
        'common' => ['currency' => [['code' => 'USD', 'symbol' => '$']]],
    ]));
    Facade::setFacadeApplication($app);
    $app->instance('events', new Dispatcher($app));
    $db = new Capsule($app);
    $mysqlCase = getenv('JANGOPAY_MYSQL_CASE') ?: '';
    $mysqlPort = (int) getenv('JANGOPAY_TEST_MYSQL_PORT');
    if ($mysqlPort > 0) {
        $mysqlPassword = getenv('JANGOPAY_TEST_MYSQL_PASSWORD');
        if (! $mysqlCase) {
            $pdo = new PDO('mysql:host=127.0.0.1;port='.$mysqlPort, 'root', $mysqlPassword);
            $pdo->exec('CREATE DATABASE jangopay_native CHARACTER SET utf8mb4');
        }
        $db->addConnection(['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $mysqlPort, 'database' => 'jangopay_native',
            'username' => 'root', 'password' => $mysqlPassword, 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'strict' => true]);
    } else {
        $db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    }
    $db->setAsGlobal();
    $db->bootEloquent();
    $db->getConnection()->setTransactionManager(new DatabaseTransactionsManager);
    $app->instance('db', $db->getDatabaseManager());
    $app->instance('db.schema', $db->schema());
    $app->instance('auth', new class
    {
        public function id()
        {
            return 1;
        }
    });
    $app->instance('cache', new Illuminate\Cache\Repository(new ArrayStore));
    $session = new Store('regression', new ArraySessionHandler(120));
    $session->start();
    $app->instance('session', $session);
    $app->instance('log', new Logger('regression', [new NullHandler]));
    $app->instance('translator', new Translator(new ArrayLoader, 'en'));
    $app->instance(Illuminate\Contracts\Routing\ResponseFactory::class,
        (new ReflectionClass(ResponseFactory::class))->newInstanceWithoutConstructor());
    $app->instance('request', Request::create('https://proxy.test'));
    $router = new Router($app['events'], $app);
    $router->get('checkout/{trade_no}', fn () => null)->name('jangopay.checkout');
    $router->getRoutes()->refreshNameLookups();
    $app->instance('url', new UrlGenerator($router->getRoutes(), $app['request']));
    $app->instance('redirect', new Redirector($app['url']));
    $validator = new Factory($app['translator'], $app);
    Request::macro('validate', function (array $rules) use ($validator) {
        return $validator->make($this->all(), $rules)->validate();
    });
    if (! $mysqlCase) {
        $schema = $db->schema();
        $schema->create('config', fn ($t) => [$t->string('name')->primary(), $t->text('value')->nullable()]);
        $schema->create('permissions', fn ($t) => [$t->increments('id'), $t->string('name')->unique(), $t->string('guard_name'), $t->string('description')->nullable()]);
        Capsule::table('permissions')->insert(['name' => 'existing.permission', 'guard_name' => 'web']);
        $schema->create('user', fn ($t) => [$t->increments('id'), $t->integer('credit')->default(0),
            $t->date('expired_at')->nullable(), $t->integer('u')->default(0), $t->integer('d')->default(0),
            $t->integer('transfer_enable')->default(0), $t->integer('enable')->default(0),
            $t->integer('level')->default(0), $t->date('reset_time')->nullable(), $t->date('ban_time')->nullable(), $t->timestamps()]);
        $schema->create('goods', fn ($t) => [$t->increments('id'), $t->integer('status'), $t->integer('type'),
            $t->integer('price'), $t->integer('limit_num')->default(0), $t->softDeletes(), $t->timestamps()]);
        $schema->create('coupon', fn ($t) => [$t->increments('id'), $t->integer('type'), $t->integer('status'),
            $t->integer('usable_times'), $t->bigInteger('end_time'), $t->softDeletes(), $t->timestamps()]);
        $schema->create('order', fn ($t) => [$t->increments('id'), $t->string('sn'), $t->integer('user_id'),
            $t->integer('goods_id')->nullable(), $t->integer('coupon_id')->nullable(),
            $t->integer('origin_amount'), $t->integer('amount'), $t->integer('pay_type'), $t->string('pay_way'),
            $t->integer('status')->default(0), $t->integer('is_expire')->default(0), $t->date('expired_at')->nullable(), $t->timestamps()]);
        $schema->create('payment', fn ($t) => [$t->increments('id'), $t->integer('user_id'), $t->integer('order_id'),
            $t->string('trade_no')->unique(), $t->integer('amount'), $t->text('url')->nullable(),
            $t->integer('status')->default(0), $t->timestamps()]);
        $schema->create('payment_callback', fn ($t) => [$t->increments('id'), $t->string('method')->nullable(),
            $t->string('trade_no')->nullable(), $t->string('out_trade_no')->nullable(), $t->string('ip')->nullable(),
            $t->text('payload')->nullable(), $t->integer('amount')->default(0), $t->integer('status')->default(0), $t->timestamps()]);
        $schema->create('coupon_log', fn ($t) => [$t->increments('id'), $t->integer('coupon_id'),
            $t->integer('goods_id')->nullable(), $t->integer('order_id')->nullable(), $t->text('description'), $t->timestamp('created_at')->nullable()]);
        $schema->create('user_credit_log', fn ($t) => [$t->increments('id'), $t->integer('user_id'),
            $t->integer('order_id')->nullable(), $t->integer('before'), $t->integer('after'), $t->integer('amount'),
            $t->text('description')->nullable(), $t->timestamp('created_at')->nullable()]);
        foreach (glob($source.'/database/migrations/*jangopay*.php') as $file) {
            (require $file)->up();
        }
        check(Capsule::table('permissions')->where('name', 'like', 'admin.jangopay.%')->count() === 3, 'upgrade installs three backend permissions');
    }
    new Order;
    if ($app['events']->getListeners('eloquent.updated: '.Order::class) === []) {
        Order::observe(OrderObserver::class);
    }
    if (! $mysqlCase) {
        User::create(['id' => 1, 'credit' => 0]);
    }
    $gateway = new JangoPay;
    $http = new Illuminate\Http\Client\Factory;
    $app->instance(Illuminate\Http\Client\Factory::class, $http);
    if (getenv('JANGOPAY_LIVE_READ_REGRESSION') === '1') {
        require __DIR__.'/live_read_case.php';
        exit;
    }
    $http->preventStrayRequests();
    $mode = 'auth-failure';
    $invoicePosts = 0;
    $quotePosts = 0;
    $nested = null;
    $triggered = false;
    $trade = '';
    $invoiceStatus = 'paid';
    $paidAt = null;
    $merchantAssets = [['code' => 'USDT', 'chain' => 'bsc', 'enabled' => true, 'invoicePaymentEnabled' => true]];
    $settlementPair = ['USDT', 'bsc'];
    $settlementAmount = '3.0';
    $http->fake(function ($r) use ($http, &$mode, &$invoicePosts, &$quotePosts, &$trade, &$invoiceStatus, &$nested, &$triggered, &$paidAt, &$merchantAssets, &$settlementPair, &$settlementAmount, $gateway) {
        $path = parse_url($r->url(), PHP_URL_PATH);
        if ($path === '/api/v1/auth/apikey/token') {
            return $mode === 'auth-failure' ? $http->response(['success' => false], 401)
                : $http->response(['data' => ['accessToken' => 'SYNTHETIC']]);
        }
        if ($path === '/api/v1/invoice/payment-assets') {
            return $http->response(['items' => $mode === 'asset-disabled'
                ? array_map(fn ($row) => array_replace($row, ['enabled' => false]), $merchantAssets) : $merchantAssets]);
        }
        if ($path === '/api/v1/invoice/list') {
            return $http->response(['invoices' => [['invoiceId' => 'I1', 'orderId' => $trade]],
                'total' => 1, 'page' => 1, 'pageSize' => 100, 'totalPages' => $mode === 'bad-pagination' ? 2 : 1]);
        }
        if ($path === '/api/v1/invoice/transactions') {
            $rows = $mode === 'quote-empty' ? [] : [['txnId' => 'T1', 'invoiceId' => 'I1', 'status' => str_starts_with($mode, 'quote-') ? 'pending' : 'settled']];
            if ($mode === 'quote-multiple') {
                $rows[] = ['txnId' => 'T2', 'invoiceId' => 'I1', 'status' => 'pending'];
            }

            return $http->response(['transactions' => $rows,
                'total' => count($rows), 'page' => 1, 'pageSize' => 100, 'totalPages' => 1]);
        }
        if ($path === '/api/v1/invoice/create') {
            $invoicePosts++;
            if ($mode === 'create-timeout') {
                throw new ConnectionException('synthetic timeout');
            }
            $trade = $r['order_id'];

            return $http->response(['invoice' => ['invoiceId' => $mode === 'cny-snapshot' ? 'I_CNY' : 'I1', 'orderId' => $trade]]);
        }
        if ($path === '/api/v1/invoice/I1') {
            $status = str_starts_with($mode, 'quote-') ? 'pending' : $invoiceStatus;
            if ($mode === 'race' && ! $triggered) {
                $triggered = true;
                $nested = $gateway->notify(callbackRequest())->getStatusCode();
                $status = 'pending';
            }

            return $http->response(['invoice' => ['invoiceId' => 'I1', 'orderId' => $trade, 'currencyCode' => 'USD',
                'amount' => $mode === 'wrong-amount' ? '300.00' : '3.00', 'payedAmount' => '3.00', 'status' => $status,
                'paidAt' => $paidAt ?? now()->toIso8601String()]]);
        }
        if ($path === '/api/v1/invoice/transaction/T1') {
            $txn = ['txnId' => 'T1', 'invoiceId' => 'I1', 'status' => str_starts_with($mode, 'quote-') ? 'pending' : 'settled',
                'currencyCode' => 'USD', 'amount' => '3.00',
                'payCurrency' => $settlementPair[0], 'chain' => $mode === 'quote-mismatch' ? 'tron' : $settlementPair[1], 'payAmount' => $settlementAmount,
                'payAddress' => str_starts_with($mode, 'quote-') ? '' : 'SYNTHETIC_SENDER', 'txHash' => '0xSYNTHETIC_HASH'];

            return $http->response(['transaction' => $txn]);
        }
        if ($path === '/v6/latest/CNY') {
            return $http->response(['result' => 'success', 'time_last_update_unix' => time(), 'rates' => ['USD' => 0.14]]);
        }
        throw new RuntimeException('Unexpected external request: '.$path);
    });
    function callbackRequest(): Request
    {
        $log = PaymentCallback::create(['method' => 'jangopay', 'status' => 0]);
        $r = Request::create('/notify', 'POST');
        $r->attributes->set('payment_callback_id', $log->id);
        app()->instance('request', $r);

        return $r;
    }
    $envelope = new class extends WebhookEnvelope
    {
        public function decrypt(Request $r): array
        {
            global $trade;

            return ['event_type' => 'payment.completed', 'invoiceId' => 'I1', 'orderId' => $trade, 'txnId' => 'T1'];
        }
    };
    $app->instance(WebhookEnvelope::class, $envelope);
    if ($mysqlCase) {
        require __DIR__.'/mysql_case.php';
        exit;
    }
    $controller = new PaymentController;
    $buy = fn () => $controller->purchase(Request::create('/purchase', 'POST', ['method' => 'jangopay', 'amount' => 3, 'pay_type' => 4]));
    check($buy()->getData(true)['status'] === 'fail', 'auth failure rejected');
    check(Order::where('status', 0)->count() === 0 && Payment::count() === 0, 'preflight closes native order');
    $coupon = Coupon::create(['type' => 1, 'status' => 1, 'usable_times' => 0, 'end_time' => time() + 86400]);
    $order = Order::create(['sn' => 'COUPON', 'user_id' => 1, 'coupon_id' => $coupon->id, 'origin_amount' => 3,
        'amount' => 3, 'pay_type' => 4, 'pay_way' => 'jangopay']);
    $gateway->purchase(Request::create('/purchase', 'POST', ['id' => $order->id, 'amount' => 3]));
    check($order->fresh()->status === -1 && $coupon->fresh()->usable_times === 1, 'native observer restores coupon');
    $gateway->purchase(Request::create('/purchase', 'POST', ['id' => $order->id, 'amount' => 3]));
    check($coupon->fresh()->usable_times === 1 && Capsule::table('coupon_log')->count() === 1, 'cleanup cannot refund twice');
    $order->updateQuietly(['status' => 2]);
    $gateway->purchase(Request::create('/purchase', 'POST', ['id' => $order->id, 'amount' => 3]));
    check($order->fresh()->status === 2, 'preflight does not close paid order');
    $mode = 'asset-disabled';
    check($buy()->getData(true)['status'] === 'fail' && $invoicePosts === 0, 'disabled asset cannot create an invoice');
    check(Order::where('status', 0)->count() === 0 && Payment::count() === 0, 'disabled asset safely closes pending native order before payment creation');
    $mode = 'healthy';
    check($buy()->getData(true)['status'] === 'success' && $invoicePosts === 1, 'auth recovery creates one invoice');
    $attempt = JangoPayAttempt::firstOrFail();
    $payment = Payment::findOrFail($attempt->payment_id);
    check(JangoPay::canPay($payment, $attempt), 'active order payable');
    check($gateway->checkout($trade)->getTargetUrl() === 'https://app.jangopay.org/pay/I1' && $quotePosts === 0, 'guarded native checkout redirects to JangoPay without coin POST');
    $app->instance(WebhookEnvelope::class, new class extends WebhookEnvelope
    {
        public function decrypt(Request $r): array
        {
            global $trade;

            return ['event_type' => 'payout.completed', 'invoiceId' => 'I1', 'orderId' => $trade, 'txnId' => 'T1'];
        }
    });
    $r = callbackRequest();
    $r->headers->set('X-JangoPay-Event', 'payout.completed');
    $ignored = $gateway->notify($r);
    $log = PaymentCallback::find($r->attributes->get('payment_callback_id'));
    check($ignored->getStatusCode() === 200 && $ignored->getData(true)['status'] === 'ignored_event', 'decrypted payout acknowledged without payment processing');
    check($log->status !== PaymentCallback::STATUS_FULFILLED && json_decode($log->payload, true)['result'] === 'ignored_event', 'payout audit is ignored, never fulfilled');
    check($attempt->fresh()->state === 'awaiting_payment' && Capsule::table('user_credit_log')->count() === 0, 'payout cannot credit a pending order even with matching identifiers');
    $app->instance(WebhookEnvelope::class, $envelope);
    $invoiceStatus = 'pending';
    check($gateway->notify(callbackRequest())->getStatusCode() === 503 && $attempt->fresh()->state === 'awaiting_payment', 'pending record stays retryable');
    check(Capsule::table('user_credit_log')->count() === 0, 'pending does not credit');
    $invoiceStatus = 'paid';
    $mode = 'wrong-amount';
    check($gateway->notify(callbackRequest())->getStatusCode() === 503 && Capsule::table('user_credit_log')->count() === 0, 'mismatched amount cannot deliver');
    $mode = 'race';
    check($gateway->notify(callbackRequest())->getStatusCode() === 503 && $nested === 200, 'concurrent delivery does not relabel stale pending evidence as fulfilled');
    check($attempt->fresh()->state === 'delivered' && Payment::find($payment->id)->status === 1, 'stale callback cannot overwrite delivery');
    check(Capsule::table('user_credit_log')->count() === 1 && (float) User::find(1)->credit === 3.0, 'native order service credits once');
    $before = Capsule::table('user_credit_log')->count();
    $r = callbackRequest();
    check($gateway->notify($r)->getStatusCode() === 200, 'duplicate acknowledged');
    $log = PaymentCallback::find($r->attributes->get('payment_callback_id'));
    check($log->status === 1 && $log->trade_no === $trade && $log->out_trade_no === 'T1' && (float) $log->amount === 3.0, 'duplicate ledger complete with correct money cast');
    check(Capsule::table('user_credit_log')->count() === $before, 'duplicate does not credit again');
    check(! JangoPay::canPay($payment->fresh(), $attempt->fresh()), 'delivered no longer payable');
    // New ambiguous remote creation must retain its native order; no second POST on retry.
    $app['cache']->flush();
    $mode = 'create-timeout';
    check($buy()->getData(true)['status'] === 'fail', 'create timeout surfaces failure');
    $unknown = JangoPayAttempt::where('state', 'unknown_creation')->firstOrFail();
    check(Payment::find($unknown->payment_id)->order->status === 0 && $invoicePosts === 2, 'unknown invoice retains pending order');
    check(! JangoPay::canPay(Payment::find($unknown->payment_id), $unknown) && $invoicePosts === 2 && $quotePosts === 0, 'unknown invoice cannot expose hosted checkout or repeat a POST');
    $mode = 'auth-failure';
    $unknownPayment = Payment::find($unknown->payment_id);
    $gateway->purchase(Request::create('/purchase', 'POST', ['id' => $unknownPayment->order_id, 'amount' => 3]));
    check($unknownPayment->order->fresh()->status === 0, 'preflight cleanup cannot close an existing uncertain attempt');
    Payment::whereKey($payment->id)->delete();
    $r = callbackRequest();
    check($gateway->notify($r)->getStatusCode() === 200, 'delivered retry survives native payment retention');
    $log = PaymentCallback::find($r->attributes->get('payment_callback_id'));
    check($log->status === 1 && (float) $log->amount === 3.0 && Capsule::table('user_credit_log')->count() === 1, 'retained snapshot fills ledger without delivery');
    require __DIR__.'/recovery_cases.php';
    require __DIR__.'/admin_cases.php';
    require __DIR__.'/boundary_cases.php';
    require __DIR__.'/hosted_checkout_cases.php';
    require __DIR__.'/recovery_entry_cases.php';
    require __DIR__.'/all_asset_cases.php';
    echo "PASS {$checks} native flow checks (isolated DB; user and decrypted event are fixtures)\n";
}
