<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支付回调日志
 */
class PaymentCallback extends Model
{
    protected $table = 'payment_callback';

    public function getStatusLabelAttribute(): string
    {
        $status_label = '';
        switch ($this->attributes['status']) {
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
