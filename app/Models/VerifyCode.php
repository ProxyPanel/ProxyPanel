<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 注册时的激活验证码
 */
class VerifyCode extends Model
{
    protected $table = 'verify_code';

    public function scopeRecentUnused($query)
    {
        return $query->whereStatus(0)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-15 minutes')));
    }
}
