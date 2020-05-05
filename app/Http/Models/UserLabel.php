<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户标签
 *
 * @property-read User|null $user
 * @method static Builder|UserLabel newModelQuery()
 * @method static Builder|UserLabel newQuery()
 * @method static Builder|UserLabel query()
 * @method static Builder|UserLabel uid()
 * @mixin Eloquent
 */
class UserLabel extends Model {
	public $timestamps = false;
	protected $table = 'user_label';
	protected $primaryKey = 'id';

	function scopeUid($query) {
		return $query->whereUserId(Auth::user()->id);
	}

	function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
