<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 邮件/ServerChan发送日志
 * Class EmailLog
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property int         $type       类型：1-邮件、2-serverChan
 * @property string      $address    收信地址
 * @property string      $title      标题
 * @property string      $content    内容
 * @property int         $status     状态：-1发送失败、0-等待发送、1-发送成功
 * @property string|null $error      发送失败抛出的异常信息
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @method static Builder|EmailLog newModelQuery()
 * @method static Builder|EmailLog newQuery()
 * @method static Builder|EmailLog query()
 * @method static Builder|EmailLog whereAddress($value)
 * @method static Builder|EmailLog whereContent($value)
 * @method static Builder|EmailLog whereCreatedAt($value)
 * @method static Builder|EmailLog whereError($value)
 * @method static Builder|EmailLog whereId($value)
 * @method static Builder|EmailLog whereStatus($value)
 * @method static Builder|EmailLog whereTitle($value)
 * @method static Builder|EmailLog whereType($value)
 * @method static Builder|EmailLog whereUpdatedAt($value)
 */
class EmailLog extends Model
{
	protected $table = 'email_log';
	protected $primaryKey = 'id';

}