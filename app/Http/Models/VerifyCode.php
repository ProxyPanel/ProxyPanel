<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 注册时的激活验证码
 * Class VerifyCode
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int         $id
 * @property string      $username   用户邮箱
 * @property string      $code       验证码
 * @property int         $status     状态：0-未使用、1-已使用、2-已失效
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 最后更新时间
 * @method static Builder|VerifyCode newModelQuery()
 * @method static Builder|VerifyCode newQuery()
 * @method static Builder|VerifyCode query()
 * @method static Builder|VerifyCode whereCode($value)
 * @method static Builder|VerifyCode whereCreatedAt($value)
 * @method static Builder|VerifyCode whereId($value)
 * @method static Builder|VerifyCode whereStatus($value)
 * @method static Builder|VerifyCode whereUpdatedAt($value)
 * @method static Builder|VerifyCode whereUsername($value)
 */
class VerifyCode extends Model
{
	protected $table = 'verify_code';
	protected $primaryKey = 'id';

}