<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $value = DB::table('config')->where('name', 'tasks_clean')->value('value');
        $clean = json_decode((string) $value, true);
        // 这行缺失或坏掉时不动它，TaskMonthly 会退回默认值
        if (! is_array($clean) || isset($clean['payment_callbacks'])) {
            return;
        }

        // 补进 payments 之后，与控制台的阅读顺序保持一致
        $merged = [];
        foreach ($clean as $key => $duration) {
            $merged[$key] = $duration;
            if ($key === 'payments') {
                $merged['payment_callbacks'] = '-6 months';
            }
        }
        if (! isset($merged['payment_callbacks'])) {
            $merged['payment_callbacks'] = '-6 months';
        }

        DB::table('config')->where('name', 'tasks_clean')->update(['value' => json_encode($merged, JSON_UNESCAPED_UNICODE)]);

        // 裸 SQL 不走 ConfigObserver，缓存要自己清
        Cache::forget('settings');
    }

    public function down(): void
    {
        $value = DB::table('config')->where('name', 'tasks_clean')->value('value');
        $clean = json_decode((string) $value, true);
        if (! is_array($clean)) {
            return;
        }

        unset($clean['payment_callbacks']);

        DB::table('config')->where('name', 'tasks_clean')->update(['value' => json_encode($clean, JSON_UNESCAPED_UNICODE)]);

        Cache::forget('settings');
    }
};
