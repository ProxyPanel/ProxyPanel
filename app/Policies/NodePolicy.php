<?php

namespace App\Policies;

use App\Models\Node;
use App\Models\User;

/**
 * 节点的归属判据：用户能看到的节点才可取详情，等级与分组之外的一律不给。
 */
class NodePolicy
{
    public function view(User $user, Node $node): bool
    {
        return $user->nodes()->where('node.id', $node->getKey())->exists();
    }
}
