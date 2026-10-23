<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// Isolated Laravel test harness. Never boots the site's providers or reads its .env/database.
$source = dirname(__DIR__, 2);
$upstream = getenv('JANGOPAY_UPSTREAM') ?: $source;
$autoload = getenv('JANGOPAY_VENDOR_AUTOLOAD') ?: $upstream.'/vendor/autoload.php';
$loader = require $autoload;
$loader->addPsr4('App\\', [$source.'/app', $upstream.'/app'], true);
foreach (['DB', 'Auth', 'Log'] as $alias) {
    if (! class_exists($alias)) {
        class_alias('Illuminate\\Support\\Facades\\'.$alias, $alias);
    }
}
if (! class_exists('Arr')) {
    class_alias(Arr::class, 'Arr');
}
if (! class_exists('Str')) {
    class_alias(Str::class, 'Str');
}
$checks = 0;
function check(bool $condition, string $label): void
{
    global $checks;
    if (! $condition) {
        throw new RuntimeException('FAIL: '.$label);
    }
    $checks++;
}
