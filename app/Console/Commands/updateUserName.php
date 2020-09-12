<?php

namespace App\Console\Commands;

use App\Components\QQInfo;
use App\Models\User;
use Illuminate\Console\Command;
use Log;

class updateUserName extends Command
{

    protected $signature = 'updateUserName';
    protected $description = '升级用户昵称';

    public function handle(): void
    {
        Log::info(
            '----------------------------【升级用户昵称】开始----------------------------'
        );

        $userList = User::all();
        foreach ($userList as $user) {
            $name = process($user->id);
            $user->update(['username' => $name]);

            Log::info(
                '---用户[ID：' . $user->id . ' - ' . $user->email . '] :' . $user->username . '---'
            );
        }

        foreach ($userList as $user) {
            if ($user->email == $user->username) {
                $name = process($user->id);

                $user->update(['username' => $name]);

                Log::info(
                    '---用户[ID：' . $user->id . ' - ' . $user->email . '] :' . $user->username . '---'
                );
            }
        }

        Log::info(
            '----------------------------【升级用户昵称】结束----------------------------'
        );
    }

}

function process($id)
{
    $user = User::find($id);
    // 先设个默认值
    $name = $user->email;
    // 用户是否设置了QQ号
    if ($user->qq) {
        $name = QQInfo::getName3($user->qq);
        // 检测用户注册是否为QQ邮箱
    } elseif (stripos($user->email, '@qq') !== false) {
        // 分离QQ邮箱后缀
        $email = explode('@', $user->email, 2);
        if (is_numeric($email[0])) {
            $name = QQInfo::getName3($email[0]);
        } elseif (str_contains($email[0], '.')) {
            $temp = explode('.', $email[0]);
            if (is_numeric($temp[1])) {
                $name = QQInfo::getName3($temp[1]);
            } else {
                echo $user->email . PHP_EOL;
            }
        }
    }
    if ($name == false) {
        $name = $user->email;
    }

    return $name;
}
