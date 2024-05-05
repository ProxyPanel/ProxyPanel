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

    public static function methodList()
    { // 加密方式
        return SsConfig::type(1)->get();
    }

    public static function protocolList()
    { // 协议
        return SsConfig::type(2)->get();
    }

    public static function obfsList()
    { // 混淆
        return SsConfig::type(3)->get();
    }

    public static function makeSubscribeCode(): string
    { // 生成用户的订阅码
        $code = Str::random();
        if (UserSubscribe::whereCode($code)->exists()) {
            $code = self::makeSubscribeCode();
        }

        return $code;
    }

    /**
     * 添加用户.
     *
     * @param  string  $username  用户
     * @param  string  $password  用户密码
     * @param  int  $transfer_enable  可用流量
     * @param  int  $date  可使用天数
     * @param  int|null  $inviter_id  邀请人
     * @param  string|null  $nickname  昵称
     */
    public static function addUser(string $username, string $password, int $transfer_enable = 0, int $date = 0, ?int $inviter_id = null, ?string $nickname = null): User
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
            'expired_at' => now()->addDays($date)->format('Y-m-d'),
            'user_group_id' => null,
            'reg_ip' => IP::getClientIp(),
            'inviter_id' => $inviter_id,
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
            do {
                $port = random_int($minPort, $maxPort);
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
        $config = SsConfig::default()->type(1)->first();

        return $config->name ?? 'aes-256-cfb';
    }

    public static function getDefaultProtocol(): string
    { // 获取默认协议
        $config = SsConfig::default()->type(2)->first();

        return $config->name ?? 'origin';
    }

    public static function getDefaultObfs(): string
    { // 获取默认混淆
        $config = SsConfig::default()->type(3)->first();

        return $config->name ?? 'plain';
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
        $log = new NotificationLog();
        $log->type = $type;
        $log->msg_id = $msgId;
        $log->address = $address;
        $log->title = $title;
        $log->content = $content;
        $log->status = $status;
        $log->error = $error;
        $log->save();

        return $log->id;
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
        $log = new CouponLog();
        $log->coupon_id = $couponId;
        $log->goods_id = $goodsId;
        $log->order_id = $orderId;
        $log->description = $description;

        return $log->save();
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
        $log = new UserCreditLog();
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->before = $before;
        $log->after = $after;
        $log->amount = $amount;
        $log->description = $description;
        $log->created_at = now();

        return $log->save();
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
        $log = new UserDataModifyLog();
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->before = $before;
        $log->after = $after;
        $log->description = $description;

        return $log->save();
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
        $marketing = new Marketing();
        $marketing->type = $type;
        $marketing->receiver = $receiver;
        $marketing->title = $title;
        $marketing->content = $content;
        $marketing->error = $error;
        $marketing->status = $status;

        return $marketing->save();
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

        if (empty($ipLocation)) {
            Log::warning(trans('errors.get_ip').'：'.$ip);
        }

        $log = new UserLoginLog();
        $log->user_id = $user->id;
        $log->ip = $ip;
        $log->country = $ipLocation['country'] ?? '';
        $log->province = $ipLocation['region'] ?? '';
        $log->city = $ipLocation['city'] ?? '';
        $log->county = '';
        $log->isp = $ipLocation['isp'] ?? '';
        $log->area = $ipLocation['area'] ?? '';
        $log->save();

        $user->update(['last_login' => time()]); // 更新登录信息
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
