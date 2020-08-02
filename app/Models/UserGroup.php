<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户分组控制
 *
 * @property int         $id
 * @property string      $name  分组名称
 * @property string|null $nodes 关联的节点ID，多个用,号分隔
 * @method static Builder|UserGroup newModelQuery()
 * @method static Builder|UserGroup newQuery()
 * @method static Builder|UserGroup query()
 * @method static Builder|UserGroup whereId($value)
 * @method static Builder|UserGroup whereName($value)
 * @method static Builder|UserGroup whereNodes($value)
 * @mixin \Eloquent
 */
class UserGroup extends Model {
	public $timestamps = false;
	protected $table = 'user_group';
}
