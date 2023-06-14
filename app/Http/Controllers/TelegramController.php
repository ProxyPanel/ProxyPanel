<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\UserOauth;
use App\Services\TelegramService;
use Exception;
use Illuminate\Http\Request;
use StdClass;

class TelegramController extends Controller
{
    protected $msg;

    public function webhook(Request $request): void
    {
        $this->msg = $this->getMessage($request->input());
        if (! $this->msg) {
            return;
        }
        try {
            switch ($this->msg->message_type) {
                case 'send':
                    $this->fromSend();
                    break;
                case 'reply':
                    $this->fromReply();
                    break;
            }
        } catch (Exception $e) {
            $telegramService = new TelegramService();
            $telegramService->sendMessage($this->msg->chat_id, $e->getMessage());
        }
    }

    private function getMessage(array $data)
    {
        if (! isset($data['message'])) {
            return false;
        }
        $obj = new StdClass();
        $obj->is_private = $data['message']['chat']['type'] === 'private';
        if (! isset($data['message']['text'])) {
            return false;
        }
        $text = explode(' ', $data['message']['text']);
        $obj->command = $text[0];
        $obj->args = array_slice($text, 1);
        $obj->chat_id = $data['message']['chat']['id'];
        $obj->message_id = $data['message']['message_id'];
        $obj->message_type = ! isset($data['message']['reply_to_message']['text']) ? 'send' : 'reply';
        $obj->text = $data['message']['text'];
        if ($obj->message_type === 'reply') {
            $obj->reply_text = $data['message']['reply_to_message']['text'];
        }

        return $obj;
    }

    private function fromSend(): void
    {
        switch ($this->msg->command) {
            case '/bind':
                $this->bind();
                break;
            case '/traffic':
                $this->traffic();
                break;
            case '/getLatestUrl':
                $this->getLatestUrl();
                break;
            case '/unbind':
                $this->unbind();
                break;
            default:
                $this->help();
        }
    }

    private function bind(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        if (! isset($msg->args[0])) {
            abort(500, '参数有误，请携带邮箱地址发送');
        }
        $user = User::whereUsername($msg->args[0])->first();
        if (! $user) {
            abort(500, '用户不存在');
        }
        if ($user->telegram_user_id) {
            abort(500, '该账号已经绑定了Telegram账号');
        }

        if (! $user->userAuths()->create(['type' => 'telegram', 'identifier' => $msg->chat_id])) {
            abort(500, '设置失败');
        }
        $telegramService = new TelegramService();
        $telegramService->sendMessage($msg->chat_id, '绑定成功');
    }

    private function traffic(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        $telegramService = new TelegramService();
        if (! $oauth = UserOauth::query()->where([
            'type' => 'telegram',
            'identifier' => $msg->chat_id,
        ])->first()) {
            $this->help();
            $telegramService->sendMessage($msg->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');

            return;
        }
        $user = $oauth->user;
        $transferEnable = formatBytes($user->transfer_enable);
        $up = formatBytes($user->u);
        $down = formatBytes($user->d);
        $remaining = formatBytes($user->transfer_enable - ($user->u + $user->d));
        $text = "🚥流量查询\n———————————————\n计划流量：`$transferEnable`\n已用上行：`$up`\n已用下行：`$down`\n剩余流量：`$remaining`";
        $telegramService->sendMessage($msg->chat_id, $text, 'markdown');
    }

    private function help(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        $telegramService = new TelegramService;
        $commands = [
            '/bind 订阅地址 - 绑定你的'.sysConfig('website_name').'账号',
            '/traffic - 查询流量信息',
            '/getLatestUrl - 获取最新的'.sysConfig('website_name').'网址',
            '/unbind - 解除绑定',
        ];
        $text = implode(PHP_EOL, $commands);
        $telegramService->sendMessage($msg->chat_id, "你可以使用以下命令进行操作：\n\n$text", 'markdown');
    }

    private function getLatestUrl(): void
    {
        $msg = $this->msg;
        $telegramService = new TelegramService;
        $text = sprintf(
            '%s的最新网址是：%s',
            sysConfig('website_name'),
            sysConfig('website_url')
        );
        $telegramService->sendMessage($msg->chat_id, $text, 'markdown');
    }

    private function unbind(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        $user = User::with([
            'userAuths' => function ($query) use ($msg) {
                $query->whereType('telegram')->whereIdentifier($msg->chat_id);
            },
        ])->first();

        $telegramService = new TelegramService;
        if (! $user) {
            $this->help();
            $telegramService->sendMessage($msg->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');

            return;
        }
        if (! $user->userAuths()->whereType('telegram')->whereIdentifier($msg->chat_id)->delete()) {
            abort(500, '解绑失败');
        }
        $telegramService->sendMessage($msg->chat_id, '解绑成功', 'markdown');
    }

    private function fromReply(): void
    {
        // ticket
        if (preg_match('/[#](.*)/', $this->msg->reply_text, $match)) {
            $this->replayTicket($match[1]);
        }
    }

    private function replayTicket($ticketId): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        $user = User::with([
            'userAuths' => function ($query) use ($msg) {
                $query->whereType('telegram')->whereIdentifier($msg->chat_id);
            },
        ])->first();

        if (! $user) {
            abort(500, '用户不存在');
        }
        $admin = User::role('Super Admin')->whereId($user->id)->first();
        if ($admin) {
            $ticket = Ticket::whereId($ticketId)->first();
            if (! $ticket) {
                abort(500, '工单不存在');
            }
            if ($ticket->status) {
                abort(500, '工单已关闭，无法回复');
            }
            $ticket->reply()->create(['admin_id' => $admin->id, 'content' => $msg->text]);
        }
        (new TelegramService)->sendMessage($msg->chat_id, "#`$ticketId` 的工单已回复成功", 'markdown');
    }
}
