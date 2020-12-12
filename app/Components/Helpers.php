<?php

namespace App\Components;

use App\Models\Config;
use App\Models\CouponLog;
use App\Models\NotificationLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserCreditLog;
use App\Models\UserDataModifyLog;
use App\Models\UserSubscribe;
use Cache;
use DateTime;
use Str;

class Helpers
{
    // 不生成的端口
    private static $denyPorts = [
        1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179,
    ];

    // 加密方式
    public static function methodList()
    {
        return SsConfig::type(1)->get();
    }

    // 协议
    public static function protocolList()
    {
        return SsConfig::type(2)->get();
    }

    // 混淆
    public static function obfsList()
    {
        return SsConfig::type(3)->get();
    }

    // 生成用户的订阅码
    public static function makeSubscribeCode(): string
    {
        $code = Str::random();
        if (UserSubscribe::whereCode($code)->exists()) {
            $code = self::makeSubscribeCode();
        }

        return $code;
    }

    /**
     * 添加用户.
     *
     * @param  string  $email  用户邮箱
     * @param  string  $password  用户密码
     * @param  string  $transfer_enable  可用流量
     * @param  int  $data  可使用天数
     * @param  int|null  $inviter_id  邀请人
     *
     * @return int
     */
    public static function addUser(string $email, string $password, string $transfer_enable, int $data, $inviter_id = null): int
    {
        $user = new User();
        $user->username = $email;
        $user->email = $email;
        $user->password = $password;
        $user->port = self::getPort(); // 生成一个可用端口
        $user->passwd = Str::random();
        $user->vmess_id = Str::uuid();
        $user->method = self::getDefaultMethod();
        $user->protocol = self::getDefaultProtocol();
        $user->obfs = self::getDefaultObfs();
        $user->transfer_enable = $transfer_enable;
        $user->expired_at = date('Y-m-d', strtotime('+'.$data.' days'));
        $user->reg_ip = IP::getClientIp();
        $user->inviter_id = $inviter_id;
        $user->save();

        return $user->id;
    }

    // 获取一个有效端口
    public static function getPort(): int
    {
        if (sysConfig('is_rand_port')) {
            $port = self::getRandPort();
        } else {
            $port = (int) sysConfig('min_port');
            $exists_port = array_merge(User::where('port', '>=', $port)->pluck('port')->toArray(), self::$denyPorts);

            while (in_array($port, $exists_port, true)) {
                $port++;
            }
        }

        return $port;
    }

    // 获取一个随机端口
    private static function getRandPort(): int
    {
        $port = random_int(sysConfig('min_port'), sysConfig('max_port'));
        $exists_port = array_merge(
            User::where('port', '<>', 0)->pluck('port')->toArray(),
            self::$denyPorts
        );

        while (in_array($port, $exists_port, true)) {
            $port = random_int(sysConfig('min_port'), sysConfig('max_port'));
        }

        return $port;
    }

    // 获取默认加密方式
    public static function getDefaultMethod(): string
    {
        $config = SsConfig::default()->type(1)->first();

        return $config->name ?? 'aes-256-cfb';
    }

    // 获取默认协议
    public static function getDefaultProtocol(): string
    {
        $config = SsConfig::default()->type(2)->first();

        return $config->name ?? 'origin';
    }

    // 获取默认混淆
    public static function getDefaultObfs(): string
    {
        $config = SsConfig::default()->type(3)->first();

        return $config->name ?? 'plain';
    }

    // 获取系统配置
    public static function cacheSysConfig($name)
    {
        if ($name === 'is_onlinePay') {
            $value = sysConfig('is_AliPay') || sysConfig('is_QQPay') || sysConfig('is_WeChatPay') || sysConfig('is_otherPay');
            Cache::tags('sysConfig')->put('is_onlinePay', $value);
        } else {
            $value = Config::find($name)->value;
            Cache::tags('sysConfig')->put($name, $value ?? false);
        }

        return $value;
    }

    public static function daysToNow($date): int
    {
        return (new DateTime())->diff(new DateTime($date))->days;
    }

    /**
     * 添加通知推送日志.
     *
     * @param  string  $title  标题
     * @param  string  $content  内容
     * @param  int  $type  发送类型
     * @param  string  $address  收信方
     * @param  int  $status  投递状态
     * @param  string  $error  投递失败时记录的异常信息
     *
     * @return int
     */
    public static function addNotificationLog(string $title, string $content, int $type, $address = 'admin', $status = 1, $error = ''): int
    {
        $log = new NotificationLog();
        $log->type = $type;
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
     *
     * @return bool
     */
    public static function addCouponLog($description, $couponId, $goodsId = null, $orderId = null): bool
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
     * @param  int  $before  记录前余额
     * @param  int  $after  记录后余额
     * @param  int  $amount  发生金额
     * @param  string  $description  描述
     *
     * @return bool
     */
    public static function addUserCreditLog($userId, $orderId, $before, $after, $amount, $description = ''): bool
    {
        $log = new UserCreditLog();
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->before = $before;
        $log->after = $after;
        $log->amount = $amount;
        $log->description = $description;
        $log->created_at = date('Y-m-d H:i:s');

        return $log->save();
    }

    /**
     * 记录流量变动日志.
     *
     * @param  int  $userId  用户ID
     * @param  int|null  $orderId  订单ID
     * @param  int  $before  记录前的值
     * @param  int  $after  记录后的值
     * @param  string  $description  描述
     *
     * @return bool
     */
    public static function addUserTrafficModifyLog($userId, $orderId, $before, $after, $description = ''): bool
    {
        $log = new UserDataModifyLog();
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->before = $before;
        $log->after = $after;
        $log->description = $description;

        return $log->save();
    }

    public static function abortIfNotModified($data): string
    {
        $req = request();
        // Only for "GET" method
        if (! $req->isMethod('GET')) {
            return '';
        }

        $etag = sha1(json_encode($data));
        if ($etag == $req->header('IF-NONE-MATCH')) {
            abort(304);
        }

        return $etag;
    }
}
