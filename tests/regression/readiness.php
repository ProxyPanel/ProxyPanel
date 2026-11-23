<?php

use App\Services\JangoPay\Ready;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;

require __DIR__.'/bootstrap.php';
$app = new Application(sys_get_temp_dir());
$rsa = openssl_pkey_new(['private_key_bits' => 2048]);
openssl_pkey_export($rsa, $pem);
$path = tempnam(sys_get_temp_dir(), 'jangopay-regression-');
file_put_contents($path, $pem);
try {
    $app->instance('config', new Repository(['jangopay' => [
        'enable_payments' => true, 'webhook_tag_format' => 'appended16',
        'final_invoice_statuses' => 'paid', 'private_key_path' => $path,
    ]]));
    $expected = ! in_array($argv[1] ?? '', ['disabled-process', 'missing-cli'], true);
    check(Ready::enabled() === $expected, 'readiness '.($argv[1] ?? 'working'));
    $app['config']->set('jangopay.enable_payments', false);
    check(! Ready::enabled(), 'disabled configuration');
    echo "PASS {$checks} readiness checks\n";
} finally {
    unlink($path);
}
