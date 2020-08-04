<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点标签
 *
 * @property int                         $id
 * @property int                         $node_id  节点ID
 * @property int                         $label_id 标签ID
 * @property-read \App\Models\Label|null $labelInfo
 * @method static Builder|NodeLabel newModelQuery()
 * @method static Builder|NodeLabel newQuery()
 * @method static Builder|NodeLabel query()
 * @method static Builder|NodeLabel whereId($value)
 * @method static Builder|NodeLabel whereLabelId($value)
 * @method static Builder|NodeLabel whereNodeId($value)
 * @mixin \Eloquent
 */
class NodeLabel extends Model {
	public $timestamps = false;
	protected $table = 'node_label';

	public function labelInfo(): HasOne {
		return $this->hasOne(Label::class, 'id', 'label_id');
	}
}
