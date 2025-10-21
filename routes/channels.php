<?php

use App\Models\Node;
use App\Models\Payment;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('payment-status.{tradeNo}', static function ($user, $tradeNo) {
    // 检查订单是否属于该用户
    return $user->id === Payment::whereTradeNo($tradeNo)->first()?->user->id;
});

Broadcast::channel('node.{type}.{nodeId}', static function ($user, $type, $nodeId) {
    // 验证用户权限和节点访问权限
    if (! $user->can("admin.node.$type")) {
        return false;
    }

    // 如果是特定节点操作，验证节点存在性和访问权限
    if ($nodeId !== 'all') {
        return Node::where('id', $nodeId)->exists();
    }

    return true;
});
