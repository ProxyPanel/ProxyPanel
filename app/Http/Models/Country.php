<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 国家/地区
 * Class Country
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Country extends Model
{
    protected $table = 'country';
    protected $primaryKey = 'id';
    public $timestamps = false;
}