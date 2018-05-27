<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentCallbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_callback', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('client_id', 50)->nullable();
            $table->string('yz_id', 50)->nullable();
            $table->string('kdt_id', 50)->nullable();
            $table->string('kdt_name', 50)->nullable();
            $table->tinyInteger('mode')->nullable();
            $table->text('msg')->nullable();
            $table->integer('sendCount')->default('0');
            $table->string('sign', 32)->default('1');
            $table->string('status', 30)->default('0');
            $table->tinyInteger('test')->default('0');
            $table->string('type', 50)->default('');
            $table->string('version', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_callback');
    }
}
