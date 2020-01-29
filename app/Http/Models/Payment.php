<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 支付单
 * Class Payment
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int             $id
 * @property string|null     $sn
 * @property int             $user_id      用户ID
 * @property int|null        $oid          本地订单ID
 * @property string|null     $order_sn     本地订单长ID
 * @property int             $pay_way      支付方式：1-微信、2-支付宝
 * @property int             $amount       金额，单位分
 * @property int             $qr_id        有赞生成的支付单ID
 * @property string|null     $qr_url       有赞生成的支付二维码URL
 * @property string|null     $qr_code      有赞生成的支付二维码图片base64
 * @property string|null     $qr_local_url 支付二维码的本地存储URL
 * @property int             $status       状态：-1-支付失败、0-等待支付、1-支付成功
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property-read mixed      $pay_way_label
 * @property-read mixed      $status_label
 * @property-read Order|null $order
 * @property-read User       $user
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment query()
 * @method static Builder|Payment uid()
 * @method static Builder|Payment whereAmount($value)
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment whereOid($value)
 * @method static Builder|Payment whereOrderSn($value)
 * @method static Builder|Payment wherePayWay($value)
 * @method static Builder|Payment whereQrCode($value)
 * @method static Builder|Payment whereQrId($value)
 * @method static Builder|Payment whereQrLocalUrl($value)
 * @method static Builder|Payment whereQrUrl($value)
 * @method static Builder|Payment whereSn($value)
 * @method static Builder|Payment whereStatus($value)
 * @method static Builder|Payment whereUpdatedAt($value)
 * @method static Builder|Payment whereUserId($value)
 */
class Payment extends Model
{
	protected $table = 'payment';
	protected $primaryKey = 'id';
	protected $appends = ['status_label'];

	function scopeUid($query)
	{
		return $query->where('user_id', Auth::user()->id);
	}

	function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	function order()
	{
		return $this->belongsTo(Order::class, 'oid', 'oid');
	}

	function getAmountAttribute($value)
	{
		return $value/100;
	}

	function setAmountAttribute($value)
	{
		return $this->attributes['amount'] = $value*100;
	}

	// 订单状态
	function getStatusLabelAttribute()
	{
		switch($this->attributes['status']){
			case -1:
				$status_label = '支付失败';
				break;
			case 1:
				$status_label = '支付成功';
				break;
			case 0:
			default:
				$status_label = '等待支付';
		}

		return $status_label;
	}

	// 支付方式
	function getPayWayLabelAttribute()
	{
		switch($this->attributes['pay_way']){
			case 1:
				$pay_way_label = '微信';
				break;
			case 2:
			default:
				$pay_way_label = '支付宝';
		}

		return $pay_way_label;
	}
}
