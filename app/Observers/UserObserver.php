<?php

namespace App\Observers;

use App\Jobs\VNet\AddUser;
use App\Jobs\VNet\DelUser;
use App\Jobs\VNet\EditUser;
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
            AddUser::dispatch($user->id, $allowNodes);
        }
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $enableChanged = Arr::has($changes, 'enable');
        $permissionFieldsChanged = Arr::hasAny($changes, ['level', 'user_group_id']);
        $configFieldsChanged = Arr::hasAny($changes, ['port', 'passwd', 'speed_limit']);

        // 如果enable状态发生变化，或者用户当前已启用且权限或配置字段发生变化
        if ($enableChanged || ($user->enable === 1 && ($permissionFieldsChanged || $configFieldsChanged))) {
            // 获取当前允许的节点
            $currentAllowedNodes = $user->nodes()->whereType(4)->get();

            if ($permissionFieldsChanged) {
                $oldAllowedNodes = $user->nodes($user->getOriginal('level'), $user->getOriginal('user_group_id'))->whereType(4)->get();
                if ($enableChanged) {
                    if ($user->enable) {
                        // 用户被启用，添加到所有当前允许的节点
                        if ($currentAllowedNodes->isNotEmpty()) {
                            AddUser::dispatch($user->id, $currentAllowedNodes);
                        }
                    } elseif ($oldAllowedNodes->isNotEmpty()) {
                        DelUser::dispatch($user->id, $oldAllowedNodes);
                    }
                } else {
                    // 计算差异
                    $nodesToRemove = $oldAllowedNodes->diff($currentAllowedNodes); // 用户失去权限的节点
                    $nodesToAdd = $currentAllowedNodes->diff($oldAllowedNodes); // 用户新增权限的节点

                    // 处理节点移除
                    if ($nodesToRemove->isNotEmpty()) {
                        DelUser::dispatch($user->id, $nodesToRemove);
                    }

                    // 处理节点添加
                    if ($nodesToAdd->isNotEmpty()) {
                        AddUser::dispatch($user->id, $nodesToAdd);
                    }

                    // 处理节点更新（权限未变但配置变了）
                    if ($configFieldsChanged && $currentAllowedNodes->isNotEmpty()) {
                        $nodesToUpdate = $currentAllowedNodes->intersect($oldAllowedNodes); // 权限未变但可能需要更新配置的节点
                        if ($nodesToUpdate->isNotEmpty()) {
                            EditUser::dispatch($user, $nodesToUpdate);
                        }
                    }
                }
            } elseif ($enableChanged && $currentAllowedNodes->isNotEmpty()) {
                // 启用状态变化处理
                if ($user->enable) {
                    // 用户被启用，添加到所有允许的节点
                    AddUser::dispatch($user->id, $currentAllowedNodes);
                } else {
                    // 用户被禁用，从所有允许的节点中移除
                    DelUser::dispatch($user->id, $currentAllowedNodes);
                }
            } elseif ($configFieldsChanged && $currentAllowedNodes->isNotEmpty()) {
                // 仅配置变化，更新所有允许的节点
                EditUser::dispatch($user, $currentAllowedNodes);
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
            DelUser::dispatch($user->id, $allowNodes);
        }
    }
}
