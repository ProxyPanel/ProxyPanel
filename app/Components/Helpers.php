<?php

namespace App\Components;

use App\Channels\BarkChannel;
use App\Channels\ServerChanChannel;
use App\Models\Config;
use App\Models\CouponLog;
use App\Models\Marketing;
use App\Models\NotificationLog;
use App\Models\SsConfig;
use App\Models\User;
use App\Models\UserBanedLog;
use App\Models\UserCreditLog;
use App\Models\UserDataModifyLog;
use App\Models\UserSubscribe;
use Cache;
use DateTime;
use NotificationChannels\BearyChat\BearyChatChannel;
use NotificationChannels\Telegram\TelegramChannel;
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
     * @param  int  $transfer_enable  可用流量
     * @param  int|null  $date  可使用天数
     * @param  int|null  $inviter_id  邀请人
     * @param  string|null  $username  昵称
     * @return User
     */
    public static function addUser(string $email, string $password, int $transfer_enable, int $date = null, int $inviter_id = null, string $username = null): User
    {
        return User::create([
            'username'        => $username ?? $email,
            'email'           => $email,
            'password'        => $password,
            'port'            => self::getPort(), // 生成一个可用端口
            'passwd'          => Str::random(),
            'vmess_id'        => Str::uuid(),
            'method'          => self::getDefaultMethod(),
            'protocol'        => self::getDefaultProtocol(),
            'obfs'            => self::getDefaultObfs(),
            'transfer_enable' => $transfer_enable,
            'expired_at'      => date('Y-m-d', strtotime('+'.$date.' days')),
            'user_group_id'   => null,
            'reg_ip'          => IP::getClientIp(),
            'inviter_id'      => $inviter_id,
        ]);
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

    /**
     * 添加用户封禁日志.
     *
     * @param  int  $userId  用户ID
     * @param  int  $time  封禁时长，单位分钟
     * @param  string  $description  封禁理由
     *
     * @return bool
     */
    public static function addUserBanLog(int $userId, int $time, string $description)
    {
        $log = new UserBanedLog();
        $log->user_id = $userId;
        $log->time = $time;
        $log->description = $description;

        return $log->save();
    }

    /**
     * 推销信息推送
     *
     * @param  int  $type  渠道类型
     * @param  string  $title  标题
     * @param  string  $content  内容
     * @param  int  $status  状态
     * @param  string  $error  报错
     * @param  string  $receiver  收件人
     * @return int
     */
    public static function addMarketing(int $type, string $title, string $content, int $status = 1, string $error = '', string $receiver = ''): int
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
}
