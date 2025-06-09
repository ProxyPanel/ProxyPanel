<?php

use App\Models\Config;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private static array $dropConfigs = ['bitpay_secret', 'is_push_bear', 'is_subscribe_ban', 'is_traffic_ban', 'is_checkin'];

    private static array $renameConfigs = [
        'aff_salt' => 'affiliate_link_salt',
        'max_rand_traffic' => 'checkin_reward_max',
        'min_rand_traffic' => 'checkin_reward',
        'referral_type' => 'referral_reward_type',
        'subscribe_ban_times' => 'subscribe_rate_limit',
        'traffic_ban_time' => 'ban_duration',
        'traffic_ban_value' => 'traffic_abuse_limit',
        'traffic_limit_time' => 'checkin_interval',
        'website_analytics' => 'website_statistics_code',
        'website_callback_url' => 'payment_callback_url',
        'website_customer_service' => 'website_customer_service_code',
    ];

    public function up(): void
    {
        if (Config::exists()) {
            Config::create([['name' => 'tasks_chunk', 'value' => 3000], ['name' => 'tasks_clean', 'value' => '{"notification_logs":"-18 months","node_daily_logs":"-13 months","node_hourly_logs":"-14 days","node_heartbeats":"-30 minutes","node_online_logs":"-14 days","payments":"-1 years","rule_logs":"-3 months","node_online_ips":"-7 days","user_baned_logs":"-3 months","user_daily_logs_nodes":"-36 days","user_daily_logs_total":"-3 months","user_hourly_logs":"-3 days","login_logs":"-3 months","subscribe_logs":"-2 months","traffic_logs":"-3 days","unpaid_orders":"-1 years"}'], ['name' => 'tasks_close', 'value' => '{"tickets":"-72 hours","confirmation_orders":"-12 hours","orders":"-15 minutes","verify":"-15 minutes"}'], ['name' => 'recently_heartbeat', 'value' => '-10 minutes']]);

            if (! sysConfig('is_subscribe_ban')) {
                Config::whereName('subscribe_ban_times')->update(['value' => null]);
            }

            if (! sysConfig('is_traffic_ban')) {
                Config::whereName('traffic_ban_time')->update(['value' => null]);
            }

            if (! sysConfig('is_checkin')) {
                Config::whereName('traffic_limit_time')->update(['value' => null]);
            }

            foreach (self::$renameConfigs as $old => $new) {
                Config::whereName($old)->update(['name' => $new]);
            }

            Config::whereName('auto_release_port')->update(['value' => sysConfig('auto_release_port') ? 30 : null]);

            foreach (self::$dropConfigs as $config) {
                Config::destroy(['name' => $config]);
            }
        }

        Order::wherePayWay('balance')->update(['pay_way' => 'credit']);
    }

    public function down(): void
    {
        Config::destroy([['name' => 'tasks_chunk'], ['name' => 'tasks_clean'], ['name' => 'tasks_close'], ['name' => 'recently_heartbeat']]);

        foreach (self::$dropConfigs as $config) {
            Config::insert(['name' => $config]);
        }

        foreach (self::$renameConfigs as $old => $new) {
            Config::whereName($new)->update(['name' => $old]);
        }

        if (sysConfig('subscribe_ban_times')) {
            Config::whereName('is_subscribe_ban')->update(['value' => 1]);
        }

        if (sysConfig('traffic_ban_time')) {
            Config::whereName('is_traffic_ban')->update(['value' => 1]);
        }

        if (sysConfig('traffic_limit_time')) {
            Config::whereName('is_checkin')->update(['value' => 1]);
        }

        Config::whereName('auto_release_port')->update(['value' => sysConfig('auto_release_port') ? 1 : null]);
    }
};
