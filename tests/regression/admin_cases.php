<?php

use App\Console\Commands\JangoPayReconcile;
use App\Http\Controllers\Admin\JangoPayController;
use App\Http\Middleware\Permission;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Exception\HttpException;

$router->get('admin/payments/jangopay', fn () => null)->name('admin.jangopay.index');
$router->getRoutes()->refreshNameLookups();
[$a,$o,$p] = $seed();
$o->close();
$recovery->check($a->id);
$admin = new JangoPayController;
$request = Request::create('/admin/resolve', 'POST', ['note' => 'Refund handled externally; receipt TEST-123', 'confirm' => '1']);
$admin->resolve($request, $a->id);
check($a->fresh()->state === 'resolved' && Capsule::table('jangopay_actions')->where('attempt_id', $a->id)->count() === 1, 'external handling recorded with operator audit');
check($recovery->check($a->id) === 'resolved' && $o->fresh()->status === -1, 'resolved closed order cannot later auto-deliver');
try {
    $admin->resolve($request, $a->id);
    throw new RuntimeException('Resolved action repeated');
} catch (HttpException $e) {
    check($e->getStatusCode() === 409, 'resolution is terminal');
}
[$a,$o,$p] = $seed('unknown_creation');
$app->instance('request', Request::create('/admin/check', 'POST', ['invoice_id' => 'I1', 'txn_id' => 'T1']));
$admin->check($app['request'], $a->id, $recovery);
check($a->fresh()->state === 'delivered' && Capsule::table('jangopay_actions')->where('attempt_id', $a->id)->value('action') === 'recheck', 'backend authenticated recheck recovers and audits');
$allowed = false;
$auth = new class($allowed)
{
    public function __construct(public bool &$allowed) {}

    public function guard($guard = null)
    {
        return $this;
    }

    public function guest()
    {
        return false;
    }

    public function id()
    {
        return 1;
    }

    public function user()
    {
        return new class($this->allowed)
        {
            public function __construct(public bool $allowed) {}

            public function can($ability)
            {
                return $this->allowed && $ability === 'admin.jangopay.check';
            }
        };
    }
};
$app->instance('auth', $auth);
$protected = Request::create('/admin/check');
$protected->setRouteResolver(fn () => (new Route(['POST'], 'admin/check', []))->name('admin.jangopay.check'));
$app->instance('request', $protected);
try {
    (new Permission)->handle($protected, fn () => 'allowed');
    throw new RuntimeException('Unauthorized backend action accepted');
} catch (UnauthorizedException $e) {
    check($e->getStatusCode() === 403, 'native permission middleware denies backend action');
}
$allowed = true;
check((new Permission)->handle($protected, fn () => 'allowed') === 'allowed', 'native permission middleware accepts authorized action');
$command = new JangoPayReconcile;
$command->setLaravel($app);
$console = new Application;
$console->setAutoExit(false);
$console->add($command);
$buffer = new BufferedOutput;
check($console->run(new ArrayInput(['command' => 'jangopay:reconcile', '--attempt' => (string) $a->id]), $buffer) === 0, 'console recovery command executes');
check(str_contains($buffer->fetch(), 'delivered'), 'console reports durable state');
$buffer = new BufferedOutput;
check($console->run(new ArrayInput(['command' => 'jangopay:reconcile', '--limit' => '0']), $buffer) !== 0, 'console rejects invalid batch limit');
$buffer = new BufferedOutput;
check($console->run(new ArrayInput(['command' => 'jangopay:reconcile', '--attempt' => '99999999']), $buffer) !== 0
    && str_contains($buffer->fetch(), 'Attempt not found'), 'console explicitly reports a missing attempt');
[$unresolved,$unresolvedOrder,$unresolvedPayment] = $seed('quote_unknown');
$admin->resolve($request, $unresolved->id);
check($unresolved->fresh()->state === 'resolved' && $unresolvedOrder->fresh()->status === -1
    && $unresolvedPayment->fresh()->status === -1, 'externally handled unknown quote releases the native pending order');
