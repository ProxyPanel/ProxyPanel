<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 注册时的激活验证码
 * Class VerifyCode
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class VerifyCode extends Model
{
    protected $table = 'verify_code';
    protected $primaryKey = 'id';

}