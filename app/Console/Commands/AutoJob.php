<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Components\ServerChan;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\EmailLog;
use App\Http\Models\Invite;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\Config;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use App\Http\Models\UserTrafficHourly;
use App\Mail\nodeCrashWarning;
use Cache;
use Mail;
use Log;
use DB;

class AutoJob extends Command
{
    protected $signature = 'autoJob';
    protected $description = '自动化任务';
    protected $ssrCheckCacheKey = 'ssr_check_warning_';
    protected $serverCheckCacheKey = 'server_check_warning_';
    protected static $config;

    public function __construct()
    {
        parent::__construct();
        self::$config = $this->systemConfig();
    }

    /*
     * 以下操作顺序如果随意挪动可能导致出现异常
     */
    public function handle()
    {
        $jobStartTime = microtime(true);

        // SSR(R)被启用说明用户购买了流量，需要重置ban_time，防止异常
        $this->resetBantime();

        // 优惠券到期自动置无效
        $this->expireCoupon();

        // 邀请码到期自动置无效
        $this->exipreInvite();

        // 封禁访问异常的订阅链接
        $this->blockSubscribe();

        // 封禁账号
        $this->blockUsers();

        // 自动移除被封禁账号的标签
        $this->removeUserLabels();

        // 解封被临时封禁的账号（ban_time > 0）
        $this->unblockUsers();

        // 端口回收与分配
        $this->dispatchPort();

        // 关闭超时未支付订单
        $this->closeOrder();

        // 监测节点状态
        $this->checkNode();

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 重置ban_time
    private function resetBantime()
    {
        User::query()->where('enable', 1)->where('ban_time', -1)->update(['ban_time' => 0]);
    }

    // 优惠券到期自动置无效
    private function expireCoupon()
    {
        $couponList = Coupon::query()->where('status', 0)->where('available_end', '<=', time())->get();
        if (!$couponList->isEmpty()) {
            foreach ($couponList as $coupon) {
                Coupon::query()->where('id', $coupon->id)->update(['status' => 2]);
            }
        }
    }

    // 邀请码到期自动置无效
    private function exipreInvite()
    {
        $inviteList = Invite::query()->where('status', 0)->where('dateline', '<=', date('Y-m-d H:i:s'))->get();
        if (!$inviteList->isEmpty()) {
            foreach ($inviteList as $invite) {
                Invite::query()->where('id', $invite->id)->update(['status' => 2]);
            }
        }
    }

    // 封禁访问异常的订阅链接
    private function blockSubscribe()
    {
        if (self::$config['is_subscribe_ban']) {
            $subscribeList = UserSubscribe::query()->where('status', 1)->get();
            if (!$subscribeList->isEmpty()) {
                foreach ($subscribeList as $subscribe) {
                    // 24小时内不同IP的请求次数
                    $request_times = UserSubscribeLog::query()->where('sid', $subscribe->id)->where('request_time', '>=', date("Y-m-d H:i:s", strtotime("-24 hours")))->distinct('request_ip')->count('request_ip');
                    if ($request_times >= self::$config['subscribe_ban_times']) {
                        UserSubscribe::query()->where('id', $subscribe->id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '存在异常，自动封禁']);

                        // 记录封禁日志
                        $this->addUserBanLog($subscribe->user_id, 0, '【完全封禁订阅】-订阅24小时内请求异常');
                    }
                }
            }
        }
    }

    // 封禁账号
    private function blockUsers()
    {
        // 封禁24小时内流量异常账号
        if (self::$config['is_traffic_ban']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('ban_time', '>=', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    $time = date('Y-m-d H:i:s', time() - 24 * 60 * 60);
                    $totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', $time)->sum('total');
                    if ($totalTraffic >= (self::$config['traffic_ban_value'] * 1024 * 1024 * 1024)) {
                        $ban_time = strtotime(date('Y-m-d H:i:s', strtotime("+" . self::$config['traffic_ban_time'] . " minutes")));
                        User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => $ban_time]);

                        // 写入日志
                        $this->addUserBanLog($user->id, self::$config['traffic_ban_time'], '【临时封禁代理】-24小时内流量异常');
                    }
                }
            }
        }

        // 禁用流量超限用户
        $userList = User::query()->where('enable', 1)->whereRaw("u + d >= transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => -1]);

                // 写入日志
                $this->addUserBanLog($user->id, 0, '【完全封禁代理】-流量已用完');
            }
        }

        // 自动禁用过期用户
        $userList = User::query()->where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                if (self::$config['is_ban_status']) {
                    User::query()->where('id', $user->id)->update(['enable' => 0, 'status' => -1, 'ban_time' => -1]);

                    $this->addUserBanLog($user->id, 0, '【完全封禁账号及代理】-账号已过期');
                } else {
                    User::query()->where('id', $user->id)->update(['enable' => 0, 'ban_time' => -1]);

                    $this->addUserBanLog($user->id, 0, '【完全封禁代理】-账号已过期');
                }
            }
        }
    }

    // 自动移除被封禁账号的标签
    private function removeUserLabels()
    {
        $userList = User::query()->where('enable', 0)->where('ban_time', -1)->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                UserLabel::query()->where('user_id', $user->id)->delete();
            }
        }
    }

    // 解封被临时封禁的账号（ban_time > 0）
    private function unblockUsers()
    {
        $userList = User::query()->where('status', '>=', 0)->where('ban_time', '>', 0)->get();
        foreach ($userList as $user) {
            if ($user->ban_time < time()) {
                User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

                // 写入操作日志
                $this->addUserBanLog($user->id, 0, '【自动解封】-封禁到期');
            }
        }

        // 用户购买了流量（可用大于已用）也解封
        $userList = User::query()->where('status', '>=', 0)->where('enable', 0)->where('ban_time', -1)->whereRaw("u + d < transfer_enable")->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                User::query()->where('id', $user->id)->update(['enable' => 1, 'ban_time' => 0]);

                // 写入操作日志
                $this->addUserBanLog($user->id, 0, '【自动解封】-有流量解封');
            }
        }
    }

    // 端口回收与分配
    private function dispatchPort()
    {
        // 自动分配端口
        if (self::$config['auto_release_port']) {
            $userList = User::query()->where('status', '>=', 0)->where('enable', 1)->where('port', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    $port = self::$config['is_rand_port'] ? $this->getRandPort() : $this->getOnlyPort();

                    User::query()->where('id', $user->id)->update(['port' => $port]);
                }
            }
        }

        // 被封禁账号自动释放端口
        if (self::$config['auto_release_port']) {
            $userList = User::query()->where('enable', 0)->where('ban_time', -1)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    if ($user->port) {
                        User::query()->where('id', $user->id)->update(['port' => 0]);
                    }
                }
            }
        }
    }

    // 自动关闭超时未支付订单
    private function closeOrder()
    {
        // 自动关闭超时未支付的有赞云订单（有赞云收款二维码超过60分钟自动关闭，我们限制30分钟内必须付款）
        $paymentList = Payment::query()->with(['order', 'order.coupon'])->where('status', 0)->where('created_at', '<=', date("Y-m-d H:i:s", strtotime("-30 minutes")))->get();
        if (!$paymentList->isEmpty()) {
            DB::beginTransaction();
            try {
                foreach ($paymentList as $payment) {
                    // 关闭支付单
                    Payment::query()->where('id', $payment->id)->update(['status' => -1]);

                    // 关闭订单
                    Order::query()->where('oid', $payment->oid)->update(['status' => -1]);

                    // 退回优惠券
                    if ($payment->order->coupon_id) {
                        Coupon::query()->where('id', $payment->order->coupon_id)->update(['status' => 0]);

                        $this->addCouponLog($payment->order->coupon_id, $payment->order->goods_id, $payment->oid, '订单超时未支付，自动退回');
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                Log::info('【异常】自动关闭超时未支付订单：' . $e->getMessage());

                DB::rollBack();
            }
        }
    }

    // 监测节点状态 TODO：需要改进，否则curl请求时间超长
    private function checkNode()
    {
        $title = "节点异常警告";

        $nodeList = SsNode::query()->where('status', 1)->get();
        foreach ($nodeList as $node) {
            // TCP检测
            $tcpCheck = $this->tcpCheck($node->ip);
            if (false !== $tcpCheck && $tcpCheck) {
                // 10分钟内已发警告，则不再发
                if (Cache::has($this->ssrCheckCacheKey . $node->id)) {
                    continue;
                }

                $content = '节点无异常';
                if ($tcpCheck === 1) {
                    $content = "节点**{$node->name}【{$node->ip}】**异常：**宕机**";
                } else if ($tcpCheck === 2) {
                    $content = "节点**{$node->name}【{$node->ip}】**异常：**海外不通**";
                } else if ($tcpCheck === 3) {
                    $content = "节点**{$node->name}【{$node->ip}】**异常：**TCP阻断**";
                }

                // 通知管理员
                $this->notifyMaster($title, $content, $node->name, $node->server);

                // 写入发信缓存
                Cache::put($this->ssrCheckCacheKey . $node->id, $node->name . '(' . $node->server . ')', 10);
            }

            // 10分钟内无节点负载信息且TCP检测认为不是宕机则认为是SSR(R)后端炸了
            $node_info = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            if ($tcpCheck !== 1 && (empty($node_info) || empty($node_info->load))) {
                // 10分钟内已发警告，则不再发
                if (Cache::has($this->serverCheckCacheKey . $node->id)) {
                    continue;
                }

                $content = "节点**{$node->name}【{$node->ip}】**异常：**心跳异常**";

                // 通知管理员
                $this->notifyMaster($title, $content, $node->name, $node->server);

                // 写入发信缓存
                Cache::put($this->serverCheckCacheKey . $node->id, $node->name . '(' . $node->server . ')', 10);
            }
        }
    }

    // TCP检测
    private function tcpCheck($ip)
    {
        return 0;
        try {
            $tcpCN = $this->curlRequest("https://ipcheck.need.sh/api.php?location=cn&ip={$ip}&type=tcp");
            $tcpUS = $this->curlRequest("https://ipcheck.need.sh/api.php?location=us&ip={$ip}&type=tcp");

            if ($tcpCN['result'] != 'success' && $tcpUS['result'] != 'success') {
                throw new \Exception("节点监测探测接口异常");
            }

            if (!$tcpCN['alive'] && !$tcpUS['alive']) {
                return 1; // 中美都不通，服务器宕机
            } else if ($tcpCN['alive'] && !$tcpUS['alive']) {
                return 2; // 中通美不通，无法出国，可能是安全组策略限制（例如：阿里云、腾讯云）
            } else if (!$tcpCN['alive'] && $tcpUS['alive']) {
                return 3; // 美通中不通，说明被墙进行TCP阻断
            } else {
                return 0; // 正常
            }
        } catch (\Exception $e) {
            Log::error('节点监测请求失败：' . $e);

            return false;
        }
    }

    // 检测服务
//    private function checkHost()
//    {
//        $data =
//        $header = [
//            'Content-Type: application/json',
//            'Content-Length: ' . strlen($data)
//        ];
//    }

    /**
     * 通知管理员
     *
     * @param string $title      消息标题
     * @param string $content    消息内容
     * @param string $nodeName   节点名称
     * @param string $nodeServer 节点域名
     */
    private function notifyMaster($title, $content, $nodeName, $nodeServer)
    {
        $this->notifyMasterByEmail($title, $content, $nodeName, $nodeServer);
        $this->notifyMasterByServerchan($title, $content);
    }

    /**
     * 发邮件通知管理员
     *
     * @param string $title      消息标题
     * @param string $content    消息内容
     * @param string $nodeName   节点名称
     * @param string $nodeServer 节点域名
     */
    private function notifyMasterByEmail($title, $content, $nodeName, $nodeServer)
    {
        if (self::$config['is_node_crash_warning'] && self::$config['crash_warning_email']) {
            try {
                Mail::to(self::$config['crash_warning_email'])->send(new nodeCrashWarning(self::$config['website_name'], $nodeName, $nodeServer));
                $this->addEmailLog(1, $title, $content);
            } catch (\Exception $e) {
                $this->addEmailLog(1, $title, $content, 0, $e->getMessage());
            }
        }
    }

    /**
     * 通过ServerChan发微信消息提醒管理员
     *
     * @param string $title   消息标题
     * @param string $content 消息内容
     */
    private function notifyMasterByServerchan($title, $content)
    {
        if (self::$config['is_server_chan'] && self::$config['server_chan_key']) {
            $serverChan = new ServerChan();
            $serverChan->send($title, $content);
        }
    }

    /**
     * 添加用户封禁日志
     *
     * @param int    $userId  用户ID
     * @param int    $minutes 封禁时长，单位分钟
     * @param string $desc    封禁理由
     */
    private function addUserBanLog($userId, $minutes, $desc)
    {
        $log = new UserBanLog();
        $log->user_id = $userId;
        $log->minutes = $minutes;
        $log->desc = $desc;
        $log->save();
    }

    /**
     * 添加邮件发送日志
     *
     * @param int    $user_id 接收者用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     */
    private function addEmailLog($userId, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $userId;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }

    /**
     * 添加优惠券操作日志
     *
     * @param int    $couponId 优惠券ID
     * @param int    $goodsId  商品ID
     * @param int    $orderId  订单ID
     * @param string $desc     备注
     */
    private function addCouponLog($couponId, $goodsId, $orderId, $desc = '')
    {
        $couponLog = new CouponLog();
        $couponLog->coupon_id = $couponId;
        $couponLog->goods_id = $goodsId;
        $couponLog->order_id = $orderId;
        $couponLog->desc = $desc;
        $couponLog->save();
    }

    // 系统配置
    private function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }

    // 获取一个随机端口
    public function getRandPort()
    {
        $config = $this->systemConfig();

        $port = mt_rand($config['min_port'], $config['max_port']);
        $deny_port = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

        $exists_port = User::query()->pluck('port')->toArray();
        if (in_array($port, $exists_port) || in_array($port, $deny_port)) {
            $port = $this->getRandPort();
        }

        return $port;
    }

    // 获取一个端口
    public function getOnlyPort()
    {
        $config = $this->systemConfig();

        $port = $config['min_port'];
        $deny_port = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

        $exists_port = User::query()->where('port', '>=', $config['min_port'])->where('port', '<=', $config['max_port'])->pluck('port')->toArray();
        while (in_array($port, $exists_port) || in_array($port, $deny_port)) {
            $port = $port + 1;
        }

        return $port;
    }

    /**
     * 发起一个CURL请求
     *
     * @param string $url
     * @param array  $data
     * @param array  $header
     *
     * @return mixed
     */
    private function curlRequest($url, $data = [], $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        // 如果设置了header
        if ($header) {
//            $header = [
//                'Content-Type: application/json',
//                'Content-Length: ' . strlen($data)
//            ];
//            $header = [
//                'Content-Type: text/xml; charset=utf-8',
//                'Content-Length: ' . strlen($data)
//            ];
//            $header = [
//                'Content-type: text/plain',
//                'Content-Length: ' . strlen($data)
//            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        // 如果data有数据，则用POST请求
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, JSON_OBJECT_AS_ARRAY);
    }
}
