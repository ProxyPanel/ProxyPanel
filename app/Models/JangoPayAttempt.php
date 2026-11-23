<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JangoPayAttempt extends Model
{
    public const CONFIRMATION_GRACE_MINUTES = 10;

    public const RESOLVABLE_STATES = ['reserved', 'unknown_creation', 'awaiting_payment', 'quote_unknown', 'quoted', 'manual_review'];

    protected $table = 'jangopay_attempts';

    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime', 'delivered_at' => 'datetime', 'fx_reference' => 'array'];
}
