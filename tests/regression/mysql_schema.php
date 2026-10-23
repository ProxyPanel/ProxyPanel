<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;

// Optional: only against a fresh disposable local database, with its own temporary server/password.
require __DIR__.'/bootstrap.php';
$port = (int) getenv('JANGOPAY_TEST_MYSQL_PORT');
$password = getenv('JANGOPAY_TEST_MYSQL_PASSWORD');
if ($port < 1024 || $password === false || $password === '') {
    throw new RuntimeException('Requires an explicitly configured disposable local MySQL server.');
}
$pdo = new PDO('mysql:host=127.0.0.1;port='.$port, 'root', $password);
$pdo->exec('CREATE DATABASE jangopay_regression CHARACTER SET utf8mb4');
$app = new Application(sys_get_temp_dir());
$app->instance('config', new Repository(['database' => ['default' => 'default']]));
Facade::setFacadeApplication($app);
$db = new Manager($app);
$db->addConnection(['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $port, 'database' => 'jangopay_regression',
    'username' => 'root', 'password' => $password, 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'strict' => true]);
$db->setAsGlobal();
$db->bootEloquent();
$app->instance('db', $db->getDatabaseManager());
$app->instance('db.schema', $db->schema());
$schema = $db->schema();
$schema->create('config', fn ($t) => [$t->string('name')->primary(), $t->text('value')->nullable()]);
$first = require $source.'/database/migrations/2026_10_22_000000_add_jangopay.php';
$first->up();
$row = ['payment_id' => 1, 'order_id' => 'OLD', 'base_currency' => 'USD', 'base_amount' => '3.00',
    'amount_usd' => '3.00', 'fx_rate' => '1', 'allowed_assets' => 'USDT:bsc', 'selected_asset' => 'USDT:bsc', 'state' => 'quoted'];
$db->table('jangopay_attempts')->insert($row);
$long = 'USDT:'.str_repeat('a', 32);
try {
    $db->table('jangopay_attempts')->where('payment_id', 1)->update(['selected_asset' => $long]);
    throw new RuntimeException('Strict MySQL did not reject the old 20-character width');
} catch (QueryException $e) {
    check((int) ($e->errorInfo[1] ?? 0) === 1406, 'old strict schema reproduces truncation failure');
}
$upgrade = require $source.'/database/migrations/2026_10_22_000001_widen_jangopay_asset.php';
$upgrade->up();
$column = $db->getConnection()->selectOne("SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jangopay_attempts' AND COLUMN_NAME = 'selected_asset'");
check((int) $column->len === 80, 'existing schema widened');
check($db->table('jangopay_attempts')->value('selected_asset') === 'USDT:bsc', 'existing evidence unchanged');
$db->table('jangopay_attempts')->where('payment_id', 1)->update(['selected_asset' => $long]);
check($db->table('jangopay_attempts')->value('selected_asset') === $long, 'full maximum-length pair round-trips');
$upgrade->up();
$upgrade->down();
$first->down();
check($db->table('jangopay_attempts')->count() === 1 && $db->table('jangopay_attempts')->value('selected_asset') === $long, 'rerun and rollback retain financial evidence');
$app->instance('cache', new Illuminate\Cache\Repository(new ArrayStore));
$schema->create('permissions', fn ($t) => [$t->increments('id'), $t->string('name')->unique(), $t->string('guard_name'), $t->string('description')->nullable()]);
$metadata = require $source.'/database/migrations/2026_10_22_000002_add_jangopay_reconciliation.php';
$metadata->up();
check($db->table('permissions')->count() === 0 && $schema->hasColumn('jangopay_attempts', 'last_checked_at'), 'fresh install leaves permission seeding to RBACSeeder');
$db->table('permissions')->insert(['name' => 'existing.permission', 'guard_name' => 'web']);
$metadata->up();
check($db->table('permissions')->where('name', 'like', 'admin.jangopay.%')->count() === 3, 'upgrade adds backend permissions');
$metadata->up();
$metadata->down();
check($db->table('permissions')->count() === 4 && $schema->hasTable('jangopay_actions')
    && $db->table('jangopay_attempts')->value('selected_asset') === $long, 'metadata migration is idempotent and retains evidence');
$reference = require $source.'/database/migrations/2026_10_23_000003_add_jangopay_fx_reference.php';
$reference->up();
check($schema->hasColumn('jangopay_attempts', 'fx_reference') && $db->table('jangopay_attempts')->value('fx_reference') === null, 'pricing reference upgrade preserves existing attempts');
$snapshot = json_encode(['usd_per_cny' => '0.14', 'verified_at' => 'synthetic approval']);
$db->table('jangopay_attempts')->update(['fx_reference' => $snapshot]);
$reference->up();
$reference->down();
check($db->table('jangopay_attempts')->value('fx_reference') === $snapshot, 'pricing reference survives repeat migration and rollback');
echo "PASS {$checks} strict MySQL/MariaDB migration checks\n";
