<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class AddMoreNotifications extends Migration
{
    protected $addConfigs = [
        'iYuu_token',
        'pushDeer_key',
        'dingTalk_access_token',
        'dingTalk_secret',
    ];

    protected $removeConfigs = [
        'push_bear_send_key',
        'push_bear_qrcode',
    ];

    public function up()
    {
        foreach ($this->addConfigs as $config) {
            Config::insertOrIgnore(['name' => $config]);
        }

        Config::destroy($this->removeConfigs);
    }

    public function down()
    {
        foreach ($this->removeConfigs as $config) {
            Config::insertOrIgnore(['name' => $config]);
        }

        Config::destroy($this->addConfigs);
    }
}
