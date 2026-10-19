<?php

namespace Tests\Unit\Providers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * viewHorizon 判据：Horizon 的鉴权中间件按 `Gate::check('viewHorizon', [$user])` 调用，带参数即不吃超管短路，
 * 所以 gate 内部必须走无参数的 can()；`horizon.index` 权限行不存在时只回绝，面板对非超管不可用是既有 RBAC 设计。
 */
class HorizonViewGateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::where('name', 'horizon.index')->get()->each->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_passes_without_a_horizon_permission_row(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin);

        $this->assertTrue(Gate::check('viewHorizon', [$admin]));
    }

    public function test_missing_permission_row_denies(): void
    {
        $user = User::create(['username' => 'plain@example.com', 'passwd' => bcrypt('secret-123'), 'nickname' => 't']);

        $this->actingAs($user);

        $this->assertFalse(Gate::check('viewHorizon', [$user]));
    }

    public function test_permission_row_granted_directly_is_enough(): void
    {
        Permission::create(['name' => 'horizon.index', 'guard_name' => 'web']);
        $user = User::create(['username' => 'granted@example.com', 'passwd' => bcrypt('secret-123'), 'nickname' => 't']);
        $user->givePermissionTo('horizon.index');

        $this->actingAs($user);

        $this->assertTrue(Gate::check('viewHorizon', [$user]));
    }
}
