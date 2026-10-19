<?php

namespace Tests\Unit\Models;

use App\Models\Node;
use PHPUnit\Framework\TestCase;

/**
 * 单端口的判据只允许有一处：节点 payload、订阅下发、表单预填、列表徽标必须取同一个值。
 * 后台关掉单端口时表单仍会提交 passwd=''（collectFormData 不去空值），
 * 用 isset() 判会把 '' 当成「已填」，节点进单端口模式而订阅仍按每用户端口下发。
 */
class NodeSinglePortTest extends TestCase
{
    public function test_missing_or_empty_profile_password_is_not_single_port(): void
    {
        $this->assertFalse($this->node([])->isSinglePort());
        $this->assertFalse($this->node(['passwd' => null])->isSinglePort());
        $this->assertFalse($this->node(['passwd' => ''])->isSinglePort());
    }

    public function test_filled_profile_password_is_single_port(): void
    {
        $this->assertTrue($this->node(['passwd' => 'secret'])->isSinglePort());
        // '0' 是合法密码，不能因为空判据把它当成「没填」
        $this->assertTrue($this->node(['passwd' => '0'])->isSinglePort());
    }

    public function test_other_profile_keys_do_not_affect_the_judgement(): void
    {
        $this->assertFalse($this->node(['method' => 'aes-256-cfb', 'protocol' => 'origin', 'obfs' => 'plain'])->isSinglePort());
    }

    private function node(array $profile): Node
    {
        return (new Node)->newFromBuilder(['type' => 4, 'profile' => json_encode($profile)]);
    }
}
