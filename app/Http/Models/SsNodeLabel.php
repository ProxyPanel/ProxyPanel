<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点标签
 * Class SsNodeLabel
 *
 * @package App\Http\Models
 * @property-read Label $labelInfo
 * @mixin Eloquent
 * @property int        $id
 * @property int        $node_id  用户ID
 * @property int        $label_id 标签ID
 * @method static Builder|SsNodeLabel newModelQuery()
 * @method static Builder|SsNodeLabel newQuery()
 * @method static Builder|SsNodeLabel query()
 * @method static Builder|SsNodeLabel whereId($value)
 * @method static Builder|SsNodeLabel whereLabelId($value)
 * @method static Builder|SsNodeLabel whereNodeId($value)
 */
class SsNodeLabel extends Model
{
	public $timestamps = FALSE;
	protected $table = 'ss_node_label';
	protected $primaryKey = 'id';

	function labelInfo()
	{
		return $this->hasOne(Label::class, 'id', 'label_id');
	}
}