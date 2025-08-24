<?php

namespace App\Utils;

use App\Models\CouponLog;
use App\Models\Marketing;
use App\Models\NotificationLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Models\UserDataModifyLog;
use App\Models\UserLoginLog;
use App\Models\UserSubscribe;
use Log;
use RuntimeException;
use Str;

class Helpers
{
    private static array $denyPorts = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

    public static function makeSubscribeCode(): string
    { // 生成用户的订阅码
        do {
            $code = Str::random();
        } while (UserSubscribe::whereCode($code)->exists());

        return $code;
    }

    /**
     * 添加用户.
     *
     * @param  string  $username  用户名
     * @param  string  $password  用户密码
     * @param  int  $transfer_enable  可用流量
     * @param  int  $date  可使用天数
     * @param  int|null  $inviter_id  邀请人
     * @param  string|null  $nickname  昵称
     * @param  int  $status  状态：-1-禁用、0-未激活、1-正常
     */
    public static function addUser(string $username, string $password, int $transfer_enable = 0, int $date = 0, ?int $inviter_id = null, ?string $nickname = null, int $status = 0): User
    {
        return User::create([
            'nickname' => $nickname ?? $username,
            'username' => $username,
            'password' => $password,
            'port' => self::getPort(), // 生成一个可用端口
            'passwd' => Str::random(),
            'vmess_id' => Str::uuid(),
            'method' => self::getDefaultMethod(),
            'protocol' => self::getDefaultProtocol(),
            'obfs' => self::getDefaultObfs(),
            'transfer_enable' => $transfer_enable,
            'expired_at' => now()->addDays($date)->toDateString(),
            'user_group_id' => null,
            'reg_ip' => IP::getClientIp(),
            'inviter_id' => $inviter_id,
            'status' => $status,
        ]);
    }

    public static function getPort(): int
    { // 获取一个有效端口
        $minPort = (int) sysConfig('min_port');
        $maxPort = (int) sysConfig('max_port');
        $isRandPort = sysConfig('is_rand_port');
        $occupiedPorts = array_merge(User::where('port', '!=', 0)->pluck('port')->toArray(), self::$denyPorts);

        $totalPorts = $maxPort - $minPort + 1;
        $availablePortsCount = $totalPorts - count($occupiedPorts);

        if ($availablePortsCount === 0) {
            throw new RuntimeException('No available port found.');
        }

        if ($isRandPort) {
            $attempts = 0;
            do {
                $port = random_int($minPort, $maxPort);
                $attempts++;
                // 防止无限循环
                if ($attempts > 100) {
                    throw new RuntimeException('Unable to find available port after 100 attempts.');
                }
            } while (in_array($port, $occupiedPorts, true));
        } else {
            $port = $minPort;
            while (in_array($port, $occupiedPorts, true)) {
                $port++;
                if ($port > $maxPort) {
                    throw new RuntimeException('No available port found.');
                }
            }
        }

        return $port;
    }

    public static function getDefaultMethod(): string
    { // 获取默认加密方式
        static $method = null;

        if ($method === null) {
            $config = SsConfig::default()->type(1)->first();
            $method = $config->name ?? 'aes-256-cfb';
        }

        return $method;
    }

    public static function getDefaultProtocol(): string
    { // 获取默认协议
        static $protocol = null;

        if ($protocol === null) {
            $config = SsConfig::default()->type(2)->first();
            $protocol = $config->name ?? 'origin';
        }

        return $protocol;
    }

    public static function getDefaultObfs(): string
    { // 获取默认混淆
        static $obfs = null;

        if ($obfs === null) {
            $config = SsConfig::default()->type(3)->first();
            $obfs = $config->name ?? 'plain';
        }

        return $obfs;
    }

    /**
     * 添加通知推送日志.
     *
     * @param  string  $title  标题
     * @param  string  $content  内容
     * @param  int  $type  发送类型
     * @param  int  $status  投递状态
     * @param  string|null  $error  投递失败时记录的异常信息
     * @param  string|null  $msgId  对公查询ID
     * @param  string  $address  收信方
     */
    public static function addNotificationLog(string $title, string $content, int $type, int $status = 1, ?string $error = null, ?string $msgId = null, string $address = 'admin'): int
    {
        return NotificationLog::create(['type' => $type, 'msg_id' => $msgId, 'address' => $address, 'title' => $title, 'content' => $content, 'status' => $status, 'error' => $error])->id;
    }

    /**
     * 添加优惠券操作日志.
     *
     * @param  string  $description  备注
     * @param  int  $couponId  优惠券ID
     * @param  int|null  $goodsId  商品ID
     * @param  int|null  $orderId  订单ID
     */
    public static function addCouponLog(string $description, int $couponId, ?int $goodsId = null, ?int $orderId = null): bool
    {
        return CouponLog::create(['coupon_id' => $couponId, 'goods_id' => $goodsId, 'order_id' => $orderId, 'description' => $description])->wasRecentlyCreated;
    }

    /**
     * 记录余额操作日志.
     *
     * @param  int  $userId  用户ID
     * @param  int|null  $orderId  订单ID
     * @param  float|int  $before  记录前余额
     * @param  float|int  $after  记录后余额
     * @param  float|int  $amount  发生金额
     * @param  string|null  $description  描述
     */
    public static function addUserCreditLog(int $userId, ?int $orderId, float|int $before, float|int $after, float|int $amount, ?string $description = null): bool
    {
        return UserCreditLog::create(['user_id' => $userId, 'order_id' => $orderId, 'before' => $before, 'after' => $after, 'amount' => $amount, 'description' => $description, 'created_at' => now()])->wasRecentlyCreated;
    }

    /**
     * 记录流量变动日志.
     *
     * @param  int  $userId  用户ID
     * @param  int  $before  记录前的值
     * @param  int  $after  记录后的值
     * @param  string|null  $description  描述
     * @param  int|null  $orderId  订单ID
     */
    public static function addUserTrafficModifyLog(int $userId, int $before, int $after, ?string $description = null, ?int $orderId = null): bool
    {
        return UserDataModifyLog::create(['user_id' => $userId, 'order_id' => $orderId, 'before' => $before, 'after' => $after, 'description' => $description])->wasRecentlyCreated;
    }

    /**
     * 推销信息推送
     *
     * @param  string  $receiver  收件人
     * @param  int  $type  渠道类型
     * @param  string  $title  标题
     * @param  string  $content  内容
     * @param  int  $status  状态
     * @param  string|null  $error  报错
     */
    public static function addMarketing(string $receiver, int $type, string $title, string $content, int $status = 1, ?string $error = null): bool
    {
        return Marketing::create(['type' => $type, 'receiver' => $receiver, 'title' => $title, 'content' => $content, 'error' => $error, 'status' => $status])->wasRecentlyCreated;
    }

    /**
     * 用户登录后操作.
     *
     * @param  User  $user  用户ID
     * @param  string  $ip  IP地址
     */
    public static function userLoginAction(User $user, string $ip): void
    {
        $ipLocation = IP::getIPInfo($ip);

        $logData = [
            'user_id' => $user->id,
            'ip' => $ip,
            'country' => $ipLocation['country'] ?? '',
            'province' => $ipLocation['region'] ?? '',
            'city' => $ipLocation['city'] ?? '',
            'county' => '', // 未使用的字段
            'isp' => $ipLocation['isp'] ?? '',
            'area' => $ipLocation['area'] ?? '',
        ];

        // 记录错误日志仅在 IP 信息无效时
        if (! $ipLocation) {
            Log::warning(trans('errors.get_ip').'：'.$ip);
        }

        // 批量插入日志记录并更新用户登录时间
        UserLoginLog::create($logData);
        $user->update(['last_login' => time()]);
    }

    public static function getPriceTag(int|float $amount): string
    { // Get price with money symbol in the user's preferred currency.
        $currentCurrency = session('currency');
        $standard = sysConfig('standard_currency');
        $currencyLib = array_column(config('common.currency'), 'symbol', 'code');
        if (! empty($currentCurrency) && isset($currencyLib[$currentCurrency]) && $currentCurrency !== $standard) {
            $convert = CurrencyExchange::convert($currentCurrency, $amount);
            if ($convert !== false) {
                return $currencyLib[$currentCurrency].$convert;
            }
        }

        return $currencyLib[$standard].$amount;
    }
}
