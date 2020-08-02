<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户登录日志
 *
 * @property int                        $id
 * @property int                        $user_id    用户ID
 * @property string                     $ip         IP地址
 * @property string                     $country    国家
 * @property string                     $province   省份
 * @property string                     $city       城市
 * @property string                     $county     郡县
 * @property string                     $isp        运营商
 * @property string                     $area       地区
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property-read \App\Models\User|null $user
 * @method static Builder|UserLoginLog newModelQuery()
 * @method static Builder|UserLoginLog newQuery()
 * @method static Builder|UserLoginLog query()
 * @method static Builder|UserLoginLog whereArea($value)
 * @method static Builder|UserLoginLog whereCity($value)
 * @method static Builder|UserLoginLog whereCountry($value)
 * @method static Builder|UserLoginLog whereCounty($value)
 * @method static Builder|UserLoginLog whereCreatedAt($value)
 * @method static Builder|UserLoginLog whereId($value)
 * @method static Builder|UserLoginLog whereIp($value)
 * @method static Builder|UserLoginLog whereIsp($value)
 * @method static Builder|UserLoginLog whereProvince($value)
 * @method static Builder|UserLoginLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserLoginLog extends Model {
	const UPDATED_AT = null;
	protected $table = 'user_login_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
