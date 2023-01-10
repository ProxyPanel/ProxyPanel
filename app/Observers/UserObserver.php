<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Jobs\VNet\addUser;
use App\Jobs\VNet\delUser;
use App\Jobs\VNet\editUser;
use App\Models\User;
use Arr;

class UserObserver
{
    public function created(User $user): void
    {
        $user->subscribe()->create(['code' => Helpers::makeSubscribeCode()]);

        $allowNodes = $user->nodes()->whereType(4)->get();
        if (count($allowNodes)) {
            addUser::dispatch($user->id, $allowNodes);
        }
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $allowNodes = $user->nodes()->whereType(4)->get();
        $oldAllowNodes = $user->nodes($user->getOriginal('level'), $user->getOriginal('user_group_id'))->whereType(4)->get();
        if ($allowNodes->isNotEmpty() || $oldAllowNodes->isNotEmpty()) {
            if (Arr::hasAny($changes, ['level', 'user_group_id', 'enable'])) {
                if (Arr::has($changes, 'enable')) {
                    if ($user->enable) { // TODO: 由于vnet未正确使用enable字段，临时解决方案
                        addUser::dispatch($user->id, $allowNodes);
                    } else {
                        delUser::dispatch($user->id, $allowNodes);
                    }
                } else {
                    // 权限修改，消除重叠的部分
                    $old = $oldAllowNodes->diff($allowNodes);
                    if ($oldAllowNodes->isNotEmpty() && $old->isNotEmpty()) {
                        delUser::dispatch($user->id, $old->diff($allowNodes));
                    }
                    $new = $allowNodes->diff($oldAllowNodes);
                    if ($new->isNotEmpty()) {
                        addUser::dispatch($user->id, $new);
                    }
                }
            } elseif (Arr::hasAny($changes, ['port', 'passwd', 'speed_limit'])) {
                editUser::dispatch($user, $allowNodes);
            }
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
