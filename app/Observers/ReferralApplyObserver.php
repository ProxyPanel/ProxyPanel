<?php

namespace App\Observers;

use Cache;

class ReferralApplyObserver
{
    /**
     * Handle the ReferralApply "created" event.
     */
    public function created(): void
    {
        Cache::forget('open_referral_apply_count');
    }

    /**
     * Handle the ReferralApply "updated" event.
     */
    public function updated(): void
    {
        Cache::forget('open_referral_apply_count');
    }
}
