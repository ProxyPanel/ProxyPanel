<?php

namespace App\Models;

use App\Components\IP;
use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Log;

/**
 * 节点配置信息.
 */
class Node extends Model
{
    protected $table = 'node';
    protected $guarded = [];
    protected $casts = [
        'profile' => 'array',
    ];

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(NodeHeartbeat::class);
    }

    public function onlineIps(): HasMany
    {
        return $this->hasMany(NodeOnlineIp::class);
    }

    public function onlineLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineLog::class);
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

    public function ruleGroup(): BelongsTo
    {
        return $this->belongsTo(RuleGroup::class);
    }

    public function relayNode(): BelongsTo
    {
        return $this->belongsTo(Node::class);
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

    public function users()
    {
        return User::activeUser()
            ->where('level', '>=', $this->attributes['level'])
            ->where(function ($query) {
                $query->whereIn('user_group_id', $this->userGroups->pluck('id'))->orWhereNull('user_group_id');
            })
            ->get();
    }

    public function refresh_geo(): bool
    {
        $ip = $this->ips();
        if ($ip !== []) {
            $data = IP::IPSB($ip[0]); // 复数IP都以第一个为准

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
        if ($this->attributes['is_ddns']) { // When ddns is enable, only domain can be used to check the ip
            $ip = gethostbyname($this->attributes['server']);
            if (strcmp($ip, $this->attributes['server']) === 0) {
                Log::warning('获取 【'.$this->attributes['server'].'】 IP失败'.$ip);
                $ip = '';
            }
        } else {
            $ip = $type === 4 ? $this->attributes['ip'] : $this->attributes['ipv6']; // check the multiple existing of ip
        }

        return array_map('trim', explode(',', $ip));
    }

    public function getConfig(User $user)
    {
        $config = [
            'id'    => $this->id,
            'name'  => $this->name,
            'host'  => $this->host,
            'group' => sysConfig('website_name'),
            'udp'   => $this->is_udp,
        ];

        if ($this->relay_node_id) {
            $parentConfig = $this->relayNode->getConfig($user);
            $config = array_merge($config, Arr::except($parentConfig, ['id', 'name', 'host', 'group', 'udp']));
            if ($parentConfig['type'] === 'trojan') {
                $config['sni'] = $parentConfig['host'];
            }
            $config['port'] = $this->port;
        } else {
            switch ($this->type) {
                case 0:
                    $config = array_merge($config, [
                        'type'   => 'shadowsocks',
                        'passwd' => $user->passwd,
                    ], $this->profile);
                    if ($this->port) {
                        $config['port'] = $this->port;
                    } else {
                        $config['port'] = $user->port;
                    }
                    break;
                case 2:
                    $config = array_merge($config, [
                        'type' => 'v2ray',
                        'port' => $this->port,
                        'uuid' => $user->vmess_id,
                    ], $this->profile);
                    break;
                case 3:
                    $config = array_merge($config, [
                        'type'   => 'trojan',
                        'port'   => $this->port,
                        'passwd' => $user->passwd,
                        'sni'    => '',
                    ], $this->profile);
                    break;
                case 1:
                case 4:
                    $config = array_merge($config, [
                        'type' => 'shadowsocksr',
                    ], $this->profile);
                    if ($this->profile['passwd'] && $this->port) {
                        //单端口使用中转的端口
                        $config['port'] = $this->port;
                        $config['protocol_param'] = $user->port.':'.$user->passwd;
                    } else {
                        $config['port'] = $user->port;
                        $config['passwd'] = $user->passwd;
                        if ($this->type === 1) {
                            $config['method'] = $user->method;
                            $config['protocol'] = $user->protocol;
                            $config['obfs'] = $user->obfs;
                        }
                    }

                    break;
            }
        }

        return $config;
    }

    public function getSpeedLimitAttribute($value)
    {
        return $value / Mbps;
    }

    public function setSpeedLimitAttribute($value)
    {
        return $this->attributes['speed_limit'] = $value * Mbps;
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            0 => 'Shadowsocks',
            1 => 'ShadowsocksR',
            2 => 'V2Ray',
            3 => 'Trojan',
            4 => 'VNet',
        ][$this->attributes['type']] ?? 'UnKnown';
    }

    public function getHostAttribute(): string
    {
        return $this->server ?? $this->ip ?? $this->ipv6;
    }
}
