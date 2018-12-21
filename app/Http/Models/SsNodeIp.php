<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SS节点在线IP信息
 * Class SsNodeIp
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class SsNodeIp extends Model
{
    protected $table = 'ss_node_ip';
    protected $primaryKey = 'id';

    public function node()
    {
        return $this->belongsTo(SsNode::class, 'node_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'port', 'port');
    }
}