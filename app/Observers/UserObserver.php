<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Jobs\VNet\addUser;
use App\Jobs\VNet\delUser;
use App\Jobs\VNet\editUser;
use App\Models\User;
use App\Models\UserSubscribe;
use Arr;

class UserObserver
{
    public function created(User $user): void
    {
        $user->subscribe()->create(['code' => Helpers::makeSubscribeCode()]);

        $allowNodes = $user->nodes()->whereType(4)->get()->pluck('id');
        if (count($allowNodes)) {
            addUser::dispatch($user->id, $allowNodes);
        }
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $allowNodes = $user->nodes()->whereType(4)->get();
        if ($allowNodes->isNotEmpty() && Arr::hasAny($changes, ['level', 'group_id', 'port', 'passwd', 'speed_limit', 'enable'])) {
            editUser::dispatch($user, $allowNodes);
        }
    }

    public function deleted(User $user): void
    {
        $allowNodes = $user->nodes()->whereType(4)->get();
        if ($allowNodes->isNotEmpty()) {
            delUser::dispatch($user->id, $allowNodes);
        }
    }
}
