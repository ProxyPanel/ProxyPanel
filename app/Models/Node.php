<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 节点配置信息
 *
 * @property int                                                                   $id
 * @property int                                                                   $type           服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet
 * @property string                                                                $name           名称
 * @property string                                                                $country_code   国家代码
 * @property string|null                                                           $server         服务器域名地址
 * @property string|null                                                           $ip             服务器IPV4地址
 * @property string|null                                                           $ipv6           服务器IPV6地址
 * @property int                                                                   $level          等级：0-无等级，全部可见
 * @property int                                                                   $speed_limit    节点限速，为0表示不限速，单位Byte
 * @property int                                                                   $client_limit   设备数限制
 * @property string|null                                                           $relay_server   中转地址
 * @property int|null                                                              $relay_port     中转端口
 * @property string|null                                                           $description    节点简单描述
 * @property string|null                                                           $geo            节点地理位置
 * @property string                                                                $method         加密方式
 * @property string                                                                $protocol       协议
 * @property string|null                                                           $protocol_param 协议参数
 * @property string                                                                $obfs           混淆
 * @property string|null                                                           $obfs_param     混淆参数
 * @property float                                                                 $traffic_rate   流量比率
 * @property int                                                                   $is_subscribe   是否允许用户订阅该节点：0-否、1-是
 * @property int                                                                   $is_ddns        是否使用DDNS：0-否、1-是
 * @property int                                                                   $is_relay       是否中转节点：0-否、1-是
 * @property int                                                                   $is_udp         是否启用UDP：0-不启用、1-启用
 * @property int                                                                   $push_port      消息推送端口
 * @property int                                                                   $detection_type 节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部
 * @property int                                                                   $compatible     兼容SS
 * @property int                                                                   $single         启用单端口功能：0-否、1-是
 * @property int|null                                                              $port           单端口的端口号或连接端口号
 * @property string|null                                                           $passwd         单端口的连接密码
 * @property int                                                                   $sort           排序值，值越大越靠前显示
 * @property int                                                                   $status         状态：0-维护、1-正常
 * @property int                                                                   $v2_alter_id    V2Ray额外ID
 * @property int                                                                   $v2_port        V2Ray服务端口
 * @property string                                                                $v2_method      V2Ray加密方式
 * @property string                                                                $v2_net         V2Ray传输协议
 * @property string                                                                $v2_type        V2Ray伪装类型
 * @property string                                                                $v2_host        V2Ray伪装的域名
 * @property string                                                                $v2_path        V2Ray的WS/H2路径
 * @property int                                                                   $v2_tls         V2Ray连接TLS：0-未开启、1-开启
 * @property string|null                                                           $tls_provider   V2Ray节点的TLS提供商授权信息
 * @property \Illuminate\Support\Carbon                                            $created_at     创建时间
 * @property \Illuminate\Support\Carbon                                            $updated_at     最后更新时间
 * @property-read \App\Models\NodeAuth|null                                        $auth
 * @property-read string                                                           $level_name
 * @property-read string                                                           $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NodeLabel[] $label
 * @property-read int|null                                                         $label_count
 * @method static Builder|Node groupNodePermit($group_id = 0)
 * @method static Builder|Node newModelQuery()
 * @method static Builder|Node newQuery()
 * @method static Builder|Node query()
 * @method static Builder|Node whereClientLimit($value)
 * @method static Builder|Node whereCompatible($value)
 * @method static Builder|Node whereCountryCode($value)
 * @method static Builder|Node whereCreatedAt($value)
 * @method static Builder|Node whereDescription($value)
 * @method static Builder|Node whereDetectionType($value)
 * @method static Builder|Node whereGeo($value)
 * @method static Builder|Node whereId($value)
 * @method static Builder|Node whereIp($value)
 * @method static Builder|Node whereIpv6($value)
 * @method static Builder|Node whereIsDdns($value)
 * @method static Builder|Node whereIsRelay($value)
 * @method static Builder|Node whereIsSubscribe($value)
 * @method static Builder|Node whereIsUdp($value)
 * @method static Builder|Node whereLevel($value)
 * @method static Builder|Node whereMethod($value)
 * @method static Builder|Node whereName($value)
 * @method static Builder|Node whereObfs($value)
 * @method static Builder|Node whereObfsParam($value)
 * @method static Builder|Node wherePasswd($value)
 * @method static Builder|Node wherePort($value)
 * @method static Builder|Node whereProtocol($value)
 * @method static Builder|Node whereProtocolParam($value)
 * @method static Builder|Node wherePushPort($value)
 * @method static Builder|Node whereRelayPort($value)
 * @method static Builder|Node whereRelayServer($value)
 * @method static Builder|Node whereServer($value)
 * @method static Builder|Node whereSingle($value)
 * @method static Builder|Node whereSort($value)
 * @method static Builder|Node whereSpeedLimit($value)
 * @method static Builder|Node whereStatus($value)
 * @method static Builder|Node whereTlsProvider($value)
 * @method static Builder|Node whereTrafficRate($value)
 * @method static Builder|Node whereType($value)
 * @method static Builder|Node whereUpdatedAt($value)
 * @method static Builder|Node whereV2AlterId($value)
 * @method static Builder|Node whereV2Host($value)
 * @method static Builder|Node whereV2Method($value)
 * @method static Builder|Node whereV2Net($value)
 * @method static Builder|Node whereV2Path($value)
 * @method static Builder|Node whereV2Port($value)
 * @method static Builder|Node whereV2Tls($value)
 * @method static Builder|Node whereV2Type($value)
 * @mixin \Eloquent
 */
class Node extends Model {
	protected $table = 'ss_node';

	public function label(): HasMany {
		return $this->hasMany(NodeLabel::class, 'node_id', 'id');
	}

	public function auth(): HasOne {
		return $this->hasOne(NodeAuth::class, 'node_id', 'id');
	}

	public function getLevelNameAttribute(): string {
		return Level::whereLevel($this->attributes['level'])->first()->name;
	}

	// Node查询，查用户所在分组Node权限
	public function scopeGroupNodePermit($query, $group_id = 0) {
		$userGroup = UserGroup::find($group_id);
		if($userGroup){
			return $query->whereIn('id', $userGroup->nodes);
		}
		return $query;
	}

	public function getTypeLabelAttribute(): string {
		switch($this->attributes['type']){
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
