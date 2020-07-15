<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户封禁日志
 *
 * @property int                             $id
 * @property int                             $user_id     用户ID
 * @property int                             $minutes     封禁账号时长，单位分钟
 * @property string                          $description 操作描述
 * @property int                             $status      状态：0-未处理、1-已处理
 * @property \Illuminate\Support\Carbon|null $created_at  创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at  最后更新时间
 * @property-read \App\Models\User|null      $user
 * @method static Builder|UserBanLog newModelQuery()
 * @method static Builder|UserBanLog newQuery()
 * @method static Builder|UserBanLog query()
 * @method static Builder|UserBanLog whereCreatedAt($value)
 * @method static Builder|UserBanLog whereDescription($value)
 * @method static Builder|UserBanLog whereId($value)
 * @method static Builder|UserBanLog whereMinutes($value)
 * @method static Builder|UserBanLog whereStatus($value)
 * @method static Builder|UserBanLog whereUpdatedAt($value)
 * @method static Builder|UserBanLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserBanLog extends Model {
	protected $table = 'user_ban_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
