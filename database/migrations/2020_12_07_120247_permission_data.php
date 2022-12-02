<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('description')->after('name');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->string('description')->after('name');
        });

        Artisan::call('cache:clear');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => 'admin.aff.detail', 'description' => '【推广系统】提现申请详情', 'guard_name' => 'web'],
            ['name' => 'admin.aff.index', 'description' => '【推广系统】提现管理列表', 'guard_name' => 'web'],
            ['name' => 'admin.aff.rebate', 'description' => '【推广系统】返利流水记录', 'guard_name' => 'web'],
            ['name' => 'admin.aff.setStatus', 'description' => '【推广系统】设置提现状态', 'guard_name' => 'web'],
            ['name' => 'admin.article.create,store', 'description' => '【客服系统】新建文章', 'guard_name' => 'web'],
            ['name' => 'admin.article.destroy', 'description' => '【客服系统】删除文章', 'guard_name' => 'web'],
            ['name' => 'admin.article.edit,update', 'description' => '【客服系统】编辑文章', 'guard_name' => 'web'],
            ['name' => 'admin.article.index,show', 'description' => '【客服系统】文章列表', 'guard_name' => 'web'],
            ['name' => 'admin.config.*', 'description' => '【设置】通用配置', 'guard_name' => 'web'],
            ['name' => 'admin.coupon.create,store', 'description' => '【商品系统】新建卡劵', 'guard_name' => 'web'],
            ['name' => 'admin.coupon.destroy', 'description' => '【商品系统】删除卡劵', 'guard_name' => 'web'],
            ['name' => 'admin.coupon.export', 'description' => '【商品系统】导出卡劵', 'guard_name' => 'web'],
            ['name' => 'admin.coupon.index', 'description' => '【商品系统】卡劵列表', 'guard_name' => 'web'],
            ['name' => 'admin.goods.create,store', 'description' => '【商品系统】新建商品', 'guard_name' => 'web'],
            ['name' => 'admin.goods.destroy', 'description' => '【商品系统】删除商品', 'guard_name' => 'web'],
            ['name' => 'admin.goods.edit,update', 'description' => '【商品系统】编辑商品', 'guard_name' => 'web'],
            ['name' => 'admin.goods.index', 'description' => '【商品系统】商品列表', 'guard_name' => 'web'],
            ['name' => 'admin.index', 'description' => '【管理中心】首页', 'guard_name' => 'web'],
            ['name' => 'admin.invite.index', 'description' => '【推广系统】邀请列表', 'guard_name' => 'web'],
            ['name' => 'admin.invite.create', 'description' => '【推广系统】生成邀请码', 'guard_name' => 'web'],
            ['name' => 'admin.invite.export', 'description' => '【推广系统】导出邀请码', 'guard_name' => 'web'],
            ['name' => 'admin.log.ban', 'description' => '【日志系统】封禁记录', 'guard_name' => 'web'],
            ['name' => 'admin.log.credit', 'description' => '【日志系统】余额记录', 'guard_name' => 'web'],
            ['name' => 'admin.log.flow', 'description' => '【日志系统】流量变动记录', 'guard_name' => 'web'],
            ['name' => 'admin.log.ip', 'description' => '【日志系统】在线IP记录', 'guard_name' => 'web'],
            ['name' => 'admin.log.notify', 'description' => '【日志系统】通知记录', 'guard_name' => 'web'],
            ['name' => 'admin.log.online', 'description' => '【日志系统】在线监控', 'guard_name' => 'web'],
            ['name' => 'admin.log.traffic', 'description' => '【日志系统】流量日志', 'guard_name' => 'web'],
            ['name' => 'admin.log.viewer', 'description' => '【日志系统】运行日志', 'guard_name' => 'web'],
            ['name' => 'admin.marketing.add', 'description' => '【客服系统】推送消息', 'guard_name' => 'web'],
            ['name' => 'admin.marketing.email', 'description' => '【客服系统】邮件消息列表', 'guard_name' => 'web'],
            ['name' => 'admin.marketing.push', 'description' => '【客服系统】推送消息列表', 'guard_name' => 'web'],
            ['name' => 'admin.node.auth.destroy', 'description' => '【线路系统】删除授权', 'guard_name' => 'web'],
            ['name' => 'admin.node.auth.index', 'description' => '【线路系统】授权列表', 'guard_name' => 'web'],
            ['name' => 'admin.node.auth.store', 'description' => '【线路系统】新建授权', 'guard_name' => 'web'],
            ['name' => 'admin.node.auth.update', 'description' => '【线路系统】编辑授权', 'guard_name' => 'web'],
            ['name' => 'admin.node.cert.create,store', 'description' => '【线路系统】新建证书', 'guard_name' => 'web'],
            ['name' => 'admin.node.cert.destroy', 'description' => '【线路系统】删除证书', 'guard_name' => 'web'],
            ['name' => 'admin.node.cert.edit,update', 'description' => '【线路系统】编辑证书', 'guard_name' => 'web'],
            ['name' => 'admin.node.cert.index', 'description' => '【线路系统】证书列表', 'guard_name' => 'web'],
            ['name' => 'admin.node.check', 'description' => '【线路系统】阻断检测', 'guard_name' => 'web'],
            ['name' => 'admin.node.create,store', 'description' => '【线路系统】新建线路', 'guard_name' => 'web'],
            ['name' => 'admin.node.destroy', 'description' => '【线路系统】删除线路', 'guard_name' => 'web'],
            ['name' => 'admin.node.edit,update', 'description' => '【线路系统】编辑线路', 'guard_name' => 'web'],
            ['name' => 'admin.node.geo', 'description' => '【线路系统】更新地理', 'guard_name' => 'web'],
            ['name' => 'admin.node.index', 'description' => '【线路系统】线路列表', 'guard_name' => 'web'],
            ['name' => 'admin.node.monitor', 'description' => '【线路系统】流量监控', 'guard_name' => 'web'],
            ['name' => 'admin.node.ping', 'description' => '【线路系统】测速', 'guard_name' => 'web'],
            ['name' => 'admin.node.pingLog', 'description' => '【线路系统】测速日志', 'guard_name' => 'web'],
            ['name' => 'admin.node.reload', 'description' => '【线路系统】重载', 'guard_name' => 'web'],
            ['name' => 'admin.order', 'description' => '【商品系统】订单列表', 'guard_name' => 'web'],
            ['name' => 'admin.payment.callback', 'description' => '【日志系统】回调列表', 'guard_name' => 'web'],
            ['name' => 'admin.permission.create,store', 'description' => '【权限系统】新建权限', 'guard_name' => 'web'],
            ['name' => 'admin.permission.destroy', 'description' => '【权限系统】删除权限', 'guard_name' => 'web'],
            ['name' => 'admin.permission.edit,update', 'description' => '【权限系统】编辑权限', 'guard_name' => 'web'],
            ['name' => 'admin.permission.index', 'description' => '【权限系统】权限列表', 'guard_name' => 'web'],
            ['name' => 'admin.role.create,store', 'description' => '【权限系统】新建角色', 'guard_name' => 'web'],
            ['name' => 'admin.role.destroy', 'description' => '【权限系统】删除角色', 'guard_name' => 'web'],
            ['name' => 'admin.role.edit,update', 'description' => '【权限系统】编辑角色', 'guard_name' => 'web'],
            ['name' => 'admin.role.index', 'description' => '【权限系统】角色列表', 'guard_name' => 'web'],
            ['name' => 'admin.rule.clear', 'description' => '【审计规则】清除触发日志', 'guard_name' => 'web'],
            ['name' => 'admin.rule.destroy', 'description' => '【审计规则】删除规则', 'guard_name' => 'web'],
            ['name' => 'admin.rule.group.assign,editNode', 'description' => '【审计规则】分组关联线路', 'guard_name' => 'web'],
            ['name' => 'admin.rule.group.create,store', 'description' => '【审计规则】新建分组', 'guard_name' => 'web'],
            ['name' => 'admin.rule.group.destroy', 'description' => '【审计规则】删除分组', 'guard_name' => 'web'],
            ['name' => 'admin.rule.group.edit,update', 'description' => '【审计规则】编辑分组', 'guard_name' => 'web'],
            ['name' => 'admin.rule.group.index', 'description' => '【审计规则】分组列表', 'guard_name' => 'web'],
            ['name' => 'admin.rule.index', 'description' => '【审计规则】规则列表', 'guard_name' => 'web'],
            ['name' => 'admin.rule.log', 'description' => '【审计规则】触发日志', 'guard_name' => 'web'],
            ['name' => 'admin.rule.store', 'description' => '【审计规则】新建规则', 'guard_name' => 'web'],
            ['name' => 'admin.rule.update', 'description' => '【审计规则】编辑规则', 'guard_name' => 'web'],
            ['name' => 'admin.subscribe.index', 'description' => '【用户系统】订阅列表', 'guard_name' => 'web'],
            ['name' => 'admin.subscribe.log', 'description' => '【用户系统】订阅记录', 'guard_name' => 'web'],
            ['name' => 'admin.subscribe.set', 'description' => '【用户系统】编辑订阅状态', 'guard_name' => 'web'],
            ['name' => 'admin.system.index', 'description' => '【设置】查看系统设置', 'guard_name' => 'web'],
            ['name' => 'admin.system.update,extend', 'description' => '【设置】编辑系统设置', 'guard_name' => 'web'],
            ['name' => 'admin.test.*', 'description' => '【设置】通知，支付设置测试', 'guard_name' => 'web'],
            ['name' => 'admin.ticket.destroy', 'description' => '【客服系统】删除工单', 'guard_name' => 'web'],
            ['name' => 'admin.ticket.edit,update', 'description' => '【客服系统】回复工单', 'guard_name' => 'web'],
            ['name' => 'admin.ticket.index', 'description' => '【客服系统】工单列表', 'guard_name' => 'web'],
            ['name' => 'admin.ticket.store', 'description' => '【客服系统】新建工单', 'guard_name' => 'web'],
            ['name' => 'admin.tools.*', 'description' => '【工具箱】', 'guard_name' => 'web'],
            ['name' => 'admin.user.batch', 'description' => '【用户系统】生成用户', 'guard_name' => 'web'],
            ['name' => 'admin.user.create,store', 'description' => '【用户系统】新建用户', 'guard_name' => 'web'],
            ['name' => 'admin.user.destroy', 'description' => '【用户系统】删除用户', 'guard_name' => 'web'],
            ['name' => 'admin.user.edit,update', 'description' => '【用户系统】编辑用户', 'guard_name' => 'web'],
            ['name' => 'admin.user.export', 'description' => '【用户系统】配置信息', 'guard_name' => 'web'],
            ['name' => 'admin.user.exportProxy', 'description' => '【用户系统】读取配置', 'guard_name' => 'web'],
            ['name' => 'admin.user.group.create,store', 'description' => '【用户系统】新建分组', 'guard_name' => 'web'],
            ['name' => 'admin.user.group.destroy', 'description' => '【用户系统】删除分组', 'guard_name' => 'web'],
            ['name' => 'admin.user.group.edit,update', 'description' => '【用户系统】编辑分组', 'guard_name' => 'web'],
            ['name' => 'admin.user.group.index', 'description' => '【用户系统】分组列表', 'guard_name' => 'web'],
            ['name' => 'admin.user.index', 'description' => '【用户系统】用户列表', 'guard_name' => 'web'],
            ['name' => 'admin.user.monitor', 'description' => '【用户系统】流量统计', 'guard_name' => 'web'],
            ['name' => 'admin.user.online', 'description' => '【用户系统】在线巡查', 'guard_name' => 'web'],
            ['name' => 'admin.user.reset', 'description' => '【用户系统】流量重置', 'guard_name' => 'web'],
            ['name' => 'admin.user.switch', 'description' => '【用户系统】用户视角', 'guard_name' => 'web'],
            ['name' => 'admin.user.updateCredit', 'description' => '【用户系统】编辑余额', 'guard_name' => 'web'],
            ['name' => 'give roles', 'description' => '【用户系统】赋予角色权限', 'guard_name' => 'web'],
        ];

        Permission::insert($permissions);
        Role::create(['name' => 'Super Admin', 'description' => '超级管理员']);

        foreach (User::whereIsAdmin(1)->get() as $admin) {
            $admin->assignRole('Super Admin');
        }

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Artisan::call('cache:clear');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Schema::table('user', function (Blueprint $table) {
            $table->boolean('is_admin')->default(0)->comment('是否管理员：0-否、1-是')->after('group_id');
        });

        foreach (User::role('Super Admin')->get() as $admin) {
            $admin->is_admin = 1;
            $admin->save();
        }

        Role::query()->delete();
        Permission::query()->delete();
    }
}
