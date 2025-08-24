<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 推送通知日志.
 */
class NotificationLog extends Model
{
    protected $table = 'notification_log';

    protected $guarded = [];

    // 通知类型
    public function getTypeLabelAttribute(): string
    {
        $type = config('common.notification.labels')[$this->type];

        return trans("admin.system.notification.channel.{$type}");
    }
}
