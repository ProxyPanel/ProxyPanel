<?php

use App\Components\MigrationToolBox;
use App\Models\Coupon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImproveCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon', function (Blueprint $table) {
            if ((new MigrationToolBox())->versionCheck()) {
                $table->json('limit')->nullable()->comment('使用限制')->after('rule');
            } else {
                $table->text('limit')->nullable()->comment('使用限制')->after('rule');
            }
            $table->unsignedTinyInteger('priority')->default(0)->comment('使用权重, 高者优先')->after('limit');
            $table->dropUnique(['sn']);
        });

        foreach (Coupon::whereNotNull('rule')->get() as $coupon) {
            $coupon->update(['limit' => ['minimum' => $coupon->rule]]);
        }

        Schema::table('coupon', function (Blueprint $table) {
            $table->dropColumn('rule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->unsignedInteger('rule')->nullable()->comment('使用限制(元)')->after('limit');
        });

        foreach (Coupon::whereNotNull('limit')->get() as $coupon) {
            if (isset($coupon->limit['minimum'])) {
                $coupon->update(['rule' => $coupon->limit['minimum']]);
            }
        }

        Schema::table('coupon', function (Blueprint $table) {
            $table->dropColumn('limit', 'priority');
            $table->unique('sn');
        });
    }
}
