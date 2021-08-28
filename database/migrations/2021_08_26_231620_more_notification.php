<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class MoreNotification extends Migration
{
    protected $configs = [
        'wechat_aid',
        'wechat_secret',
        'wechat_cid',
        'tg_chat_token',
        'pushplus_token',
    ];

    public function up()
    {
        foreach ($this->configs as $config) {
            Config::insert(['name' => $config]);
        }
    }

    public function down()
    {
        Config::destroy($this->configs);
    }
}
