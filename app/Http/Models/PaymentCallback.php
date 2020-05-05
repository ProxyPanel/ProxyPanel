<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 支付回调日志
 *
 * @property int         $id
 * @property string|null $trade_no     本地订单号
 * @property string|null $out_trade_no 外部订单号（支付平台）
 * @property int|null    $amount       交易金额，单位分
 * @property int|null    $status       交易状态：0-失败、1-成功
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed  $status_label
 * @method static Builder|PaymentCallback newModelQuery()
 * @method static Builder|PaymentCallback newQuery()
 * @method static Builder|PaymentCallback query()
 * @method static Builder|PaymentCallback whereAmount($value)
 * @method static Builder|PaymentCallback whereCreatedAt($value)
 * @method static Builder|PaymentCallback whereId($value)
 * @method static Builder|PaymentCallback whereOutTradeNo($value)
 * @method static Builder|PaymentCallback whereStatus($value)
 * @method static Builder|PaymentCallback whereTradeNo($value)
 * @method static Builder|PaymentCallback whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PaymentCallback extends Model {
	protected $table = 'payment_callback';
	protected $primaryKey = 'id';
	protected $appends = ['status_label'];

	function getStatusLabelAttribute() {
		$status_label = '';
		switch($this->attributes['status']){
			case 'WAIT_BUYER_PAY':
				$status_label = '等待买家付款';
				break;
			case 'WAIT_SELLER_SEND_GOODS':
				$status_label = '等待卖家发货';
				break;
			case 'TRADE_SUCCESS':
				$status_label = '交易成功';
				break;
			case 'PAID':
				$status_label = '支付完成';
				break;
		}

		return $status_label;
	}
}
