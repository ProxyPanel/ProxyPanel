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
            $telegramService = new TelegramService;
            $telegramService->sendMessage($this->msg->chat_id, $e->getMessage());
        }
    }

    private function getMessage(array $data)
    {
        if (! isset($data['message'])) {
            return false;
        }
        $obj = new StdClass;
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
        $commands = [
            '/bind' => 'bind',
            '/traffic' => 'traffic',
            '/url' => 'getUrl',
            '/unbind' => 'unbind',
        ];

        $command = $this->msg->command;

        if (isset($commands[$command])) {
            $this->{$commands[$command]}();
        } else {
            $this->help();
        }
    }

    private function help(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }

        $webName = sysConfig('website_name');
        $accountType = sysConfig('username_type') === 'email' || sysConfig('username_type') === null ? ucfirst(trans('validation.attributes.email')) : trans('model.user.username');
        $telegramService = new TelegramService;
        $commands = trans('user.telegram.command.intro').": \n\n/bind `$accountType` -[".trans('user.telegram.command.bind', ['web_name' => $webName])."]\n/traffic -[".trans('user.telegram.command.traffic')."]\n/url -[".trans('user.telegram.command.web_url', ['web_name' => $webName])."]\n/unbind -[".trans('user.telegram.command.unbind').']';
        $telegramService->sendMessage($msg->chat_id, $commands, 'markdown');
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
            abort(500, trans('user.telegram.params_missing'));
        }
        $admin = User::role('Super Admin')->whereId($user->id)->first();
        if ($admin) {
            $ticket = Ticket::whereId($ticketId)->first();
            if (! $ticket) {
                abort(500, trans('user.telegram.ticket_missing'));
            }

            $ticket->reply()->create(['admin_id' => $admin->id, 'content' => $msg->text]);
            if ($ticket->status !== 1) {
                $ticket->update(['status' => 1]);
            }
        }
        (new TelegramService)->sendMessage($msg->chat_id, trans('user.telegram.ticket_reply', ['id' => $ticketId]), 'markdown');
    }

    private function bind(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        if (! isset($msg->args[0])) {
            abort(500, trans('user.telegram.params_missing'));
        }
        $user = User::whereUsername($msg->args[0])->first();
        if (! $user) {
            abort(500, trans('user.telegram.user_missing'));
        }
        if ($user->telegram_user_id) {
            abort(500, trans('user.telegram.bind_exists'));
        }

        if (! $user->userAuths()->create(['type' => 'telegram', 'identifier' => $msg->chat_id])) {
            abort(500, trans('common.failed_item', ['attribute' => trans('user.oauth.bind')]));
        }
        $telegramService = new TelegramService;
        $telegramService->sendMessage($msg->chat_id, trans('common.success_item', ['attribute' => trans('user.oauth.bind')]));
    }

    private function traffic(): void
    {
        $msg = $this->msg;
        if (! $msg->is_private) {
            return;
        }
        $telegramService = new TelegramService;
        if (! $oauth = UserOauth::query()->where(['type' => 'telegram', 'identifier' => $msg->chat_id])->first()) {
            $this->help();
            $telegramService->sendMessage($msg->chat_id, trans('user.telegram.bind_missing'), 'markdown');

            return;
        }
        $user = $oauth->user;
        $transferEnable = formatBytes($user->transfer_enable);
        $up = formatBytes($user->u);
        $down = formatBytes($user->d);
        $remaining = formatBytes($user->transfer_enable - ($user->u + $user->d));
        $text = '🚥'.trans('user.subscribe.info.title')."\n———————————————\n".trans('user.subscribe.info.total').": `$transferEnable`\n".trans('user.subscribe.info.upload').": `$up`\n".trans('user.subscribe.info.download').": `$down`\n".trans('user.account.remain').": `$remaining`";
        $telegramService->sendMessage($msg->chat_id, $text, 'markdown');
    }

    private function getUrl(): void
    {
        $msg = $this->msg;
        $telegramService = new TelegramService;
        $telegramService->sendMessage($msg->chat_id, trans('user.telegram.get_url', ['get_url' => sysConfig('website_name')]).': '.sysConfig('website_url'), 'markdown');
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
            $telegramService->sendMessage($msg->chat_id, trans('user.telegram.bind_missing'), 'markdown');

            return;
        }
        if (! $user->userAuths()->whereType('telegram')->whereIdentifier($msg->chat_id)->delete()) {
            abort(500, trans('common.failed_item', ['attribute' => trans('user.oauth.unbind')]));
        }
        $telegramService->sendMessage($msg->chat_id, trans('common.success_item', ['attribute' => trans('user.oauth.unbind')]), 'markdown');
    }
}
