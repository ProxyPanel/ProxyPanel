<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点标签
 * Class SsNodeLabel
 *
 * @package App\Http\Models
 * @property-read \App\Http\Models\Label $labelInfo
 * @mixin \Eloquent
 */
class SsNodeLabel extends Model
{
    protected $table = 'ss_node_label';
    protected $primaryKey = 'id';
    public $timestamps = false;

    function labelInfo()
    {
        return $this->hasOne(Label::class, 'id', 'label_id');
    }
}