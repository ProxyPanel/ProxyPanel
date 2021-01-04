<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class AddPaybeaverPayment extends Migration
{
    protected $configs = [
        'paybeaver_app_id',
        'paybeaver_app_secret',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
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
