<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支付回调日志.
 */
class PaymentCallback extends Model
{
    protected $table = 'payment_callback';

    public function getStatusLabelAttribute(): string
    {
        return [
            'WAIT_BUYER_PAY'         => '等待买家付款',
            'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
            'TRADE_SUCCESS'          => '交易成功',
            'PAID'                   => '支付完成',
        ][$this->attributes['status']] ?? '';
    }
}
