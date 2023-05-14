<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 标签.
 */
class Label extends Model
{
    public $timestamps = false;

    protected $table = 'label';

    protected $guarded = [];

    public function nodes()
    {
        return $this->belongsToMany(Node::class);
    }
}
