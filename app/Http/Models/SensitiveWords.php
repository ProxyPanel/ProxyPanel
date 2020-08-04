<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 敏感词
 * Class SensitiveWords
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class SensitiveWords extends Model
{
    protected $table = 'sensitive_words';
    protected $primaryKey = 'id';
    public $timestamps = false;
}