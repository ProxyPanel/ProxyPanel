<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 邮件/ServerChan发送日志
 * Class EmailLog
 *
 * @package App\Http\Models
 */
class EmailLog extends Model
{
    protected $table = 'email_log';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}