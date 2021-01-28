<?php

use Illuminate\Database\Migrations\Migration;

class ConfigClean extends Migration
{
    protected $configs = [
        'mix_subscribe',
        'geetest_id',
        'geetest_key',
        'google_captcha_secret',
        'google_captcha_sitekey',
        'hcaptcha_secret',
        'hcaptcha_sitekey',
    ];
    protected $newConfigs = [
        'captcha_key',
        'captcha_secret',
    ];

    public function up()
    {
        \App\Models\Config::whereIn('name', $this->configs)->delete();
        foreach ($this->newConfigs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
    }

    public function down()
    {
        foreach ($this->configs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
        \App\Models\Config::whereIn('name', $this->newConfigs)->delete();
    }
}
