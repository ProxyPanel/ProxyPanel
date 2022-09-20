<?php

use App\Components\MigrationToolBox;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('分组名称');
            if ((new MigrationToolBox())->versionCheck()) {
                $table->json('nodes')->nullable()->comment('关联的节点ID，多个用,号分隔');
            } else {
                $table->text('nodes')->nullable()->comment('关联的节点ID，多个用,号分隔');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group');
    }
}
