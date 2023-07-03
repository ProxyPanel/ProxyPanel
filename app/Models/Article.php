<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章.
 */
class Article extends Model
{
    use SoftDeletes;

    protected $table = 'article';

    protected $casts = ['deleted_at' => 'datetime'];

    protected $guarded = [];

    // 筛选类型
    public function scopeType(Builder $query, int $type): Builder
    {
        return $query->whereType($type);
    }

    public function scopeLang(Builder $query, ?string $language = null): Builder
    {
        return $query->whereLanguage($language ?? app()->getLocale());
    }
}
