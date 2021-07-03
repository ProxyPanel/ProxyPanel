<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Oauth extends Migration
{
    protected $configs = [
        'oauth_path',
        'username_type',
    ];

    public function up()
    {
        Schema::create('user_oauth', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique()->comment('用户ID');
            $table->string('type', 10)->comment('登录类型');
            $table->string('identifier', 128)->unique()->comment('手机号/邮箱/第三方的唯一标识');
            $table->string('credential', 128)->comment('密码/Token凭证');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('最后更新时间');
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        foreach ($this->configs as $config) {
            Config::insert(['name' => $config]);
        }

        Schema::table('user', function (Blueprint $table) {
            $table->renameColumn('username', 'nickname');
            $table->renameColumn('email', 'username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
            $table->renameColumn('nickname', 'username');
        });
        Config::destroy($this->configs);

        Schema::dropIfExists('user_oauth');
    }
}
