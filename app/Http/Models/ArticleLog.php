<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章日志
 * Class ArticleLog
 * @package App\Http\Models
 */
class ArticleLog extends Model
{
    protected $table = 'article_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aid',
        'lat',
        'lng',
        'ip',
        'headers',
        'nation',
        'province',
        'city',
        'district',
        'street',
        'street_number',
        'address',
        'full',
        'is_pull',
        'status'
    ];

}