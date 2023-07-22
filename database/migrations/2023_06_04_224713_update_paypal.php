<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private static array $dropConfigs = ['paypal_username', 'paypal_password', 'paypal_secret', 'paypal_certificate'];

    private static array $newConfigs = ['paypal_client_id', 'paypal_client_secret'];

    public function up(): void
    {
        if (Config::exists()) {
            foreach (self::$newConfigs as $config) {
                Config::insert(['name' => $config]);
            }
            foreach (self::$dropConfigs as $config) {
                Config::destroy(['name' => $config]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::$newConfigs as $config) {
            Config::destroy(['name' => $config]);
        }
        foreach (self::$dropConfigs as $config) {
            Config::insert(['name' => $config]);
        }
    }
};
