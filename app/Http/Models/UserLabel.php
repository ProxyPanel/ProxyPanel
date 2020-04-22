<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户标签
 * Class UserLabel
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int       $id
 * @property int       $user_id  用户ID
 * @property int       $label_id 标签ID
 * @property-read User $user
 * @method static Builder|UserLabel newModelQuery()
 * @method static Builder|UserLabel newQuery()
 * @method static Builder|UserLabel query()
 * @method static Builder|UserLabel uid()
 * @method static Builder|UserLabel whereId($value)
 * @method static Builder|UserLabel whereLabelId($value)
 * @method static Builder|UserLabel whereUserId($value)
 */
class UserLabel extends Model
{
	public $timestamps = FALSE;
	protected $table = 'user_label';
	protected $primaryKey = 'id';

	function scopeUid($query)
	{
		return $query->whereUserId(Auth::user()->id);
	}

	function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}