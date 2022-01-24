<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMsgidNotificationLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_log', static function (Blueprint $table) {
            $table->uuid('msg_id')->nullable()->comment('消息对公查询号')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_log', static function (Blueprint $table) {
            $table->dropColumn('msg_id');
        });
    }
}
