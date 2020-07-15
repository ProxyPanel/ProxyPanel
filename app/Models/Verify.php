<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 注册时的验证激活地址
 *
 * @property int                             $id
 * @property int                             $type       激活类型：1-自行激活、2-管理员激活
 * @property int                             $user_id    用户ID
 * @property string                          $token      校验token
 * @property int                             $status     状态：0-未使用、1-已使用、2-已失效
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\User|null      $user
 * @method static Builder|Verify newModelQuery()
 * @method static Builder|Verify newQuery()
 * @method static Builder|Verify query()
 * @method static Builder|Verify type($type)
 * @method static Builder|Verify whereCreatedAt($value)
 * @method static Builder|Verify whereId($value)
 * @method static Builder|Verify whereStatus($value)
 * @method static Builder|Verify whereToken($value)
 * @method static Builder|Verify whereType($value)
 * @method static Builder|Verify whereUpdatedAt($value)
 * @method static Builder|Verify whereUserId($value)
 * @mixin \Eloquent
 */
class Verify extends Model {
	protected $table = 'verify';

	// 筛选类型
	public function scopeType($query, $type) {
		return $query->whereType($type);
	}

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

}
