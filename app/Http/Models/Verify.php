<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 注册时的验证激活地址
 *
 * @property int            $id
 * @property int            $type       激活类型：1-自行激活、2-管理员激活
 * @property int            $user_id    用户ID
 * @property string         $token      校验token
 * @property int            $status     状态：0-未使用、1-已使用、2-已失效
 * @property Carbon|null    $created_at 创建时间
 * @property Carbon|null    $updated_at 最后更新时间
 * @property-read User|null $user
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
 * @mixin Eloquent
 */
class Verify extends Model {
	protected $table = 'verify';
	protected $primaryKey = 'id';

	// 筛选类型
	function scopeType($query, $type) {
		return $query->whereType($type);
	}

	function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

}
