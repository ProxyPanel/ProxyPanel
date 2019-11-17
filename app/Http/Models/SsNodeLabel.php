<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 节点标签
 * Class SsNodeLabel
 *
 * @package App\Http\Models
 * @property-read Label $labelInfo
 * @mixin Eloquent
 */
class SsNodeLabel extends Model
{
	protected $table = 'ss_node_label';
	protected $primaryKey = 'id';
	public $timestamps = FALSE;

	function labelInfo()
	{
		return $this->hasOne(Label::class, 'id', 'label_id');
	}
}