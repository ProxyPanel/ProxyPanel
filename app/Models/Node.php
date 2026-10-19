<?php

namespace App\Models;

use App\Casts\data_rate;
use App\Enums\ProxyType;
use App\Observers\NodeObserver;
use App\Utils\IP;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Log;

/**
 * 节点配置信息.
 */
#[ObservedBy([NodeObserver::class])]
class Node extends Model
{
    protected $table = 'node';

    protected $guarded = [];

    protected $casts = ['type' => 'integer', 'speed_limit' => data_rate::class, 'profile' => 'array', 'details' => 'array'];

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    /** 每节点一行：由 node_heartbeat 上 node_id 的唯一索引保证 */
    public function heartbeat(): HasOne
    {
        return $this->hasOne(NodeHeartbeat::class);
    }

    public function latestHeartbeat(): HasOne
    {
        return $this->hasOne(NodeHeartbeat::class)->ofMany(
            ['log_time' => 'max'],
            function ($query) {
                $query->where('log_time', '>=', strtotime(sysConfig('recently_heartbeat')));
            }
        );
    }

    public function onlineIps(): HasMany
    {
        return $this->hasMany(NodeOnlineIp::class);
    }

    public function onlineLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineLog::class);
    }

    public function latestOnlineLog(): HasOne
    {
        return $this->hasOne(NodeOnlineLog::class)->ofMany(
            ['log_time' => 'max'],
            function ($query) {
                // 与 latestHeartbeat 用同一个阈值：两个窗口不一致时，节点还在线却显示不出在线人数
                $query->where('log_time', '>=', strtotime(sysConfig('recently_heartbeat')));
            }
        );
    }

    public function userDataFlowLogs(): HasMany
    {
        return $this->hasMany(UserDataFlowLog::class);
    }

    public function ruleLogs(): HasMany
    {
        return $this->hasMany(RuleLog::class);
    }

    public function dailyDataFlows(): HasMany
    {
        return $this->hasMany(NodeDailyDataFlow::class);
    }

    public function hourlyDataFlows(): HasMany
    {
        return $this->hasMany(NodeHourlyDataFlow::class);
    }

    public function country(): HasOne
    {
        return $this->HasOne(Country::class, 'code', 'country_code');
    }

    public function ruleGroup(): BelongsTo
    {
        return $this->belongsTo(RuleGroup::class);
    }

    public function relayNode(): BelongsTo
    {
        return $this->belongsTo(__CLASS__);
    }

    public function childNodes(): HasMany
    {
        return $this->hasMany(__CLASS__, 'relay_node_id', 'id');
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class);
    }

    public function auth(): HasOne
    {
        return $this->hasOne(NodeAuth::class);
    }

    public function level_table(): HasOne
    {
        return $this->hasOne(Level::class, 'level', 'level');
    }

    /**
     * 节点上可用账号的资格判据，同时是四个协议 userList 端点与 VNET 对账的唯一基准。
     *
     * 只取下发会用到的列：整行水合会把 user 表 30 多列全读回来，而这个列表一次要 1900 行。
     * speed_limit_bytes 访问器读的是原始 speed_limit，所以它必须在列里。
     */
    public const USER_COLUMNS = ['id', 'port', 'passwd', 'method', 'protocol', 'obfs', 'vmess_id', 'speed_limit', 'enable'];

    public function users(): Collection
    {
        return User::activeUser()
            ->where('level', '>=', $this->level)
            ->where(function ($query) {
                $query->whereIn('user_group_id', $this->userGroups->pluck('id'))->orWhereNull('user_group_id');
            })
            ->select(self::USER_COLUMNS)
            // 固定顺序：userList 的 ETAG 由这份数组算出，行序不保证时同一份数据会反复失效
            ->orderBy('id')
            ->get();
    }

    /** 面板主动推送时逐个寻址用的地址列表：DDNS 走域名，否则用逗号分隔的多 IP */
    public function pushAddresses(): array
    {
        $addresses = $this->is_ddns ? [$this->server] : $this->ips();

        if ($addresses === []) { // 没填 ip 时退回域名，否则这个节点谁都推不到，对账会静默空转
            $addresses = [$this->server];
        }

        return array_values(array_filter($addresses));
    }

    public function refresh_geo(): array
    {
        $ip = $this->ips();
        $geo = explode(',', $this->geo ?? '');
        $ret = ['original' => [
            isset($geo[0]) ? (float) trim($geo[0]) : null,
            isset($geo[1]) ? (float) trim($geo[1]) : null,
        ]];
        if ($ip !== []) {
            $data = IP::getIPGeo($ip[0]); // 复数IP都以第一个为准

            if ($data) {
                $this->updateQuietly(['geo' => ($data['latitude'] ?? null).','.($data['longitude'] ?? null)]);
                $ret['update'] = [$data['latitude'] ?? null, $data['longitude'] ?? null];
            }
        }

        return $ret;
    }

    public function ips(int $type = 4): array
    {
        // 使用DDNS的node先通过gethostbyname获取ip地址
        if ($this->is_ddns ?? 0) { // When ddns is enabled, only domain can be used to check the ip
            $ip = gethostbyname($this->server);
            if (strcmp($ip, $this->server) === 0) {
                Log::warning('获取 【'.$this->server.'】 IP失败'.$ip);
                $ip = '';
            }
        } else {
            $ip = $type === 4 ? $this->ip : $this->ipv6; // check the multiple existing of ip
        }

        return $ip ? array_map('trim', explode(',', $ip)) : [];
    }

    public function isSinglePort(): bool
    { // 单端口的唯一判据：profile.passwd 填了才算单端口，缺失/null/空串都是「没填」（'0' 是合法密码）
        $passwd = $this->profile['passwd'] ?? null;

        return $passwd !== null && $passwd !== '';
    }

    public function getSSRConfig(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->profile['method'] ?? '',
            'protocol' => $this->profile['protocol'] ?? '',
            // 客户端早就从 profile 拿到这个值，节点拿不到就按默认值跑：前段是并发上限、
            // auth_chain_f 的 # 后段是包长表轮换秒数，两侧不同值等于首连即失配
            'protocol_param' => $this->profile['protocol_param'] ?? '',
            'obfs' => $this->profile['obfs'] ?? '',
            'obfs_param' => $this->profile['obfs_param'] ?? '',
            'is_udp' => (int) $this->is_udp,
            'speed_limit' => $this->speed_limit_bytes,
            'client_limit' => $this->client_limit,
            'single' => $this->isSinglePort() ? 1 : 0,
            'port' => (string) $this->port,
            'passwd' => $this->profile['passwd'] ?? '',
            'push_port' => $this->push_port,
            'secret' => $this->auth->secret,
            'redirect_url' => sysConfig('redirect_url'),
        ];
    }

    public function getSpeedLimitBytesAttribute(): int
    { // 节点侧（含 VNet 的 NodeInfo.speed_limit）按字节/秒解释；speed_limit 的 cast 值是 Mbps，只给人看
        return (int) $this->getRawOriginal('speed_limit');
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => ProxyType::tryFrom($this->type)?->label() ?? 'UnKnown',
        );
    }

    protected function host(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->server ?? $this->ip ?? $this->ipv6,
        );
    }
}
