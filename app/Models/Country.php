<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 国家/地区.
 */
class Country extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'country';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $guarded = [];

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class, 'country_code');
    }
}
