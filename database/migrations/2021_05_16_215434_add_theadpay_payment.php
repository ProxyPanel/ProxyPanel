<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class AddTheadpayPayment extends Migration
{
    protected $configs = [
        'theadpay_url',
        'theadpay_mchid',
        'theadpay_key',
    ];

    public function up()
    {
        foreach ($this->configs as $config) {
            Config::insert(['name' => $config]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Config::destroy($this->configs);
    }
}
