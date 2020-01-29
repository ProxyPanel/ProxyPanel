<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 支付回调（有赞云支付）
 * Class PaymentCallback
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property string|null $client_id
 * @property string|null $yz_id
 * @property string|null $kdt_id
 * @property string|null $kdt_name
 * @property int|null    $mode
 * @property string|null $msg
 * @property int|null    $sendCount
 * @property string|null $sign
 * @property string|null $status
 * @property int|null    $test
 * @property string|null $type
 * @property string|null $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed  $status_label
 * @method static Builder|PaymentCallback newModelQuery()
 * @method static Builder|PaymentCallback newQuery()
 * @method static Builder|PaymentCallback query()
 * @method static Builder|PaymentCallback whereClientId($value)
 * @method static Builder|PaymentCallback whereCreatedAt($value)
 * @method static Builder|PaymentCallback whereId($value)
 * @method static Builder|PaymentCallback whereKdtId($value)
 * @method static Builder|PaymentCallback whereKdtName($value)
 * @method static Builder|PaymentCallback whereMode($value)
 * @method static Builder|PaymentCallback whereMsg($value)
 * @method static Builder|PaymentCallback whereSendCount($value)
 * @method static Builder|PaymentCallback whereSign($value)
 * @method static Builder|PaymentCallback whereStatus($value)
 * @method static Builder|PaymentCallback whereTest($value)
 * @method static Builder|PaymentCallback whereType($value)
 * @method static Builder|PaymentCallback whereUpdatedAt($value)
 * @method static Builder|PaymentCallback whereVersion($value)
 * @method static Builder|PaymentCallback whereYzId($value)
 */
class PaymentCallback extends Model
{
	protected $table = 'payment_callback';
	protected $primaryKey = 'id';
	protected $appends = ['status_label'];

	function getStatusLabelAttribute()
	{
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