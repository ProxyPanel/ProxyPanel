<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private static array $configs = ['cryptomus_merchant_uuid', 'cryptomus_api_key'];

    public function up(): void
    {
        if (Config::exists()) {
            foreach (self::$configs as $config) {
                Config::insert(['name' => $config]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::$configs as $config) {
            Config::destroy(['name' => $config]);
        }
    }
};
