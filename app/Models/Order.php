<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 订单.
 */
class Order extends Model
{
    protected $table = 'order';
    protected $dates = ['expired_at'];
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class)->withTrashed();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeUid($query, $uid = null)
    {
        return $query->whereUserId($uid ?: Auth::id());
    }

    public function scopeRecentUnPay($query)
    {
        return $query->whereStatus(0)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-15 minutes')));
    }

    public function scopeUserPrepay($query, $uid = null)
    {
        return $query->uid($uid)->whereStatus(3);
    }

    public function scopeActive($query)
    {
        return $query->whereIsExpire(0)->whereStatus(2);
    }

    public function scopeActivePlan($query)
    {
        return $query->active()->with('goods')->whereHas('goods', static function ($query) {
            $query->whereType(2);
        });
    }

    public function scopeActivePackage($query)
    {
        return $query->active()->with('goods')->whereHas('goods', static function ($query) {
            $query->whereType(1);
        });
    }

    public function scopeUserActivePlan($query, $uid = null)
    {
        return $query->uid($uid)->activePlan();
    }

    public function scopeUserActivePackage($query, $uid = null)
    {
        return $query->uid($uid)->activePackage();
    }

    public function close() // 关闭订单
    {
        return $this->update(['status' => -1]);
    }

    public function paid() // 完成订单
    {
        return $this->update(['status' => 1]);
    }

    public function complete() // 完成订单
    {
        return $this->update(['status' => 2]);
    }

    public function prepay() // 预支付订单
    {
        return $this->update(['status' => 3]);
    }

    // 订单状态
    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
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

    public function getOriginAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setOriginAmountAttribute($value)
    {
        return $this->attributes['origin_amount'] = $value * 100;
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }

    // 支付渠道
    public function getPayTypeLabelAttribute(): string
    {
        switch ($this->attributes['pay_type']) {
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
            case 6:
                $pay_type_label = 'Stripe';
                break;
            case 7:
                $pay_type_label = 'PayBeaver';
                break;
            default:
                $pay_type_label = '';
        }

        return $pay_type_label;
    }

    // 支付图标
    public function getPayTypeIconAttribute(): string
    {
        $base_path = '/assets/images/payment/';

        switch ($this->attributes['pay_type']) {
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
            case 6:
                $pay_type_icon = $base_path.'stripe.png';
                break;
            default:
                $pay_type_icon = $base_path.'coin.png';
        }

        return $pay_type_icon;
    }

    // 支付方式
    public function getPayWayLabelAttribute(): string
    {
        switch ($this->attributes['pay_way']) {
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
            case 'stripe':
                $pay_way_label = 'Stripe';
                break;
            case 'paybeaver':
                $pay_way_label = '海狸支付';
                break;
            default:
                $pay_way_label = '未知';
        }

        return $pay_way_label;
    }
}
