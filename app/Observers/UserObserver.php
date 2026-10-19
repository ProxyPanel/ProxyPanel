<?php

namespace App\Observers;

use App\Enums\ProxyType;
use App\Jobs\Hysteria2\DelUser as Hysteria2DelUser;
use App\Jobs\VNet\AddUser;
use App\Jobs\VNet\DelUser;
use App\Models\User;
use App\Utils\Helpers;
use Arr;

class UserObserver
{
    public function created(User $user): void
    {
        $user->subscribe()->create(['code' => Helpers::makeSubscribeCode()]);

        $allowNodes = $user->nodes()->whereType(ProxyType::VNET->value)->get();
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
            $currentAllowedNodes = $user->nodes()->whereType(ProxyType::VNET->value)->get();

            // 掉级/换组后不在「当前允许」集合里的节点，面板不会再向它下发这个人，只能显式摘掉
            if ($permissionFieldsChanged) {
                $previous = $user->nodes($user->getOriginal('level'), $user->getOriginal('user_group_id'))
                    ->whereType(ProxyType::VNET->value)
                    ->get();
                // 按ID求差集，比 Collection::diff 的模型 JSON 比较更稳
                $staleNodes = $previous->whereNotIn('id', $currentAllowedNodes->pluck('id')->all());

                if ($staleNodes->isNotEmpty()) {
                    DelUser::dispatch($user->id, $staleNodes);
                }
            }

            // 其余一律下发当前状态：enable=0 时节点按这个字段把账号摘掉，无需再分「加/删」两条路
            if ($currentAllowedNodes->isNotEmpty()) {
                AddUser::dispatch($user->id, $currentAllowedNodes);
            }

            // Hysteria2 的节点只在客户端连接时向面板鉴权，没有「在节点上增删用户」这一步，
            // 但失去资格（禁用、掉权限、改凭据）时必须踢掉已建立的会话，否则会被继续计入在线人数。
            $this->kickHysteria2Sessions($user, $enableChanged, $permissionFieldsChanged, $configFieldsChanged);
        }

        if ($user->status === -1 && Arr::has($changes, ['status'])) {
            $user->invites()->whereStatus(0)->update(['status' => 2]); // 废除其名下邀请码
        }
    }

    public function deleted(User $user): void
    {
        $allowNodes = $user->nodes()->whereType(ProxyType::VNET->value)->get();
        if ($allowNodes->isNotEmpty()) {
            DelUser::dispatch($user->id, $allowNodes);
        }

        $hysteria2Nodes = $user->nodes()->whereType(ProxyType::HYSTERIA2->value)->get();
        if ($hysteria2Nodes->isNotEmpty()) {
            Hysteria2DelUser::dispatch($user->id, $hysteria2Nodes);
        }
    }

    /**
     * 踢掉用户在 Hysteria2 节点上的会话.
     *
     * 与上面的 VNet 分支同构：被禁用时踢掉全部可用节点；权限变更时踢掉失去权限的节点；
     * 端口/密码/限速等凭据变更时踢掉权限未变的节点，让客户端用新凭据重新鉴权。
     */
    private function kickHysteria2Sessions(User $user, bool $enableChanged, bool $permissionChanged, bool $configChanged): void
    {
        $current = $user->nodes()->whereType(ProxyType::HYSTERIA2->value)->get();
        $previous = $permissionChanged
            ? $user->nodes($user->getOriginal('level'), $user->getOriginal('user_group_id'))->whereType(ProxyType::HYSTERIA2->value)->get()
            : $current;

        $targets = $current->whereIn('id', []); // 以空集合起步，保持 Eloquent Collection 类型（DelUser 构造器要求）

        if ($enableChanged && ! $user->enable) {
            $targets = $targets->merge($current);
        }

        if ($permissionChanged) { // 按ID求差集，比 Collection::diff 依赖模型 JSON 比较更稳
            $targets = $targets->merge($previous->whereNotIn('id', $current->pluck('id')->all()));
        }

        if ($configChanged) {
            $targets = $targets->merge($current->whereIn('id', $previous->pluck('id')->all()));
        }

        $targets = $targets->unique('id');

        if ($targets->isNotEmpty()) {
            Hysteria2DelUser::dispatch($user->id, $targets);
        }
    }
}
