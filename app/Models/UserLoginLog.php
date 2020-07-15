<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 用户登录日志
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property string                     $ip
 * @property string                     $country
 * @property string                     $province
 * @property string                     $city
 * @property string                     $county
 * @property string                     $isp
 * @property string                     $area
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
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
 * @method static Builder|UserLoginLog whereUpdatedAt($value)
 * @method static Builder|UserLoginLog whereUserId($value)
 * @mixin \Eloquent
 */
class UserLoginLog extends Model {
	protected $table = 'user_login_log';

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
