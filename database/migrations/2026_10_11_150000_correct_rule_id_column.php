<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * rule_id 的取值只有「存在的规则 id」与 NULL 两种：白名单模式上报的 0 与「规则已被删除」都落 NULL
     * （外键只认存在的 id），所以列默认值不能是 0，注释也不能再说 0。
     */
    private const RULE_ID = "ALTER TABLE `rule_log` MODIFY `rule_id` int(10) unsigned NULL DEFAULT NULL COMMENT '规则ID，NULL表示白名单模式未命中或规则已被删除'";

    private const RULE_ID_OLD = "ALTER TABLE `rule_log` MODIFY `rule_id` int(10) unsigned NULL DEFAULT 0 COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址'";

    public function up(): void
    {
        DB::statement(self::RULE_ID);
    }

    public function down(): void
    {
        DB::statement(self::RULE_ID_OLD);
    }
};
