<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 邮件/ServerChan发送日志
 * Class EmailLog
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class EmailLog extends Model
{
    protected $table = 'email_log';
    protected $primaryKey = 'id';

}