<?php

use Illuminate\Database\Migrations\Migration;

class AddAffCodeConfig extends Migration
{
    protected $newConfigs = [
        'aff_salt',
    ];

    public function up()
    {
        foreach ($this->newConfigs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
    }

    public function down()
    {
        \App\Models\Config::whereIn('name', $this->newConfigs)->delete();
    }
}
