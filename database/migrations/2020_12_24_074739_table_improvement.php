<?php

use App\Models\Coupon;
use App\Models\Node;
use App\Models\NodeOnlineIp;
use App\Models\Order;
use App\Models\RuleGroup;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableImprovement extends Migration
{
    public function up()
    {
        // ----- 开始 数据库表关系优化 -----
        Schema::table('level', function (Blueprint $table) {
            $table->unique('level');
        });

        Schema::table('node_certificate', function (Blueprint $table) {
            $table->unique('domain');
        });

        Schema::table('ss_node', function (Blueprint $table) {
            $table->unsignedInteger('rule_group_id')->nullable()->comment('从属规则分组ID')->after('level');
            $table->foreign('rule_group_id')->references('id')->on('rule_group')->nullOnDelete();
            $table->rename('node');
        });

        Schema::table('ss_node_info', function (Blueprint $table) {
            $table->unsignedInteger('node_id')->comment('节点ID')->change();
            $table->rename('node_heartbeat');
        });

        Schema::table('ss_node_ip', function (Blueprint $table) {
            $table->rename('node_online_ip');
        });
        NodeOnlineIp::whereNodeId(0)->update(['node_id' => null]);
        NodeOnlineIp::whereUserId(0)->update(['user_id' => null]);
        Schema::table('node_online_ip', function (Blueprint $table) {
            $table->unsignedInteger('node_id')->comment('节点ID')->change();
            $table->unsignedInteger('user_id')->default(null)->nullable()->change();
        });

        Schema::table('ss_node_online_log', function (Blueprint $table) {
            $table->rename('node_online_log');
        });

        Schema::table('node_label', function (Blueprint $table) {
            $table->unsignedInteger('node_id')->comment('节点ID')->change();
            $table->unsignedInteger('label_id')->comment('标签ID')->change();
            $table->unique(['node_id', 'label_id']);
            $table->rename('label_node');
        });

        Order::whereGoodsId(0)->update(['goods_id' => null]);
        Order::whereCouponId(0)->orWhereNotIn('coupon_id', Coupon::withTrashed()->pluck('id')->toArray())->update(['coupon_id' => null]);
        Schema::table('order', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('购买者ID')->change();
            $table->foreign('goods_id')->references('id')->on('goods')->nullOnDelete();
            $table->foreign('coupon_id')->references('id')->on('coupon')->nullOnDelete();
        });

        Schema::create('node_user_group', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('node_id')->comment('节点ID');
            $table->unsignedInteger('user_group_id')->comment('从属用户分组ID');

            $table->unique(['user_group_id', 'node_id']);
            $table->foreign('node_id')->references('id')->on('node')->cascadeOnDelete();
            $table->foreign('user_group_id')->references('id')->on('user_group')->cascadeOnDelete();
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('order')->cascadeOnDelete();
        });

        Schema::table('referral_apply', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('申请者ID')->change();
        });

        Schema::table('rule_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('触发者ID')->change();
        });

        Schema::create('rule_rule_group', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_id')->comment('规则ID');
            $table->unsignedInteger('rule_group_id')->comment('从属规则分组ID');

            $table->unique(['rule_group_id', 'rule_id']);
            $table->foreign('rule_id')->references('id')->on('rule')->cascadeOnDelete();
            $table->foreign('rule_group_id')->references('id')->on('rule_group')->cascadeOnDelete();
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('ticket_reply', function (Blueprint $table) {
            $table->unsignedInteger('ticket_id')->comment('工单ID')->change();
        });

        Schema::table('user', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->nullable()->default(null)->comment('所属分组')->change();
        });
        User::whereGroupId(0)->update(['group_id' => null]);
        Schema::table('user', function (Blueprint $table) {
            $table->renameColumn('group_id', 'user_group_id');
            $table->foreign('user_group_id')->references('id')->on('user_group')->nullOnDelete();
        });

        Schema::table('user_baned_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_credit_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_daily_data_flow', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_data_modify_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_hourly_data_flow', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_login_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_subscribe', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
        });

        Schema::table('user_traffic_log', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->comment('用户ID')->change();
            $table->unsignedInteger('node_id')->comment('节点ID')->change();
        });
        // ----- 结束 数据库表关系优化 -----

        // ----- 开始 数据转化 & 弃用数据 -----
        foreach (RuleGroup::all() as $group) {
            $group->rules()->attach(json_decode($group->rules, true));
            foreach (json_decode($group->nodes, true) as $id) {
                $node = Node::find($id);
                if ($node) {
                    $node->update(['rule_group_id' => $group->id]);
                }
            }
        }

        foreach (UserGroup::all() as $group) {
            $group->nodes()->attach(json_decode($group->nodes, true));
        }

        Schema::table('rule_group', function (Blueprint $table) {
            $table->dropColumn('nodes');
            $table->dropColumn('rules');
        });

        Schema::table('user_group', function (Blueprint $table) {
            $table->dropColumn('nodes');
        });

        Schema::table('node_rule', function (Blueprint $table) {
            $table->drop();
        });

        Schema::table('rule_group_node', function (Blueprint $table) {
            $table->drop();
        });
        // ----- 结束 数据转化 & 弃用数据 -----
    }

    public function down()
    {
        // 不可逆
    }
}
