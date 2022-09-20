<?php

use App\Components\MigrationToolBox;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rule_group', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('type')->default(1)->comment('模式：1-阻断、0-放行');
            $table->string('name')->comment('分组名称');
            if ((new MigrationToolBox())->versionCheck()) {
                $table->json('rules')->nullable()->comment('关联的规则ID，多个用,号分隔');
                $table->json('nodes')->nullable()->comment('关联的节点ID，多个用,号分隔');
            } else {
                $table->text('rules')->nullable()->comment('关联的规则ID，多个用,号分隔');
                $table->text('nodes')->nullable()->comment('关联的节点ID，多个用,号分隔');
            }

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
        Schema::dropIfExists('rule_group');
    }
}
