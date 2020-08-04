<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支付回调（有赞云支付）
 * Class PaymentCallback
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class PaymentCallback extends Model
{
    protected $table = 'payment_callback';
    protected $primaryKey = 'id';
    protected $appends = ['status_label'];

    function getStatusLabelAttribute()
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