<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 用户信息
 *
 * @property int                                                                                                            $id
 * @property string                                                                                                         $username        昵称
 * @property string                                                                                                         $email           邮箱
 * @property string                                                                                                         $password        密码
 * @property int                                                                                                            $port            代理端口
 * @property string                                                                                                         $passwd          代理密码
 * @property string                                                                                                         $vmess_id
 * @property int                                                                                                            $transfer_enable 可用流量，单位字节，默认1TiB
 * @property int                                                                                                            $u               已上传流量，单位字节
 * @property int                                                                                                            $d               已下载流量，单位字节
 * @property int|null                                                                                                       $t               最后使用时间
 * @property string|null                                                                                                    $ip              最后连接IP
 * @property int                                                                                                            $enable          代理状态
 * @property string                                                                                                         $method          加密方式
 * @property string                                                                                                         $protocol        协议
 * @property string|null                                                                                                    $protocol_param  协议参数
 * @property string                                                                                                         $obfs            混淆
 * @property int                                                                                                            $speed_limit     用户限速，为0表示不限速，单位Byte
 * @property string|null                                                                                                    $wechat          微信
 * @property string|null                                                                                                    $qq              QQ
 * @property int                                                                                                            $credit          余额，单位分
 * @property mixed|null                                                                                                     $enable_time     开通日期
 * @property mixed                                                                                                          $expire_time     过期时间
 * @property int|null                                                                                                       $ban_time        封禁到期时间
 * @property string|null                                                                                                    $remark          备注
 * @property int                                                                                                            $level           等级，默认0级
 * @property int                                                                                                            $group_id        所属分组
 * @property int                                                                                                            $is_admin        是否管理员：0-否、1-是
 * @property string                                                                                                         $reg_ip          注册IP
 * @property int                                                                                                            $last_login      最后登录时间
 * @property int                                                                                                            $referral_uid    邀请人
 * @property mixed|null                                                                                                     $reset_time      流量重置日期
 * @property int                                                                                                            $invite_num      可生成邀请码数
 * @property int                                                                                                            $status          状态：-1-禁用、0-未激活、1-正常
 * @property string|null                                                                                                    $remember_token
 * @property \Illuminate\Support\Carbon                                                                                     $created_at      创建时间
 * @property \Illuminate\Support\Carbon                                                                                     $updated_at      最后更新时间
 * @property-read string                                                                                                    $level_name
 * @property-read \App\Models\UserGroup|null                                                                                $group
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null                                                                                                  $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[]                                            $payment
 * @property-read int|null                                                                                                  $payment_count
 * @property-read \App\Models\User|null                                                                                     $referral
 * @property-read \App\Models\UserSubscribe|null                                                                            $subscribe
 * @method static Builder|User activeUser()
 * @method static Builder|User groupUserPermit($node_id = 0)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User uid()
 * @method static Builder|User whereBanTime($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCredit($value)
 * @method static Builder|User whereD($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEnable($value)
 * @method static Builder|User whereEnableTime($value)
 * @method static Builder|User whereExpireTime($value)
 * @method static Builder|User whereGroupId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereInviteNum($value)
 * @method static Builder|User whereIp($value)
 * @method static Builder|User whereIsAdmin($value)
 * @method static Builder|User whereLastLogin($value)
 * @method static Builder|User whereLevel($value)
 * @method static Builder|User whereMethod($value)
 * @method static Builder|User whereObfs($value)
 * @method static Builder|User wherePasswd($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePort($value)
 * @method static Builder|User whereProtocol($value)
 * @method static Builder|User whereProtocolParam($value)
 * @method static Builder|User whereQq($value)
 * @method static Builder|User whereReferralUid($value)
 * @method static Builder|User whereRegIp($value)
 * @method static Builder|User whereRemark($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereResetTime($value)
 * @method static Builder|User whereSpeedLimit($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereT($value)
 * @method static Builder|User whereTransferEnable($value)
 * @method static Builder|User whereU($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereVmessId($value)
 * @method static Builder|User whereWechat($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable {
	use Notifiable;

	protected $table = 'user';

	protected $dates = ['enable_time', 'expire_time', 'reset_time'];
	protected $casts = [
		'enable_time' => 'date:Y-m-d',
		'expire_time' => 'date:Y-m-d',
		'reset_time'  => 'date:Y-m-d',
	];

	public function scopeUid($query) {
		return $query->whereId(Auth::id());
	}

	public function payment(): HasMany {
		return $this->hasMany(Payment::class, 'user_id', 'id');
	}

	public function getLevelNameAttribute(): string {
		return Level::whereLevel($this->attributes['level'])->first()->name;
	}

	public function group(): HasOne {
		return $this->hasOne(UserGroup::class, 'id', 'group_id');
	}

	public function subscribe(): HasOne {
		return $this->hasOne(UserSubscribe::class, 'user_id', 'id');
	}

	public function referral(): HasOne {
		return $this->hasOne(__CLASS__, 'id', 'referral_uid');
	}

	public function getCreditAttribute($value) {
		return $value / 100;
	}

	public function setCreditAttribute($value) {
		return $this->attributes['credit'] = $value * 100;
	}

	// User查询，查那些用户有传入Node的权限
	public function scopeGroupUserPermit($query, $node_id = 0) {
		$groups = [0];
		if($node_id){
			foreach(UserGroup::all() as $userGroup){
				$nodes = explode(',', $userGroup->nodes);
				if(in_array($node_id, $nodes, true)){
					$groups[] = $userGroup->id;
				}
			}
		}
		return $query->whereIn('group_id', $groups);
	}

	public function scopeActiveUser($query) {
		return $query->where('status', '<>', -1)->whereEnable(1);
	}
}
