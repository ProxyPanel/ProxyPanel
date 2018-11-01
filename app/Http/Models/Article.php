<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章
 * Class Article
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Article extends Model
{
    protected $table = 'article';
    protected $primaryKey = 'id';

}