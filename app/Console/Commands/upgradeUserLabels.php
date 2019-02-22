<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Console\Command;

class upgradeUserLabels extends Command
{
    protected $signature = 'upgradeUserLabels';
    protected $description = '初始化用户默认标签';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        if (empty(self::$systemConfig['initial_labels_for_user'])) {
            \Log::info('初始化用户默认标签失败：系统未设置默认标签');
            exit();
        }

        $userList = User::query()->where('status', '>=', 0)->get();
        foreach ($userList as $user) {
            // 跳过已经有标签的用户
            $count = UserLabel::query()->where('user_id', $user->id)->count();
            if ($count) {
                continue;
            }

            // 给用户生成默认标签
            $this->makeUserDefaultLabels($user->id);
        }
    }

    // 生成用户默认标签
    private function makeUserDefaultLabels($userId)
    {
        $labels = explode(',', self::$systemConfig['initial_labels_for_user']);

        foreach ($labels as $vo) {
            $userLabel = new UserLabel();
            $userLabel->user_id = $userId;
            $userLabel->label_id = $vo;
            $userLabel->save();
        }
    }
}
