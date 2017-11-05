<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 返利申请
 * Class ReferralApply
 * @package App\Http\Models
 */
class ReferralApply extends Model
{
    protected $table = 'referral_apply';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'before',
        'after',
        'amount',
        'link_logs',
        'status'
    ];

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}