<?php

namespace App\Jobs\VNet;

use App\Jobs\VNet\Concerns\PushesToNodes;
use App\Models\Node;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class AddUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use PushesToNodes;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    public $backoff = [30, NodeReachability::TTL];

    private array $data;

    private Collection $nodes;

    public function __construct(array|int $userIds, Collection|Node $nodes)
    {
        if ($nodes instanceof Collection) {
            $this->nodes = $nodes;
        } else {
            $this->nodes = new Collection([$nodes]);
        }

        foreach (User::findMany($userIds) as $user) {
            $this->data[] = [
                'uid' => $user->id,
                'port' => $user->port,
                'passwd' => $user->passwd,
                'speed_limit' => $user->speed_limit_bytes,
                'enable' => $user->enable,
            ];
        }
    }

    public function handle(): void
    {
        if ($this->data === []) { // 账号都不存在了，删除流程会负责摘掉节点上的残留
            return;
        }

        // 按 uid 落状态，重投同一批不会多出一份；只有传输层没送达才让队列再来一次
        $undelivered = $this->pushToNodes($this->nodes, 'api/v2/user/add/list', '新增用户', $this->data);

        if ($undelivered !== []) {
            throw new Exception('【新增用户】未送达：'.implode(',', $undelivered));
        }
    }

    // 队列失败处理
    public function failed(Throwable $exception): void
    {
        Log::alert('【新增用户】推送异常：'.$exception->getMessage());
    }
}
