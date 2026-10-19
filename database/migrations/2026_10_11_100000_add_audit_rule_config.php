<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /** 审计规则处置的两个可调项：封禁阈值 0 = 只记录不封禁，记录冷却默认 30 分钟 */
    private static array $defaults = ['rule_ban_times' => 0, 'rule_log_cooldown' => 30];

    public function up(): void
    {
        // 空库（全新安装）留白：紧接着的 db:seed 由 ConfigSeeder 逐键 insert，这里先插会撞主键
        if (! Config::exists()) {
            return;
        }

        $rows = [];
        foreach (self::$defaults as $name => $value) {
            $rows[] = ['name' => $name, 'value' => (string) $value];
        }
        Config::insertOrIgnore($rows);

        Cache::forget('settings'); // 不经模型的写入不会触发 ConfigObserver，缓存自己清
    }

    public function down(): void
    {
        Config::whereIn('name', array_keys(self::$defaults))->delete();

        Cache::forget('settings');
    }
};
