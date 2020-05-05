<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * SS分组和节点关联表
 *
 * @property int $id
 * @property int $group_id 分组ID
 * @property int $node_id  节点ID
 * @method static Builder|SsGroupNode newModelQuery()
 * @method static Builder|SsGroupNode newQuery()
 * @method static Builder|SsGroupNode query()
 * @method static Builder|SsGroupNode whereGroupId($value)
 * @method static Builder|SsGroupNode whereId($value)
 * @method static Builder|SsGroupNode whereNodeId($value)
 * @mixin Eloquent
 */
class SsGroupNode extends Model {
	public $timestamps = false;
	protected $table = 'ss_group_node';
	protected $primaryKey = 'id';

}
