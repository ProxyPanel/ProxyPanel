<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 标签.
 */
class Label extends Model
{
    public $timestamps = false;

    protected $table = 'label';

    protected $guarded = [];

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class);
    }
}
