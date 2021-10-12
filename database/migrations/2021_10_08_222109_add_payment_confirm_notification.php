<?php

use Illuminate\Database\Migrations\Migration;

class AddPaymentConfirmNotification extends Migration
{
    protected $configs = [
        'payment_confirm_notification',
        'wechat_token',
        'wechat_encodingAESKey',
    ];

    public function up()
    {
        foreach ($this->configs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
    }

    public function down()
    {
        \App\Models\Config::destroy($this->configs);
    }
}
