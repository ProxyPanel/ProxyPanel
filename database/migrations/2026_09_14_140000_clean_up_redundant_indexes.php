<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 全仓没有任何迁移创建过 idx_node_label（它来自已删除的 2020_08_21_145711_create_node_label_table，建在旧表 node_label 上），
        // 库里有没有取决于安装 lineage，因此每次删索引都先判存在，失败重放时建操作同样要判。
        // 1) label_node：idx_node_label(node_id, label_id) 与唯一键
        //    node_label_node_id_label_id_unique(node_id, label_id) 列与顺序完全相同，纯冗余。
        //    外键 node_label_node_id_foreign 仍可由该唯一键的 node_id 前缀服务。
        if (Schema::hasIndex('label_node', 'idx_node_label')) {
            Schema::table('label_node', static function (Blueprint $table) {
                $table->dropIndex('idx_node_label');
            });
        }

        // 2) user_subscribe：user_subscribe_code_index(code) 与唯一键
        //    user_subscribe_code_unique(code) 完全相同，纯冗余；code 上没有外键，唯一键已覆盖按 code 的查询。
        if (Schema::hasIndex('user_subscribe', 'user_subscribe_code_index')) {
            Schema::table('user_subscribe', static function (Blueprint $table) {
                $table->dropIndex('user_subscribe_code_index');
            });
        }

        // 3) order：重排列序，让「user_id + status」成为可用的索引区间。
        //    原来的 idx_order_search(user_id, goods_id, is_expire, status) 把 status 放在第 4 列，
        //    而所有用户维度查询都带 status（paidOrders、userPrepay、userActivePlan/Package、
        //    按 status 判断是否已有待支付订单等），is_expire 只在其中一部分出现，
        //    因此 status 提到 is_expire 之前收益最大；goods_id 全仓只有一处按它过滤，放末尾。
        //
        //    顺序不能颠倒：order 的 order_user_id_foreign 外键没有独立索引，
        //    靠的就是 idx_order_search 的 user_id 前缀，先删后建会被 MySQL 拒绝
        //    （errno 1553 Cannot drop index needed in a foreign key constraint）。
        if (! Schema::hasIndex('order', 'idx_order_user_status')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->index(['user_id', 'status', 'is_expire', 'goods_id'], 'idx_order_user_status');
            });
        }

        if (Schema::hasIndex('order', 'idx_order_search')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->dropIndex('idx_order_search');
            });
        }
    }

    public function down(): void
    {
        // 同样先建后删：重建原列序的索引之前不能把 user_id 前缀索引删掉
        if (! Schema::hasIndex('order', 'idx_order_search')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->index(['user_id', 'goods_id', 'is_expire', 'status'], 'idx_order_search');
            });
        }

        if (Schema::hasIndex('order', 'idx_order_user_status')) {
            Schema::table('order', static function (Blueprint $table) {
                $table->dropIndex('idx_order_user_status');
            });
        }

        if (! Schema::hasIndex('user_subscribe', 'user_subscribe_code_index')) {
            Schema::table('user_subscribe', static function (Blueprint $table) {
                $table->index('code', 'user_subscribe_code_index');
            });
        }

        if (! Schema::hasIndex('label_node', 'idx_node_label')) {
            Schema::table('label_node', static function (Blueprint $table) {
                $table->index(['node_id', 'label_id'], 'idx_node_label');
            });
        }
    }
};
