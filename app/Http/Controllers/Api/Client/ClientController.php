<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\ClientApiResponse;
use App\Helpers\ResponseEnum;
use App\Models\Article;
use App\Models\GoodsCategory;
use App\Models\Level;
use App\Models\ReferralLog;
use App\Models\Ticket;
use App\Services\ProxyService;
use App\Services\UserService;
use App\Utils\Helpers;
use Arr;
use Artisan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

use function config;

class ClientController extends Controller
{
    use ClientApiResponse;

    public function __construct(Request $request)
    {
        if (str_contains($request->userAgent(), 'bob_vpn')) {
            $this->setClient('bob');
        }
    }

    public function getUserInfo()
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $userInfo = (new UserService)->getProfile();
        $userInfo['user_name'] = $user->nickname;
        $userInfo['email'] = $user->username;
        $userInfo['class_expire'] = $user->expiration_date;
        $userInfo['money'] = $user->credit;
        $userInfo['plan']['name'] = $user->orders()->activePlan()->latest()->first()->goods->name ?? '无';
        $ann = Article::type(2)->latest()->first();
        $user_expire = now()->diffInDays($user->expired_at, false) < 0;
        $total = $user->u + $user->d;
        $transfer_enable = $user->transfer_enable;
        $expired_days = now()->diffInDays($user->expired_at, false);
        $userInfo['class_expire_notice'] = '';
        if ($expired_days < 0) {
            $userInfo['class_expire_notice'] = '账号会员已过期，请先续费再使用~';
        } elseif ($expired_days > 0 && $expired_days <= config('client.class_expire_notice.days')) {
            $userInfo['class_expire_notice'] = sprintf(config('client.class_expire_notice.msg'), $expired_days);
        }

        $data['info'] = [
            'user' => $userInfo,
            'ssrSubToken' => $user->subscribe->code,
            'user_expire' => $user_expire,
            'subUrl' => route('sub', $user->subscribe->code),
            'baseUrl' => sysConfig('subscribe_domain') ?? sysConfig('website_url'),
            'ann' => $ann,
            'avatar' => $user->avatar,
            'usedTraffic' => formatBytes($total),
            'enableTraffic' => formatBytes($transfer_enable),
            'unUsedTraffic' => formatBytes($transfer_enable - $total),
            'reset_time' => now()->diffInDays($user->reset_time, false),
            'android_index_button' => config('client.android_index_button'),
        ];

        return $this->succeed(null, $data);
    }

    public function getOrders(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $user->orders()->orderByDesc('id')->limit(8)->get();
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->id,
                'total_amount' => $order->amount * 100,
                'plan' => ['name' => $order->goods()->value('name') ?? '余额充值'],
                'status' => [-1 => 2, 0 => 0, 1 => 1, 2 => 3, 3 => 4][$order->status],
                'created_at' => strtotime($order->created_at),
            ];
        }

        return $this->succeed($data);
    }

    public function getUserTransfer(): JsonResponse
    {
        $user = auth()->user();

        return $this->succeed(null, [
            'arr' => [
                'todayUsedTraffic' => formatBytes($user->d),
                'lastUsedTraffic' => formatBytes($user->u),
                'unUsedTraffic' => formatBytes($user->transfer_enable - $user->d - $user->u),
            ],
        ]);
    }

    public function shop(): JsonResponse
    {
        $shops = [
            'keys' => [],
            'data' => [],
        ];
        foreach (GoodsCategory::query()->whereStatus(1)->with('goods')->has('goods')->get() as $category) {
            $shops['keys'][] = $category['name'];
            $shops['data'][$category['name']] = $category->goods()->get(['name', 'price', 'traffic'])->append('traffic_label')->toArray();
        }

        return $this->succeed($shops);
    }

    public function getInvite(): JsonResponse
    {
        $user = auth()->user();

        $referral_traffic = formatBytes(sysConfig('referral_traffic'), 'MiB');
        $referral_percent = sysConfig('referral_percent');
        // 邀请码
        $code = $user->invites()->whereStatus(0)->value('code');

        $data['invite_gift'] = trans('user.invite.promotion', [
            'traffic' => $referral_traffic,
            'referral_percent' => $referral_percent * 100,
        ]);

        $userService = new UserService;

        $data['invite_code'] = $code ?? $userService->inviteURI(true);
        $data['invite_url'] = $userService->inviteURI();
        $data['invite_text'] = $data['invite_url'].'&(复制整段文字到浏览器打开即可访问),找梯子最重要的就是稳定,这个已经上线三年了,一直稳定没有被封过,赶紧下载备用吧!'.($code ? '安装后打开填写我的邀请码【'.$code.'】,你还能多得3天会员.' : '');
        // 累计数据
        $data['back_sum'] = ReferralLog::uid()->sum('commission') / 100;
        $data['user_num'] = $user->invitees()->count();
        $data['list'] = $user->invitees()->with('orders')->whereHas('orders', function (Builder $query) {
            $query->where('status', '>=', 2)->where('amount', '>', 0);
        })->selectRaw('username as user_name, UNIX_TIMESTAMP(created_at) as datetime, id')->orderByDesc('created_at')->limit(10)->get()->toArray();
        foreach ($data['list'] as &$item) {
            $item['ref_get'] = ReferralLog::uid()->where('invitee_id', $item['id'])->sum('commission') / 100;
        }

        return $this->succeed(null, $data);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('is_checkin')) {
            return response()->json(['ret' => 0, 'title' => trans('common.failed'), 'msg' => trans('user.home.attendance.disable')]);
        }

        // 已签到过，验证是否有效
        if (Cache::has('userCheckIn_'.$user->id)) {
            return response()->json(['ret' => 0, 'title' => trans('common.success'), 'msg' => trans('user.home.attendance.done')]);
        }

        $traffic = random_int((int) sysConfig('min_rand_traffic'), (int) sysConfig('max_rand_traffic')) * MiB;

        if (! $user->incrementData($traffic)) {
            return response()->json(['ret' => 0, 'title' => trans('common.failed'), 'msg' => trans('user.home.attendance.failed')]);
        }
        Helpers::addUserTrafficModifyLog($user->id, $user->transfer_enable, $user->transfer_enable + $traffic, trans('user.home.attendance.attribute'));

        // 多久后可以再签到
        $ttl = sysConfig('traffic_limit_time') ? sysConfig('traffic_limit_time') * Minute : Day;
        Cache::put('userCheckIn_'.$user->id, '1', $ttl);

        return $this->succeed(null, null, [200, trans('user.home.attendance.success', ['data' => formatBytes($traffic)])]);
    }

    public function proxyCheck(Request $request): JsonResponse
    {
        $md5 = $request->get('md5', '');

        $proxy = (new ProxyService)->getProxyCode('clash');
        if (strtolower(md5(json_encode($proxy))) === strtolower($md5)) {
            return $this->succeed(false);
        }

        return $this->succeed(true, ['md5' => strtolower(md5(json_encode($proxy)))]);
    }

    public function downloadProxies(Request $request)
    {
        $flag = strtolower($request->input('flag') ?? ($request->userAgent() ?? ''));

        return (new ProxyService)->getProxyText($flag === 'v2rayng' ? 'v2rayng' : 'clash', $request->input('type'));
    }

    public function getProxyList(): JsonResponse
    {
        $proxyServer = new ProxyService;

        $servers = [];
        foreach ($proxyServer->getNodeList(null, false) as $node) {
            $server = $proxyServer->getProxyConfig($node);
            if ($server['type'] === '`shadowsocks`' || $server['type'] === 'shadowsocksr') {
                $server['type'] = 1;
            }

            $online_log = $node->onlineLogs->where('log_time', '>=', strtotime('-5 minutes'))->sortBy('log_time')->first(); // 在线人数
            $server['node_ip'] = filter_var($server['host'], FILTER_VALIDATE_IP) ? $server['host'] : gethostbyname($server['host']);
            $server['online'] = $online_log->online_user ?? 0;
            $this->getOnlineCount($server, $server['online']);
            $servers[] = $server;
        }

        return $this->succeed($servers);
    }

    private function getOnlineCount(&$node, int $online): void
    {
        $node['flag'] = $node['area'];

        if ($online < 15) {
            $node['text'] = '⭐ 畅 通';
            $node['color'] = '#28a745';
        } elseif ($online < 30) {
            $node['text'] = '💫 拥 挤';
            $node['color'] = '#ffc107';
        } else {
            $node['text'] = '🔥 爆 满';
            $node['color'] = '#dc3545';
        }
    }

    public function getconfig(): JsonResponse
    {
        $config = $this->clientConfig();
        Arr::forget($config, ['read', 'configured']);

        return $this->succeed(null, ['config' => $config]);
    }

    private function clientConfig(?string $key = null)
    {
        if (! config('client')) {
            Artisan::call('config:cache');
        }

        if (config('client.configured') !== true && config('client.read')) {
            $this->setClientConfig();
        }

        return $key ? config('client.'.$key) : config('client');
    }

    private function setClientConfig(): void
    {
        $ann = Article::type(2)->latest()->first();

        if ($ann) {
            config(['client.notice.title' => $ann->title, 'client.notice.content' => $ann->content]);
        }
        config([
            'client.configured' => true,
            'client.name' => sysConfig('website_name'),
            'client.node_class_name' => Level::all()->pluck('name', 'level')->toArray(),
            'client.baseUrl' => sysConfig('website_url'),
            'client.subscribe_url' => sysConfig('subscribe_domain') ?: sysConfig('website_url'),
            'client.checkinMin' => sysConfig('min_rand_traffic'),
            'client.checkinMax' => sysConfig('max_rand_traffic'),
            'client.invite_gift' => sysConfig('default_traffic') / 1024,
        ]);
    }

    public function checkClientVersion(Request $request): JsonResponse
    {
        $version = $request->input('version');
        $type = $request->input('type');
        $config = $this->clientConfig('vpn_update');
        if (! isset($version, $type)) {
            return $this->failed(ResponseEnum::CLIENT_PARAMETER_ERROR);
        }
        $vpn = $config[$type];

        if (! $config['enable'] || $vpn['version'] === $version) {
            return $this->succeed(null, ['enable' => false]);
        }

        return $this->succeed(null, ['enable' => true, 'download_url' => $vpn['download_url'], 'must' => $vpn['must'], 'message' => $vpn['message']], [200, $vpn['message']]);
    }

    public function ticketList(): JsonResponse
    {
        $ticket = Ticket::where('user_id', auth()->user()->id)->selectRaw('id, title, UNIX_TIMESTAMP(created_at) as datetime, status')->orderBy('created_at', 'DESC')->get();

        return $this->succeed($ticket);
    }
}
