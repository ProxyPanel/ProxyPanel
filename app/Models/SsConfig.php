<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息.
 */
class SsConfig extends Model
{
    public $timestamps = false;

    protected $table = 'ss_config';

    protected $guarded = [];

    public function scopeDefault($query): void
    {  // 筛选默认
        $query->whereIsDefault(1);
    }

    public function scopeType($query, int $type): void
    { // 筛选类型
        $query->whereType($type);
    }

    public function setDefault(): bool
    { // 设置默认
        self::type($this->type)->default()->update(['is_default' => 0]); // unset original default config

        return $this->update(['is_default' => 1]);
    }
}
