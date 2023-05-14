<?php

namespace App\Models;

use App\Components\Helpers;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kyslik\ColumnSortable\Sortable;

/**
 * 订单.
 */
class Order extends Model
{
    use Sortable;

    public $sortable = ['id', 'sn', 'expired_at', 'created_at'];

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

    public function scopeRecentUnPay($query, int $minutes = 0)
    {
        if (! $minutes) {
            $minutes = config('tasks.close.orders');
        }

        return $query->whereStatus(0)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-'.$minutes.' minutes')));
    }

    public function scopeUserPrepay($query, $uid = null)
    {
        return $query->uid($uid)->whereStatus(3)->oldest();
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

    public function paid() // 支付需要确认的订单
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

    public function expired() // 预支付订单
    {
        return $this->update(['is_expire' => 1]);
    }

    public function getStatusLabelAttribute($isHtml): string
    { // 订单状态
        return $this->statusTags($this->attributes['status'], $this->attributes['is_expire']);
    }

    public function statusTags($status, $expire, $isHtml = true): string
    {
        switch ($status) {
            case -1:
                $label = trans('common.order.status.cancel');
                break;
            case 0:
                $tag = 1;
                $label = trans('common.payment.status.wait');
                break;
            case 1:
                $tag = 2;
                $label = trans('common.order.status.review');
                break;
            case 2:
                if ($this->attributes['goods_id'] === null) {
                    $label = trans('common.order.status.complete');
                } elseif ($expire) {
                    $label = trans('common.status.expire');
                } else {
                    $tag = 3;
                    $label = trans('common.order.status.ongoing');
                }
                break;
            case 3:
                $tag = 2;
                $label = trans('common.order.status.prepaid');
                break;
            default:
                $tag = 4;
                $label = trans('common.status.unknown');
        }

        if ($isHtml) {
            $label = '<span class="badge badge-'.['default', 'danger', 'info', 'success', 'warning'][$tag ?? 0].'">'.$label.'</span>';
        }

        return $label;
    }

    public function getOriginAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setOriginAmountAttribute($value)
    {
        return $this->attributes['origin_amount'] = $value * 100;
    }

    public function getOriginAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->origin_amount);
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function setAmountAttribute($value)
    {
        return $this->attributes['amount'] = $value * 100;
    }

    public function getAmountTagAttribute(): string
    {
        return Helpers::getPriceTag($this->amount);
    }

    // 支付渠道
    public function getPayTypeLabelAttribute(): string
    {
        return [
            0 => trans('common.payment.credit'),
            1 => trans('common.payment.alipay'),
            2 => trans('common.payment.qq'),
            3 => trans('common.payment.wechat'),
            4 => trans('common.payment.crypto'),
            5 => 'PayPal',
            6 => 'Stripe',
            7 => trans('common.payment.manual'),
        ][$this->attributes['pay_type']] ?? '';
    }

    // 支付图标
    public function getPayTypeIconAttribute(): string
    {
        return '/assets/images/payment/'.config('common.payment.icon')[$this->attributes['pay_type']] ?? 'coin.png';
    }

    // 支付方式
    public function getPayWayLabelAttribute(): string
    {
        return config('common.payment.labels')[$this->attributes['pay_way']] ?? '未知';
    }
}
