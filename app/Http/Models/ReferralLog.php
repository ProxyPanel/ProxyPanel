<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 返利日志
 * Class ReferralLog
 * @package App\Http\Models
 */
class ReferralLog extends Model
{
    protected $table = 'referral_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'ref_user_id',
        'order_id',
        'amount',
        'ref_amount',
        'status'
    ];

    function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}