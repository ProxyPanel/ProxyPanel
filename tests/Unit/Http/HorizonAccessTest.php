<?php

namespace Tests\Unit\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Tests\TestCase;

/**
 * 后台打开 Horizon 面板的整条链：admin 组的 Permission、Horizon 控制器自带的 Authenticate、再到仪表盘视图。
 * Horizon 出厂的判据里带 `|| app()->environment('local')`，测试环境是 local 会白给放行，
 * 所以这里按生产语义把判据重挂一次（去掉本地兜底），否则这条用例什么都没测。
 */
class HorizonAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Horizon::auth(fn ($request) => Gate::check('viewHorizon', [$request->user()]));
    }

    public function test_super_admin_opens_the_horizon_dashboard(): void
    {
        $this->actingAs($this->superAdmin(), 'web');

        $response = $this->get('/admin/horizon');

        $response->assertStatus(200);
        $this->assertStringContainsString('<div id="horizon"', $response->getContent());
    }

    public function test_admin_without_the_permission_is_not_sent_to_the_dashboard(): void
    {
        $user = User::create(['username' => 'plain@example.com', 'passwd' => bcrypt('secret-123'), 'nickname' => 't']);

        $this->actingAs($user, 'web');

        $response = $this->get('/admin/horizon');

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString('<div id="horizon"', $response->getContent());
    }
}
