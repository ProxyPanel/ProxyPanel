<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSubscribeDesc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_subscribe', function (Blueprint $table) {
            $table->text('ban_desc')->nullable()->comment('封禁理由')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_subscribe', function (Blueprint $table) {
            $table->string('ban_desc', 50)->nullable()->comment('封禁理由')->change();
        });
    }
}
