<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品
 * Class Goods
 * @package App\Http\Models
 */
class Goods extends Model
{
    protected $table = 'goods';
    protected $primaryKey = 'id';
    protected $fillable = [
        'sku',
        'name',
        'logo',
        'traffic',
        'score',
        'type',
        'price',
        'desc',
        'days',
        'is_del',
        'status'
    ];

}