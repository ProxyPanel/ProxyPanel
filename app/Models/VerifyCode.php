<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 注册时的激活验证码
 *
 * @property int                        $id
 * @property string                     $address    用户邮箱
 * @property string                     $code       验证码
 * @property int                        $status     状态：0-未使用、1-已使用、2-已失效
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 最后更新时间
 * @method static Builder|VerifyCode newModelQuery()
 * @method static Builder|VerifyCode newQuery()
 * @method static Builder|VerifyCode query()
 * @method static Builder|VerifyCode whereAddress($value)
 * @method static Builder|VerifyCode whereCode($value)
 * @method static Builder|VerifyCode whereCreatedAt($value)
 * @method static Builder|VerifyCode whereId($value)
 * @method static Builder|VerifyCode whereStatus($value)
 * @method static Builder|VerifyCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VerifyCode extends Model {
	protected $table = 'verify_code';
}
