<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * 用户订阅地址请求日志
 *
 * @property int                                                              $id
 * @property int|null                                                         $sid            对应user_subscribe的id
 * @property string|null                                                      $request_ip     请求IP
 * @property string|null                                                      $request_time   请求时间
 * @property string|null                                                      $request_header 请求头部信息
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $user
 * @property-read int|null                                                    $user_count
 * @method static Builder|UserSubscribeLog newModelQuery()
 * @method static Builder|UserSubscribeLog newQuery()
 * @method static Builder|UserSubscribeLog query()
 * @method static Builder|UserSubscribeLog whereId($value)
 * @method static Builder|UserSubscribeLog whereRequestHeader($value)
 * @method static Builder|UserSubscribeLog whereRequestIp($value)
 * @method static Builder|UserSubscribeLog whereRequestTime($value)
 * @method static Builder|UserSubscribeLog whereSid($value)
 * @mixin \Eloquent
 */
class UserSubscribeLog extends Model {
	public $timestamps = false;
	protected $table = 'user_subscribe_log';

	public function user(): HasManyThrough {
		return $this->hasManyThrough(User::class, UserSubscribe::class, 'id', 'id', 'sid', 'user_id');
	}
}
