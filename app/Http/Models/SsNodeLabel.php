<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 节点标签
 * Class SsNodeLabel
 *
 * @package App\Http\Models
 */
class SsNodeLabel extends Model
{
    protected $table = 'ss_node_label';
    protected $primaryKey = 'id';
    public $timestamps = false;
}