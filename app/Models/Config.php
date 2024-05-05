<?php

namespace App\Models;

use App\Observers\ConfigObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

/**
 * 系统配置.
 */
#[ObservedBy([ConfigObserver::class])]
class Config extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'config';

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $guarded = [];
}
