<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章.
 */
class Article extends Model
{
    use SoftDeletes;

    protected $table = 'article';
    protected $dates = ['deleted_at'];
    protected $guarded = [];

    // 筛选类型
    public function scopeType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeLang($query, $language = null)
    {
        return $query->whereLanguage($language ?? app()->getLocale());
    }
}
