<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支付回调（有赞云支付）
 * Class PaymentCallback
 *
 * @package App\Http\Models
 */
class PaymentCallback extends Model
{
    protected $table = 'payment_callback';
    protected $primaryKey = 'id';

}