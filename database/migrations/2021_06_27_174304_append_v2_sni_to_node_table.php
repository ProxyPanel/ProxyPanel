<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendV2SniToNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('node', function (Blueprint $table) {
            $table->string('v2_sni', 191)->nullable()->comment('V2Ray的SNI配置');
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
            $table->dropColumn(['v2_sni']);
        });
    }
}
