<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paypal支付订单
 * Class Paypal
 * @package App\Http\Models
 */
class Paypal extends Model
{
    protected $table = 'paypal';
    protected $primaryKey = 'id';
    protected $fillable = [
        'oid',
        'invoice_number',
        'items',
        'response_data',
        'error',
    ];

}