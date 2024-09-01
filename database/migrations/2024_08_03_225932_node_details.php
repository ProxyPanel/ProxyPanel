<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private static array $configs = ['node_renewal_notification'];

    public function up(): void
    {
        Schema::table('node', static function (Blueprint $table) {
            $table->json('details')->nullable()->comment('节点信息')->after('client_limit');
        });

        if (Config::exists()) {
            foreach (self::$configs as $config) {
                Config::insert(['name' => $config]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('node', static function (Blueprint $table) {
            $table->dropColumn('details');
        });

        foreach (self::$configs as $config) {
            Config::destroy(['name' => $config]);
        }
    }
};
