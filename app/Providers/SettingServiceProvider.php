<?php

namespace App\Providers;

use App\Channels\BarkChannel;
use App\Channels\ServerChanChannel;
use App\Models\Config;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\BearyChat\BearyChatChannel;
use NotificationChannels\Telegram\TelegramChannel;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $toApp = collect([
            'geetest_id'             => 'geetest.id',
            'geetest_key'            => 'geetest.key',
            'google_captcha_secret'  => 'NoCaptcha.secret',
            'google_captcha_sitekey' => 'NoCaptcha.sitekey',
            'hcaptcha_secret'        => 'HCaptcha.secret',
            'hcaptcha_sitekey'       => 'HCaptcha.sitekey',
        ]);

        $notifications = collect([
            'account_expire_notification',
            'data_anomaly_notification',
            'data_exhaust_notification',
            'node_blocked_notification',
            'node_daily_notification',
            'node_offline_notification',
            'password_reset_notification',
            'payment_received_notification',
            'ticket_closed_notification',
            'ticket_created_notification',
            'ticket_replied_notification',
        ]);

        $settings = Config::all();

        $modified = $settings
            ->whereNotIn('name', $toApp->keys()->merge($notifications)) // 设置一般系统选项
            ->pluck('value', 'name')
            ->merge($settings->whereIn('name', $notifications)->pluck('value', 'name')->map(function ($item) {
                return self::setChannel(json_decode($item, true) ?? []);
            })) // 设置通知相关选项
            ->merge(collect(['is_onlinePay' => $settings->whereIn('name', ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'])->pluck('value')->filter()->isNotEmpty(),
            ])) // 设置在线支付开关
            ->sortKeys();

        config(['settings' => $modified]); // 设置系统参数

        $settings->whereIn('name', $toApp->keys())->pluck('value', 'name')->each(function ($item, $key) use ($toApp) {
            config([$toApp[$key] => $item]); // 设置PHP软件包相关配置
        });

        collect([
            'website_name' => 'app.name',
            'website_url'  => 'app.url',
        ])->each(function ($item, $key) {
            config([$item => config('settings.'.$key)]); // 设置APP有关的选项
        });
    }

    private static function setChannel(array $channels)
    {
        $options = [
            'telegram'   => TelegramChannel::class,
            'beary'      => BearyChatChannel::class,
            'bark'       => BarkChannel::class,
            'serverChan' => ServerChanChannel::class,
        ];
        foreach ($options as $option => $str) {
            if (($key = array_search($option, $channels, true)) !== false) {
                $channels[$key] = $str;
            }
        }

        return $channels;
    }
}
