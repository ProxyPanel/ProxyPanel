<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章
 * Class Article
 * @package App\Http\Models
 */
class Article extends Model
{
    protected $table = 'article';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'author',
        'content',
        'is_del',
        'type',
        'sort'
    ];

}