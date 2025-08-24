<?php

namespace App\Models;

use App\Casts\data_rate;
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

    protected $casts = ['speed_limit' => data_rate::class, 'profile' => 'array', 'details' => 'array'];

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(NodeHeartbeat::class);
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
                $query->where('log_time', '>=', strtotime('-5 minutes'));
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

    public function users(): Collection
    {
        return User::activeUser()
            ->where('level', '>=', $this->level)
            ->where(function ($query) {
                $query->whereIn('user_group_id', $this->userGroups->pluck('id'))->orWhereNull('user_group_id');
            })
            ->get();
    }

    public function refresh_geo(): bool
    {
        $ip = $this->ips();
        if ($ip !== []) {
            $data = IP::getIPGeo($ip[0]); // 复数IP都以第一个为准

            if ($data) {
                self::withoutEvents(function () use ($data) {
                    $this->update(['geo' => ($data['latitude'] ?? null).','.($data['longitude'] ?? null)]);
                });

                return true;
            }
        }

        return false;
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

        return array_map('trim', explode(',', $ip));
    }

    public function getSSRConfig(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->profile['method'] ?? '',
            'protocol' => $this->profile['protocol'] ?? '',
            'obfs' => $this->profile['obfs'] ?? '',
            'obfs_param' => $this->profile['obfs_param'] ?? '',
            'is_udp' => (int) $this->is_udp,
            'speed_limit' => $this->getRawOriginal('speed_limit'),
            'client_limit' => $this->client_limit,
            'single' => isset($this->profile['passwd']) ? 1 : 0,
            'port' => (string) $this->port,
            'passwd' => $this->profile['passwd'] ?? '',
            'push_port' => $this->push_port,
            'secret' => $this->auth->secret,
            'redirect_url' => sysConfig('redirect_url'),
        ];
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                0 => 'Shadowsocks',
                1 => 'ShadowsocksR',
                2 => 'V2Ray',
                3 => 'Trojan',
                4 => 'VNet',
                default => 'UnKnown',
            },
        );
    }

    protected function host(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->server ?? $this->ip ?? $this->ipv6,
        );
    }
}
