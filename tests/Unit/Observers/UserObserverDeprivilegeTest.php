<?php

namespace Tests\Unit\Observers;

use App\Enums\ProxyType;
use App\Jobs\Hysteria2\DelUser as Hysteria2DelUser;
use App\Jobs\VNet\AddUser;
use App\Jobs\VNet\DelUser;
use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * 失去资格的节点不会再收到这个人，观察者必须显式摘号：掉级/换组对 VNet 派 DelUser（差集），
 * 禁用/掉权限/改凭据对 Hysteria2 派 DelUser 踢会话；权限未变时不派 VNet DelUser。
 */
class UserObserverDeprivilegeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_downgrade_dispatches_vnet_deluser_for_stale_node(): void
    {
        Queue::fake();

        $node = $this->node(ProxyType::VNET->value, 1);
        $user = $this->user(1);

        // 建号时 level=1 够得着 level=1 的节点，created 已派一次 AddUser
        Queue::assertPushed(AddUser::class, 1);

        $user->update(['level' => 0]);

        // 掉级后该节点不在「当前允许」集合，只能显式摘掉；当前集合为空不再派 AddUser
        Queue::assertPushed(DelUser::class, 1);
        Queue::assertPushed(AddUser::class, 1);
    }

    public function test_disable_kicks_hysteria2_sessions(): void
    {
        Queue::fake();

        $this->node(ProxyType::HYSTERIA2->value, 0);
        $user = $this->user(0);

        $user->update(['enable' => 0]);

        Queue::assertPushed(Hysteria2DelUser::class, 1);
    }

    public function test_config_change_without_permission_change_does_not_dispatch_vnet_deluser(): void
    {
        Queue::fake();

        $this->node(ProxyType::VNET->value, 0);
        $user = $this->user(0);
        Queue::assertPushed(AddUser::class, 1);

        // 仅改凭据：权限未变，无「失去资格」节点，不派 VNet DelUser；仍按当前状态下发
        $user->update(['passwd' => 'newpass']);

        Queue::assertNotPushed(DelUser::class);
        Queue::assertPushed(AddUser::class, 2);
    }

    private function node(int $type, int $level): Node
    {
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'obs-'.uniqid(),
            'type' => $type,
            'level' => $level,
            'status' => 1,
            'profile' => [],
        ]));
    }

    private function user(int $level): User
    {
        return User::create([
            'username' => 'obs-'.uniqid().'@example.com',
            'passwd' => bcrypt('secret-123'),
            'port' => 32000 + random_int(1, 999),
            'level' => $level,
            'enable' => 1,
            'status' => 1,
            'transfer_enable' => 10 * GiB,
        ]);
    }
}
