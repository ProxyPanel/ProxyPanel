<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点配置信息.
 */
class Node extends Model
{
    protected $table = 'ss_node';
    protected $guarded = ['id', 'created_at'];

    public function labels(): HasMany
    {
        return $this->hasMany(NodeLabel::class);
    }

    public function heartBeats(): HasMany
    {
        return $this->hasMany(NodeHeartBeat::class);
    }

    public function onlineLogs(): HasMany
    {
        return $this->hasMany(NodeOnlineLog::class);
    }

    public function pingLogs(): HasMany
    {
        return $this->hasMany(NodePing::class);
    }

    public function dailyDataFlows(): HasMany
    {
        return $this->hasMany(NodeDailyDataFlow::class);
    }

    public function hourlyDataFlows(): HasMany
    {
        return $this->hasMany(NodeHourlyDataFlow::class);
    }

    public function rules(): hasMany
    {
        return $this->hasMany(NodeRule::class);
    }

    public function ruleGroup(): hasOne
    {
        return $this->hasOne(RuleGroupNode::class);
    }

    public function auth(): HasOne
    {
        return $this->hasOne(NodeAuth::class);
    }

    public function level_table(): HasOne
    {
        return $this->hasOne(Level::class, 'level', 'level');
    }

    public function scopeUserAllowNodes($query, $user_group_id, $user_level)
    {
        $userGroup = UserGroup::find($user_group_id);
        if ($userGroup) {
            $query->whereIn('id', $userGroup->nodes);
        }

        return $query->whereStatus(1)->where('level', '<=', $user_level ?: 0);
    }

    public function config($user)
    {
        $config = [
            'id' => $this->id,
            'name' => $this->name,
            'host' => $this->is_relay ? $this->relay_server : ($this->server ?: $this->ip),
            'group' => sysConfig('website_name'),
        ];
        switch ($this->type) {
            case 2:
                $config = array_merge($config, [
                    'type' => 'v2ray',
                    'port' => $this->is_relay ? $this->relay_port : $this->v2_port,
                    'uuid' => $user->vmess_id,
                    'method' => $this->v2_method,
                    'v2_alter_id' => $this->v2_alter_id,
                    'v2_net' => $this->v2_net,
                    'v2_type' => $this->v2_type,
                    'v2_host' => $this->v2_host,
                    'v2_path' => $this->v2_path,
                    'v2_tls' => $this->v2_tls ? 'tls' : '',
                    'udp' => $this->is_udp,
                ]);
                break;
            case 3:
                $config = array_merge($config, [
                    'type' => 'trojan',
                    'port' => $this->is_relay ? $this->relay_port : $this->v2_port,
                    'passwd' => $user->passwd,
                    'sni' => $this->is_relay ? $this->server : '',
                    'udp' => $this->is_udp,
                ]);
                break;
            case 1:
            case 4:
                $config = array_merge($config, [
                    'type' => $this->compatible ? 'shadowsocks' : 'shadowsocksr',
                    'method' => $this->method,
                    'protocol' => $this->protocol,
                    'obfs' => $this->obfs,
                    'obfs_param' => $this->obfs_param,
                    'udp' => $this->is_udp,
                ]);
                if ($this->single) {
                    //单端口使用中转的端口
                    $config['port'] = $this->is_relay ? $this->relay_port : $this->port;
                    $config['passwd'] = $this->passwd;
                    $config['protocol_param'] = $user->port.':'.$user->passwd;
                } else {
                    $config['port'] = $user->port;
                    $config['passwd'] = $user->passwd;
                    $config['protocol_param'] = $this->protocol_param;
                    if ($this->type === 1) {
                        $config['method'] = $user->method;
                        $config['protocol'] = $user->protocol;
                        $config['obfs'] = $user->obfs;
                    }
                }

                break;
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

    public function getNodeAccessUsersAttribute()
    {
        return User::nodeAllowUsers($this->attributes['id'], $this->attributes['level'])->get();
    }

    public function getTypeLabelAttribute(): string
    {
        switch ($this->attributes['type']) {
            case 1:
                $type_label = 'ShadowsocksR';
                break;
            case 2:
                $type_label = 'V2Ray';
                break;
            case 3:
                $type_label = 'Trojan';
                break;
            case 4:
                $type_label = 'VNet';
                break;
            default:
                $type_label = 'UnKnown';
        }

        return $type_label;
    }
}
