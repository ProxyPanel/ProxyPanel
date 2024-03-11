<?php

namespace App\Observers;

use App\Jobs\VNet\addUser;
use App\Jobs\VNet\delUser;
use App\Jobs\VNet\editUser;
use App\Models\User;
use App\Utils\Helpers;
use Arr;

class UserObserver
{
    public function created(User $user): void
    {
        $user->subscribe()->create(['code' => Helpers::makeSubscribeCode()]);

        $allowNodes = $user->nodes()->whereType(4)->get();
        if ($allowNodes->isNotEmpty()) {
            addUser::dispatch($user->id, $allowNodes);
        }
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $enableChange = Arr::has($changes, ['enable']);
        if (($user->enable === 1 || $enableChange) && Arr::hasAny($changes, ['level', 'user_group_id', 'enable', 'port', 'passwd', 'speed_limit'])) {
            $allowNodes = $user->nodes()->whereType(4)->get();
            if (Arr::hasAny($changes, ['level', 'user_group_id'])) {
                $oldAllowNodes = $user->nodes($user->getOriginal('level'), $user->getOriginal('user_group_id'))->whereType(4)->get();
                if ($enableChange) {
                    if ($user->enable === 0 && $oldAllowNodes->isNotEmpty()) {
                        delUser::dispatch($user->id, $oldAllowNodes);
                    } elseif ($user->enable === 1 && $allowNodes->isNotEmpty()) {
                        addUser::dispatch($user->id, $allowNodes);
                    }
                } else {
                    $old = $oldAllowNodes->diff($allowNodes); //old 有 allow 没有
                    $new = $allowNodes->diff($oldAllowNodes); //allow 有 old 没有
                    if ($old->isNotEmpty()) {
                        delUser::dispatch($user->id, $old);
                    }
                    if ($new->isNotEmpty()) {
                        addUser::dispatch($user->id, $new);
                    }
                    if (Arr::hasAny($changes, ['port', 'passwd', 'speed_limit'])) {
                        $same = $allowNodes->intersect($oldAllowNodes); // 共有部分
                        if ($same->isNotEmpty()) {
                            editUser::dispatch($user, $same);
                        }
                    }
                }
            } elseif ($allowNodes->isNotEmpty()) {
                if ($enableChange) {
                    if ($user->enable === 1) { // TODO: 由于vnet未正确使用enable字段，临时解决方案
                        addUser::dispatch($user->id, $allowNodes);
                    } else {
                        delUser::dispatch($user->id, $allowNodes);
                    }
                } elseif (Arr::hasAny($changes, ['port', 'passwd', 'speed_limit'])) {
                    editUser::dispatch($user, $allowNodes);
                }
            }
        }

        if ($user->status === -1 && Arr::has($changes, ['status'])) {
            $user->invites()->whereStatus(0)->update(['status' => 2]); // 废除其名下邀请码
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
