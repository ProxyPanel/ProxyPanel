<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SS节点信息
 * Class SsNode
 *
 * @package App\Http\Models
 */
class SsNode extends Model
{
    protected $table = 'ss_node';
    protected $primaryKey = 'id';

    public function label()
    {
        return $this->hasMany(SsNodeLabel::class, 'node_id', 'id');
    }
}