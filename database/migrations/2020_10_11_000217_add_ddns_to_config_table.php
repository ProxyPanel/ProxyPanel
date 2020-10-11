<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class AddDdnsToConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    private static $dropConfigs = [
        'is_user_rand_port', 'is_namesilo', 'namesilo_key', 'is_forbid_china', 'is_forbid_oversea',
        'alipay_private_key', 'alipay_public_key', 'alipay_transport', 'alipay_currency',
    ];

    private static $newConfigs = ['forbid_mode', 'ddns_mode', 'ddns_key', 'ddns_secret'];


    public function up()
    {
        foreach (self::$newConfigs as $config) {
            Config::insert(['name' => $config]);
        }
        foreach (self::$dropConfigs as $config) {
            Config::destroy(['name' => $config]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::$newConfigs as $config) {
            Config::destroy(['name' => $config]);
        }
        foreach (self::$dropConfigs as $config) {
            Config::insert(['name' => $config]);
        }
    }
}
