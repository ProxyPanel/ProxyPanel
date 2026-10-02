<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    // 订单已终结的状态：-1 已关闭、2 已完成 —— 只有这两种支付单不会再收到有意义的回调
    private const SETTLED_ORDER_STATUSES = [-1, 2];

    public function up(): void
    {
        // 历史上 trade_no 由 Str::random(8) 生成且没有唯一索引，可能已经撞号。
        // 每组保留最早一行（它的号在网关手里），其余重新发号；订单在途的不改号，改成报错让人处理。
        $duplicated = DB::table('payment')
            ->select('trade_no')
            ->groupBy('trade_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('trade_no');

        $blocked = [];

        foreach ($duplicated as $tradeNo) {
            $rows = DB::table('payment')
                ->join('order', 'order.id', '=', 'payment.order_id')
                ->where('payment.trade_no', $tradeNo)
                ->orderBy('payment.id')
                ->get(['payment.id', 'payment.order_id', 'order.status']);

            // 在 PHP 侧切片而不是 ->skip(1)：MariaDB 不接受没有 LIMIT 的 OFFSET
            foreach ($rows->slice(1) as $row) {
                if (! in_array((int) $row->status, self::SETTLED_ORDER_STATUSES, true)) {
                    $blocked[] = "payment.id={$row->id}（order.status={$row->status}）";

                    continue;
                }

                $issued = (string) Str::ulid();
                DB::table('payment')->where('id', $row->id)->update(['trade_no' => $issued]);
                Log::warning("支付单号重复：payment.id={$row->id} 的 {$tradeNo} 改为 {$issued}");
            }
        }

        if ($blocked !== []) {
            throw new RuntimeException('支付单号存在在途重复，改号会让已付款对不上支付单，无法安全加唯一索引：'
                .implode('、', $blocked).'——等这些订单终结或人工处理后重跑本迁移');
        }

        Schema::table('payment', static function (Blueprint $table) {
            $table->unique('trade_no', 'payment_trade_no_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payment', static function (Blueprint $table) {
            $table->dropUnique('payment_trade_no_unique');
        });
    }
};
