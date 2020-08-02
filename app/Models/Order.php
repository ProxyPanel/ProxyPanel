<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 订单
 *
 * @property int                             $oid
 * @property string                          $order_sn      订单编号
 * @property int                             $user_id       操作人
 * @property int                             $goods_id      商品ID
 * @property int                             $coupon_id     优惠券ID
 * @property int                             $origin_amount 订单原始总价，单位分
 * @property int                             $amount        订单总价，单位分
 * @property \Illuminate\Support\Carbon|null $expired_at    过期时间
 * @property int                             $is_expire     是否已过期：0-未过期、1-已过期
 * @property int                             $pay_type      支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal
 * @property string                          $pay_way       支付方式：balance、f2fpay、codepay、payjs、bitpayx等
 * @property int                             $status        订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成
 * @property \Illuminate\Support\Carbon      $created_at    创建时间
 * @property \Illuminate\Support\Carbon      $updated_at    最后更新时间
 * @property-read \App\Models\Coupon|null    $coupon
 * @property-read string                     $pay_type_icon
 * @property-read string                     $pay_type_label
 * @property-read string                     $pay_way_label
 * @property-read string                     $status_label
 * @property-read \App\Models\Goods|null     $goods
 * @property-read \App\Models\Payment|null   $payment
 * @property-read \App\Models\User|null      $user
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order uid()
 * @method static Builder|Order whereAmount($value)
 * @method static Builder|Order whereCouponId($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereExpiredAt($value)
 * @method static Builder|Order whereGoodsId($value)
 * @method static Builder|Order whereIsExpire($value)
 * @method static Builder|Order whereOid($value)
 * @method static Builder|Order whereOrderSn($value)
 * @method static Builder|Order whereOriginAmount($value)
 * @method static Builder|Order wherePayType($value)
 * @method static Builder|Order wherePayWay($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Model {
	protected $table = 'order';
	protected $primaryKey = 'oid';
	protected $dates = ['expired_at'];

	public function scopeUid($query) {
		return $query->whereUserId(Auth::id());
	}

	public function user(): HasOne {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function goods(): HasOne {
		return $this->hasOne(Goods::class, 'id', 'goods_id')->withTrashed();
	}

	public function coupon(): HasOne {
		return $this->hasOne(Coupon::class, 'id', 'coupon_id')->withTrashed();
	}

	public function payment(): HasOne {
		return $this->hasOne(Payment::class, 'oid', 'oid');
	}

	// 订单状态
	public function getStatusLabelAttribute(): string {
		switch($this->attributes['status']){
			case -1:
				$status_label = trans('home.invoice_status_closed');
				break;
			case 1:
				$status_label = trans('home.invoice_status_wait_confirm');
				break;
			case 2:
				$status_label = trans('home.invoice_status_payment_confirm');
				break;
			case 0:
				$status_label = trans('home.invoice_status_wait_payment');
				break;
			default:
				$status_label = 'Unknown';
		}

		return $status_label;
	}


	public function getOriginAmountAttribute($value) {
		return $value / 100;
	}

	public function setOriginAmountAttribute($value) {
		return $this->attributes['origin_amount'] = $value * 100;
	}

	public function getAmountAttribute($value) {
		return $value / 100;
	}

	public function setAmountAttribute($value) {
		return $this->attributes['amount'] = $value * 100;
	}

	// 支付渠道
	public function getPayTypeLabelAttribute(): string {
		switch($this->attributes['pay_type']){
			case 0:
				$pay_type_label = '余额';
				break;
			case 1:
				$pay_type_label = '支付宝';
				break;
			case 2:
				$pay_type_label = 'QQ';
				break;
			case 3:
				$pay_type_label = '微信';
				break;
			case 4:
				$pay_type_label = '虚拟货币';
				break;
			case 5:
				$pay_type_label = 'PayPal';
				break;
			default:
				$pay_type_label = '';
		}
		return $pay_type_label;
	}

	// 支付图标
	public function getPayTypeIconAttribute(): string {
		$base_path = '/assets/images/payment/';

		switch($this->attributes['pay_type']){
			case 1:
				$pay_type_icon = $base_path.'alipay.png';
				break;
			case 2:
				$pay_type_icon = $base_path.'qq.png';
				break;
			case 3:
				$pay_type_icon = $base_path.'wechat.png';
				break;
			case 5:
				$pay_type_icon = $base_path.'paypal.png';
				break;
			case 0:
			case 4:
			default:
				$pay_type_icon = $base_path.'coin.png';
		}
		return $pay_type_icon;
	}

	// 支付方式
	public function getPayWayLabelAttribute(): string {
		switch($this->attributes['pay_way']){
			case 'credit':
				$pay_way_label = '余额';
				break;
			case 'youzan':
				$pay_way_label = '有赞云';
				break;
			case 'f2fpay':
				$pay_way_label = '支付宝当面付';
				break;
			case 'codepay':
				$pay_way_label = '码支付';
				break;
			case 'payjs':
				$pay_way_label = 'PayJs';
				break;
			case 'bitpayx':
				$pay_way_label = '麻瓜宝';
				break;
			case 'paypal':
				$pay_way_label = 'PayPal';
				break;
			default:
				$pay_way_label = '未知';
		}

		return $pay_way_label;
	}
}
