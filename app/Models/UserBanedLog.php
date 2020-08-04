<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户封禁日志
 *
 * @property int                        $id
 * @property int                        $user_id     用户ID
 * @property int                        $minutes     封禁账号时长，单位分钟
 * @property string                     $description 操作描述
 * @property int                        $status      状态：0-未处理、1-已处理
 * @property \Illuminate\Support\Carbon $created_at  创建时间
 * @property \Illuminate\Support\Carbon $updated_at  最后更新时间
 * @property-read \App\Models\User|null $user
 * @method static Builder|UserBanedLog newModelQuery()
 * @method static Builder|UserBanedLog newQuery()
 * @method static Builder|UserBanedLog query()
 * @method static Builder|UserBanedLog whereCreatedAt($value)
 * @method static Builder|UserBanedLog whereDescription($value)
 * @method static Builder|UserBanedLog whereId($value)
 * @method static Builder|UserBanedLog whereMinutes($value)
 * @method static Builder|UserBanedLog whereStatus($value)
 * @method static Builder|UserBanedLog whereUpdatedAt($value)
 * @method static Builder|UserBanedLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserBanedLog extends Model {
	protected $table = 'user_baned_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
