<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 节点标签
 */
class NodeLabel extends Model {
	public $timestamps = false;
	protected $table = 'node_label';

	public function node(): BelongsTo {
		return $this->belongsTo(Node::class);
	}

	public function label(): BelongsTo {
		return $this->belongsTo(Label::class);
	}
}
