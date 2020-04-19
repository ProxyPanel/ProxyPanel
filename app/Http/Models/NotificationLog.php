<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 推送通知日志
 * Class NotificationLog
 *
 * @property int         $id
 * @property int         $type       类型：1-邮件、2-ServerChan、3-Bark
 * @property string      $address    收信地址
 * @property string      $title      标题
 * @property string      $content    内容
 * @property int         $status     状态：-1发送失败、0-等待发送、1-发送成功
 * @property string|null $error      发送失败抛出的异常信息
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @method static Builder|NotificationLog newModelQuery()
 * @method static Builder|NotificationLog newQuery()
 * @method static Builder|NotificationLog query()
 * @method static Builder|NotificationLog whereAddress($value)
 * @method static Builder|NotificationLog whereContent($value)
 * @method static Builder|NotificationLog whereCreatedAt($value)
 * @method static Builder|NotificationLog whereError($value)
 * @method static Builder|NotificationLog whereId($value)
 * @method static Builder|NotificationLog whereStatus($value)
 * @method static Builder|NotificationLog whereTitle($value)
 * @method static Builder|NotificationLog whereType($value)
 * @method static Builder|NotificationLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class NotificationLog extends Model
{
	protected $table = 'notification_log';
	protected $primaryKey = 'id';

}