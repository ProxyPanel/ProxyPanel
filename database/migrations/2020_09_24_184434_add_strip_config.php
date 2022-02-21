<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class AddStripConfig extends Migration
{
    protected $configs = [
        'stripe_public_key',
        'stripe_secret_key',
        'stripe_signing_secret',
        'stripe_currency',
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
