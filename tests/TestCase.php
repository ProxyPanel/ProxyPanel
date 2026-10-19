<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * 提醒与告警的收件人代码里写死是 id=1，空库上的用例得让它真存在，否则断言是空跑。
     * 挂 DatabaseTransactions 的用例里这些写入会随事务回滚。
     */
    protected function admin(): User
    {
        return User::find(1) ?? User::withoutEvents(fn () => User::create([
            'id' => 1,
            'username' => 'admin-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => random_int(20000, 65000),
            'status' => 1,
            'enable' => 1,
        ]));
    }

    /**
     * 走后台路由的用例还要让它真的是 Super Admin：`Permission` 中间件按路由名 can()，
     * 而 AuthServiceProvider 的 Gate::before 只在无参数判据上给超管短路。
     */
    protected function superAdmin(): User
    {
        $admin = $this->admin();
        $admin->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web'])->name);

        return $admin;
    }
}
