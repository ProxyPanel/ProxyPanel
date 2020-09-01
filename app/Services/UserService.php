<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Auth;

class UserService {
	private static $user;

	public function __construct(User $user = null) {
		self::$user = $user?: Auth::getUser();
	}

	public function isAvailable(): bool {
		return !self::$user->ban_time && self::$user->transfer_enable && self::$user->expired_at > time();
	}

	// 添加用户余额
	public function updateCredit(float $credit): bool {
		self::$user->credit += $credit;
		return self::$user->credit >= 0 && self::$user->save();
	}

	// 添加用户流量
	public function incrementData(int $data): bool {
		self::$user->transfer_enable += $data;
		return self::$user->save();
	}

	public function isNotCompleteOrderByUserId(int $userId): bool {
		return Order::uid($userId)->whereIn('status', [0, 1])->exists();
	}

	public function trafficFetch(int $u, int $d): bool {
		self::$user->u += $u;
		self::$user->d += $d;
		return self::$user->save();
	}
}