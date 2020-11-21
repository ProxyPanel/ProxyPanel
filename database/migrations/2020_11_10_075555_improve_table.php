<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImproveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('coupon_log', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->default(null)->nullable()->change();
            $table->unsignedInteger('goods_id')->default(null)->nullable()->change();
            $table->unsignedInteger('order_id')->default(null)->nullable()->change();
            $table->foreign('coupon_id')->references('id')->on('coupon')->nullOnDelete();
            $table->foreign('goods_id')->references('id')->on('goods')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('order')->nullOnDelete();
        });

        Schema::table('email_filter', function (Blueprint $table) {
            $table->index(['words', 'type']);
        });

        Schema::table('invite', function (Blueprint $table) {
            $table->foreign('inviter_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('invitee_id')->references('id')->on('user')->nullOnDelete();
        });

        Schema::table('node_auth', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('node_daily_data_flow', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('node_hourly_data_flow', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('node_label', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
            $table->foreign('label_id')->references('id')->on('label')->cascadeOnDelete();
        });

        Schema::table('node_ping', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('node_rule', function (Blueprint $table) {
            $table->index(['node_id', 'rule_id']);
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
            $table->foreign('rule_id')->references('id')->on('rule')->cascadeOnDelete();
        });

        Schema::table('order', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->index(['user_id', 'order_id']);
        });

        Schema::table('referral_apply', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('referral_log', function (Blueprint $table) {
            $table->unsignedInteger('invitee_id')->default(null)->nullable()->change();
            $table->unsignedInteger('order_id')->default(null)->nullable()->change();
            $table->foreign('inviter_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('invitee_id')->references('id')->on('user')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('order')->nullOnDelete();
            $table->index(['inviter_id', 'invitee_id']);
        });

        Schema::table('rule_group_node', function (Blueprint $table) {
            $table->foreign('rule_group_id')->references('id')->on('rule_group')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('rule_log', function (Blueprint $table) {
            $table->unsignedInteger('node_id')->default(null)->nullable()->change();
            $table->unsignedInteger('rule_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('ss_node')->nullOnDelete();
            $table->foreign('rule_id')->references('id')->on('rule')->nullOnDelete();
        });

        Schema::table('ss_config', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('ss_node', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('ss_node_info', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('ss_node_ip', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('ss_node_online_log', function (Blueprint $table) {
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->default(null)->nullable()->comment('管理员ID')->change();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('user')->nullOnDelete();
        });

        Schema::table('ticket_reply', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->default(null)->nullable()->comment('管理员ID')->change();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('user')->nullOnDelete();
            $table->foreign('ticket_id')->references('id')->on('ticket')->cascadeOnDelete();
        });

        Schema::table('user', function (Blueprint $table) {
            $table->foreign('inviter_id')->references('id')->on('user')->nullOnDelete();
        });

        Schema::table('user_baned_log', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('user_credit_log', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->default(null)->nullable()->comment('订单ID')->change();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('order')->nullOnDelete();
        });

        Schema::table('user_daily_data_flow', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('user_data_modify_log', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->default(null)->nullable()->comment('发生的订单ID')->change();
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('order')->nullOnDelete();
        });

        Schema::table('user_hourly_data_flow', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('user_login_log', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('user_subscribe', function (Blueprint $table) {
            $table->unique('code');
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::table('user_subscribe_log', function (Blueprint $table) {
            $table->foreign('user_subscribe_id')->references('id')->on('user_subscribe')->cascadeOnDelete();
        });

        Schema::table('user_traffic_log', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('ss_node')->cascadeOnDelete();
        });

        Schema::table('verify', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('coupon_log', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['goods_id']);
            $table->dropForeign(['order_id']);
            $table->dropIndex('coupon_log_coupon_id_foreign');
            $table->dropIndex('coupon_log_goods_id_foreign');
            $table->dropIndex('coupon_log_order_id_foreign');
        });

        Schema::table('coupon_log', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->default(0)->nullable(false)->change();
            $table->unsignedInteger('goods_id')->default(0)->nullable(false)->change();
            $table->unsignedInteger('order_id')->default(0)->nullable(false)->change();
        });

        Schema::table('email_filter', function (Blueprint $table) {
            $table->dropIndex(['words', 'type']);
        });

        Schema::table('invite', function (Blueprint $table) {
            $table->dropForeign(['inviter_id']);
            $table->dropForeign(['invitee_id']);
            $table->dropIndex('invite_inviter_id_foreign');
            $table->dropIndex('invite_invitee_id_foreign');
        });

        Schema::table('node_auth', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
            $table->dropIndex('node_auth_node_id_foreign');
        });

        Schema::table('node_daily_data_flow', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
        });

        Schema::table('node_hourly_data_flow', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
        });

        Schema::table('node_label', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
            $table->dropForeign(['label_id']);
            $table->dropIndex('node_label_label_id_foreign');
        });

        Schema::table('node_ping', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
        });

        Schema::table('node_rule', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
            $table->dropForeign(['rule_id']);
            $table->dropIndex(['node_id', 'rule_id']);
            $table->dropIndex('node_rule_rule_id_foreign');
        });

        Schema::table('order', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'order_id']);
        });

        Schema::table('referral_apply', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('referral_apply_user_id_foreign');
        });

        Schema::table('referral_log', function (Blueprint $table) {
            $table->dropForeign(['inviter_id']);
            $table->dropForeign(['invitee_id']);
            $table->dropForeign(['order_id']);
            $table->dropIndex(['inviter_id', 'invitee_id']);
            $table->dropIndex('referral_log_invitee_id_foreign');
            $table->dropIndex('referral_log_order_id_foreign');
        });

        Schema::table('referral_log', function (Blueprint $table) {
            $table->unsignedInteger('invitee_id')->nullable(false)->change();
            $table->unsignedInteger('order_id')->nullable(false)->change();
        });

        Schema::table('rule_group_node', function (Blueprint $table) {
            $table->dropForeign(['rule_group_id']);
            $table->dropForeign(['node_id']);
            $table->dropIndex('rule_group_node_rule_group_id_foreign');
            $table->dropIndex('rule_group_node_node_id_foreign');
        });

        Schema::table('rule_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['node_id']);
            $table->dropForeign(['rule_id']);
            $table->dropIndex('rule_log_node_id_foreign');
            $table->dropIndex('rule_log_rule_id_foreign');
        });

        Schema::table('rule_log', function (Blueprint $table) {
            $table->unsignedInteger('node_id')->default(0)->nullable(false)->change();
            $table->unsignedInteger('rule_id')->default(0)->nullable(false)->change();
        });

        Schema::table('ss_config', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table('ss_node', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table('ss_node_info', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
        });

        Schema::table('ss_node_ip', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('ss_node_online_log', function (Blueprint $table) {
            $table->dropForeign(['node_id']);
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['admin_id']);
            $table->dropIndex('ticket_user_id_foreign');
            $table->dropIndex('ticket_admin_id_foreign');
        });

        Schema::table('ticket', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->default(0)->nullable(false)->change();
        });

        Schema::table('ticket_reply', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['ticket_id']);
            $table->dropIndex('ticket_reply_user_id_foreign');
            $table->dropIndex('ticket_reply_admin_id_foreign');
            $table->dropIndex('ticket_reply_ticket_id_foreign');
        });

        Schema::table('ticket_reply', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->default(0)->nullable(false)->change();
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropForeign(['inviter_id']);
            $table->dropIndex('user_inviter_id_foreign');
        });

        Schema::table('user_baned_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('user_baned_log_user_id_foreign');
        });

        Schema::table('user_credit_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['order_id']);
            $table->dropIndex('user_credit_log_user_id_foreign');
            $table->dropIndex('user_credit_log_order_id_foreign');
        });

        Schema::table('user_credit_log', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->default(0)->nullable(false)->change();
        });

        Schema::table('user_daily_data_flow', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['node_id']);
            $table->dropIndex('user_daily_data_flow_node_id_foreign');
        });

        Schema::table('user_data_modify_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['order_id']);
            $table->dropIndex('user_data_modify_log_user_id_foreign');
            $table->dropIndex('user_data_modify_log_order_id_foreign');
        });

        Schema::table('user_data_modify_log', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->default(0)->nullable(false)->change();
        });

        Schema::table('user_hourly_data_flow', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['node_id']);
            $table->dropIndex('user_hourly_data_flow_node_id_foreign');
        });

        Schema::table('user_login_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('user_login_log_user_id_foreign');
        });

        Schema::table('user_subscribe', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_subscribe_log', function (Blueprint $table) {
            $table->dropForeign(['user_subscribe_id']);
        });

        Schema::table('user_traffic_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['node_id']);
            $table->dropIndex('user_traffic_log_node_id_foreign');
        });

        Schema::table('verify', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('verify_user_id_foreign');
        });
    }
}
