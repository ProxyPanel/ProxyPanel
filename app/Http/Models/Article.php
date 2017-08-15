<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章
 * Class Node
 * @package App\Http\Models
 */
class Article extends Model
{
    protected $table = 'article';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'content',
        'is_del',
        'sort'
    ];

}