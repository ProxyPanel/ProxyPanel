<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleGroupNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rule_group_node', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_group_id')->comment('规则分组ID');
            $table->unsignedInteger('node_id')->comment('节点ID');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rule_group_node');
    }
}
