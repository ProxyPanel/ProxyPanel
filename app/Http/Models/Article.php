<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章
 * Class Article
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Article extends Model
{
    use SoftDeletes;

    protected $table = 'article';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    // 筛选类型
    function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}