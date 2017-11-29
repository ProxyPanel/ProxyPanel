<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'user_payment';
    public function user(){
        return $this->belongsTo("App\Http\Models\User");
    }
}
