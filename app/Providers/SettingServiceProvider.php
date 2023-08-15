<?php

namespace App\Providers;

use App\Channels\BarkChannel;
use App\Channels\DingTalkChannel;
use App\Channels\iYuuChannel;
use App\Channels\PushDeerChannel;
use App\Channels\PushPlusChannel;
use App\Channels\ServerChanChannel;
use App\Channels\TgChatChannel;
use App\Channels\WeChatChannel;
use App\Models\Config;
use Cache;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Telegram\TelegramChannel;

class SettingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $toApp = collect([
            2 => ['geetest.id', 'geetest.key'],
            3 => ['NoCaptcha.secret', 'NoCaptcha.sitekey'],
            4 => ['HCaptcha.secret', 'HCaptcha.sitekey'],
            5 => ['turnstile.secret_key', 'turnstile.site_key'],
        ]);

        $notifications = [
            'account_expire_notification',
            'data_anomaly_notification',
            'data_exhaust_notification',
            'node_blocked_notification',
            'node_daily_notification',
            'node_offline_notification',
            'password_reset_notification',
            'payment_confirm_notification',
            'payment_received_notification',
            'ticket_closed_notification',
            'ticket_created_notification',
            'ticket_replied_notification',
        ];
        $payments = ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'];
        if (! Cache::has('settings')) {
            Cache::forever('settings', Config::whereNotNull('value')->get());
        }
        $settings = Cache::get('settings');
        $modified = $settings
            ->whereNotIn('name', $notifications) // 设置一般系统选项
            ->pluck('value', 'name')
            ->merge($settings->whereIn('name', $notifications)->pluck('value', 'name')->map(function ($item) {
                return self::setChannel(json_decode($item, true) ?? (is_array($item) ? $item : [$item])); // 设置通知相关选项
            }))
            ->merge(collect(['is_onlinePay' => $settings->whereIn('name', $payments)->pluck('value')->filter()->isNotEmpty()])) // 设置在线支付开关
            ->sortKeys()
            ->toArray();

        config(['settings' => $modified]); // 设置系统参数

        if (config('settings.is_captcha') > 1) {
            config([$toApp[config('settings.is_captcha')][0] => config('settings.captcha_secret')]);
            config([$toApp[config('settings.is_captcha')][1] => config('settings.captcha_key')]);
        }

        collect(['website_name' => 'app.name', 'website_url' => 'app.url'])->each(fn ($item, $key) => config([$item => config('settings.'.$key)]));
    }

    private static function setChannel(array $channels): array
    {
        foreach (
            [
                'telegram' => TelegramChannel::class,
                'bark' => BarkChannel::class,
                'pushPlus' => PushPlusChannel::class,
                'serverChan' => ServerChanChannel::class,
                'tgChat' => TgChatChannel::class,
                'weChat' => WeChatChannel::class,
                'iYuu' => iYuuChannel::class,
                'pushDear' => PushDeerChannel::class,
                'dingTalk' => DingTalkChannel::class,
            ] as $key => $channel
        ) {
            $index = array_search($key, $channels, true);
            if ($index !== false) {
                $channels[$index] = $channel;
            }
        }

        return $channels;
    }
}
