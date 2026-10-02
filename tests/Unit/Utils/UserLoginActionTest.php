<?php

namespace Tests\Unit\Utils;

use App\Jobs\RecordUserLogin;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * 登录日志必须走队列：IP 归属地查询要访问外部接口，留在请求路径里就会拖慢登录响应。
 *
 * 这个用例只锁「是否派发」；任务里写库的字段由 App\Jobs\RecordUserLogin::handle() 负责，
 * 它需要真实数据库，本项目跑测试的环境没有。
 */
class UserLoginActionTest extends TestCase
{
    public function test_login_action_queues_the_log_instead_of_writing_it_inline(): void
    {
        Queue::fake();

        Helpers::userLoginAction((new User)->newFromBuilder(['id' => 42]), '1.2.3.4');

        Queue::assertPushed(RecordUserLogin::class, 1);
    }
}
