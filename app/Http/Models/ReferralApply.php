<?php

namespace App\Http\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * 返利申请
 * Class ReferralApply
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class ReferralApply extends Model
{
    protected $table = 'referral_apply';
    protected $primaryKey = 'id';

    function scopeUid($query)
    {
        return $query->where('user_id', Auth::user()->id);
    }

    function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function getBeforeAttribute($value)
    {
        return $value / 100;
    }

    function setBeforeAttribute($value)
    {
        $this->attributes['before'] = $value * 100;
    }

    function getAfterAttribute($value)
    {
        return $value / 100;
    }

    function setAfterAttribute($value)
    {
        $this->attributes['after'] = $value * 100;
    }

    function getAmountAttribute($value)
    {
        return $value / 100;
    }

    function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value * 100;
    }
}