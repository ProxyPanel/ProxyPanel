<?php

namespace App\Observers;

use App\Jobs\VNet\reloadNode;
use App\Models\Node;
use App\Models\UserGroup;
use Arr;

class UserGroupObserver
{
    public function created(UserGroup $userGroup): void
    {
        $nodes = Node::whereType(4)->whereIn('id', $userGroup->nodes)->get();
        if ($nodes->isNotEmpty()) {
            reloadNode::dispatch($nodes);
        }
    }

    public function updated(UserGroup $userGroup): void
    {
        $changes = $userGroup->getChanges();
        if (Arr::has($changes, 'nodes')) {
            $nodes = Node::whereType(4)
                ->whereIn('id', array_diff($userGroup->nodes ?? [], $userGroup->getOriginal('nodes') ?? []))
                ->get();
            if ($nodes->isNotEmpty()) {
                reloadNode::dispatch($nodes);
            }
        }
    }
}
