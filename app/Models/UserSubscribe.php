<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户订阅地址
 *
 * @property int                             $id
 * @property int                             $user_id    用户ID
 * @property string|null                     $code       订阅地址唯一识别码
 * @property int                             $times      地址请求次数
 * @property int                             $status     状态：0-禁用、1-启用
 * @property int                             $ban_time   封禁时间
 * @property string                          $ban_desc   封禁理由
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @property-read \App\Models\User|null      $user
 * @method static Builder|UserSubscribe newModelQuery()
 * @method static Builder|UserSubscribe newQuery()
 * @method static Builder|UserSubscribe query()
 * @method static Builder|UserSubscribe uid()
 * @method static Builder|UserSubscribe whereBanDesc($value)
 * @method static Builder|UserSubscribe whereBanTime($value)
 * @method static Builder|UserSubscribe whereCode($value)
 * @method static Builder|UserSubscribe whereCreatedAt($value)
 * @method static Builder|UserSubscribe whereId($value)
 * @method static Builder|UserSubscribe whereStatus($value)
 * @method static Builder|UserSubscribe whereTimes($value)
 * @method static Builder|UserSubscribe whereUpdatedAt($value)
 * @method static Builder|UserSubscribe whereUserId($value)
 * @mixin \Eloquent
 */
class UserSubscribe extends Model {
	protected $table = 'user_subscribe';
	protected $primaryKey = 'id';

	function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
