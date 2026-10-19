<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEY = 'node_heartbeats';

    private const DEFAULT = '-30 minutes';

    /**
     * 心跳收敛成每节点一行后，保留期判据已经没有活可干：要清的那批行不存在，节点被删除时由
     * node_heartbeat_node_id_foreign 的 CASCADE 带走，离线节点的行则由 latestHeartbeat 的时间窗口挡在读取之外。
     * 后台「清理任务」按存储的键渲染，所以键要一起摘掉，不能只留一份没人读的默认值。
     */
    public function up(): void
    {
        $clean = $this->current();

        if (! is_array($clean) || ! isset($clean[self::KEY])) {
            return; // 空库（全新安装）由 ConfigSeeder 写新的键集合，这里不动
        }

        unset($clean[self::KEY]);

        $this->store($clean);
    }

    public function down(): void
    {
        $clean = $this->current();

        if (! is_array($clean) || isset($clean[self::KEY])) {
            return;
        }

        // 按原来的阅读位置补回：紧跟 node_hourly_logs
        $restored = [];
        foreach ($clean as $key => $duration) {
            $restored[$key] = $duration;

            if ($key === 'node_hourly_logs') {
                $restored[self::KEY] = self::DEFAULT;
            }
        }

        if (! isset($restored[self::KEY])) {
            $restored[self::KEY] = self::DEFAULT;
        }

        $this->store($restored);
    }

    /**
     * @return array<string, string>|null
     */
    private function current(): ?array
    {
        $value = DB::table('config')->where('name', 'tasks_clean')->value('value');

        return json_decode((string) $value, true);
    }

    private function store(array $clean): void
    {
        DB::table('config')->where('name', 'tasks_clean')->update(['value' => json_encode($clean, JSON_UNESCAPED_UNICODE)]);

        // 裸 SQL 不走 ConfigObserver，缓存要自己清
        Cache::forget('settings');
    }
};
