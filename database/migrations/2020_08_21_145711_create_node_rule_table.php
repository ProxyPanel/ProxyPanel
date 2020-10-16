<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_rule', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('node_id')->nullable()->comment('节点ID');
            $table->unsignedInteger('rule_id')->nullable()->comment('审计规则ID');
            $table->boolean('is_black')->default(1)->comment('是否黑名单模式：0-不是、1-是');
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
        Schema::dropIfExists('node_rule');
    }
}
