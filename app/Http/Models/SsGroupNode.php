<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SS分组和节点关联表
 * Class SsNodeGroup
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class SsGroupNode extends Model
{
    protected $table = 'ss_group_node';
    protected $primaryKey = 'id';
    public $timestamps = false;

}