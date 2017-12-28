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
        'group_id',
        'country_code',
        'server',
        'desc',
        'method',
        'custom_method',
        'protocol',
        'protocol_param',
        'obfs',
        'obfs_param',
        'traffic_rate',
        'bandwidth',
        'traffic',
        'monitor_url',
        'compatible',
        'single',
        'single_force',
        'single_port',
        'single_passwd',
        'single_method',
        'single_protocol',
        'single_obfs',
        'sort',
        'status'
    ];

}