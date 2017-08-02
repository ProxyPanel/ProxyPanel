<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SS节点信息
 * Class SsNode
 * @package App\Http\Models
 */
class SsNode extends Model
{
    protected $table = 'ss_node';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'server',
        'method',
        'custom_method',
        'protocol',
        'protocol_param',
        'obfs',
        'obfs_param',
        'traffic_rate',
        'bandwidth',
        'transfer',
        'sort',
        'status'
    ];

}