<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * user_traffic_log 的单轮上报增量按字节存，上限要与 user.u / user.d 及各数据流表一致（bigint unsigned）：
 * 一轮 2 GiB 以上的增量落到窄列会被静默截断（部署与默认的 sql_mode 为空，越界写不报错）。
 */
return new class extends Migration
{
    private const UPLOAD = "ALTER TABLE `user_traffic_log` MODIFY `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量'";

    private const DOWNLOAD = "ALTER TABLE `user_traffic_log` MODIFY `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量'";

    private const UPLOAD_NARROW = "ALTER TABLE `user_traffic_log` MODIFY `u` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量'";

    private const DOWNLOAD_NARROW = "ALTER TABLE `user_traffic_log` MODIFY `d` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量'";

    public function up(): void
    {
        DB::statement(self::UPLOAD);
        DB::statement(self::DOWNLOAD);
    }

    public function down(): void
    {
        // 收回窄列会截断已经落库的大值：结构可回退，数据不回退
        DB::statement(self::UPLOAD_NARROW);
        DB::statement(self::DOWNLOAD_NARROW);
    }
};
