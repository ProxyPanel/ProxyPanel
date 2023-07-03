<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息.
 */
class SsConfig extends Model
{
    public $timestamps = false;

    protected $table = 'ss_config';

    protected $guarded = [];

    public function scopeDefault(Builder $query): Builder
    {  // 筛选默认
        return $query->whereIsDefault(1);
    }

    public function scopeType(Builder $query, int $type): Builder
    { // 筛选类型
        return $query->whereType($type);
    }

    public function setDefault(): bool
    { // 设置默认
        self::type($this->type)->default()->update(['is_default' => 0]); // unset original default config

        return $this->update(['is_default' => 1]);
    }
}
