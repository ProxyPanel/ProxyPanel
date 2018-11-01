<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SS节点负载情况
 * Class SsNodeInfo
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class SsNodeInfo extends Model
{
    protected $table = 'ss_node_info';
    protected $primaryKey = 'id';
    public $timestamps = false;

}