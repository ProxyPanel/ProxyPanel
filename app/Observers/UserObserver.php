<?php

namespace App\Observers;

use App\Components\Helpers;
use App\Jobs\VNet\addUser;
use App\Jobs\VNet\delUser;
use App\Jobs\VNet\editUser;
use App\Models\Node;
use App\Models\User;
use App\Models\UserSubscribe;
use Arr;
use DB;
use Exception;
use Log;

class UserObserver
{
    public function created(User $user): void
    {
        $subscribe = new UserSubscribe();
        $subscribe->user_id = $user->id;
        $subscribe->code = Helpers::makeSubscribeCode();
        $subscribe->save();

        $allowNodes = Node::userAllowNodes($user->group_id, $user->level)->whereType(4)->get();
        if ($allowNodes->isNotEmpty()) {
            addUser::dispatchAfterResponse($user->id, $allowNodes);
        }
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $allowNodes = Node::userAllowNodes($user->group_id, $user->level)->whereType(4)->get();
        if ($allowNodes->isNotEmpty() && Arr::hasAny($changes, ['level', 'group_id', 'port', 'passwd', 'speed_limit', 'enable'])) {
            editUser::dispatchAfterResponse($user, $allowNodes);
        }
    }

    public function deleting(User $user): void
    {
        try {
            DB::beginTransaction();
            // 邀请关系脱钩
            $user->invites()->delete();
            $user->invitees()->update(['inviter_id' => 0]);
            $user->commissionSettlements()->delete();
            $user->commissionLogs()->delete();

            $user->subscribe()->delete();
            $user->subscribeLogs()->delete();

            // 清理日志
            $user->orders()->delete();
            $user->payments()->delete();
            $user->onlineIpLogs()->delete();
            $user->ruleLogs()->delete();
            $user->tickets()->delete();
            $user->ticketReplies()->delete();
            $user->banedLogs()->delete();
            $user->creditLogs()->delete();
            $user->dailyDataFlows()->delete();
            $user->dataFlowLogs()->delete();
            $user->dataModifyLogs()->delete();
            $user->hourlyDataFlows()->delete();
            $user->loginLogs()->delete();
            $user->verify()->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('删除用户相关信息错误：'.$e->getMessage());
            DB::rollBack();
        }
    }

    public function deleted(User $user): void
    {
        $allowNodes = Node::userAllowNodes($user->group_id, $user->level)->whereType(4)->get();
        if ($allowNodes->isNotEmpty()) {
            delUser::dispatch($user->id, $allowNodes);
        }
    }
}
