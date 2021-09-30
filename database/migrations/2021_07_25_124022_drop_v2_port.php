<?php

use App\Models\Node;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropV2Port extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Node::whereType(2)->get() as $node) {
            $node->port = $node->v2_port;
        }

        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('v2_port');
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
            $table->unsignedSmallInteger('v2_port')->default(0)->comment('V2Ray服务端口')->after('v2_alter_id');
        });
    }
}
