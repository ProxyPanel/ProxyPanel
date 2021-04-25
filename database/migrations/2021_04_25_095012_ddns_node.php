<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DdnsNode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('node', function (Blueprint $table) {
            $table->text('ip')->nullable()->comment('服务器IPV4地址')->change();
            $table->text('ipv6')->nullable()->comment('服务器IPV6地址')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('ip', 'ipv6');
        });
        Schema::table('node', function (Blueprint $table) {
            $table->ipAddress('ip')->nullable()->comment('服务器IPV4地址')->after('server');
            $table->ipAddress('ipv6')->nullable()->comment('服务器IPV6地址')->after('ip');
        });
    }
}
