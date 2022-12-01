<?php

namespace App\Services;

use App\Components\Client\Text;
use App\Components\Client\URLSchemes;
use App\Helpers\ClientConfig;
use App\Models\Node;
use App\Models\User;
use Arr;

class ProxyServer extends BaseService
{
    use ClientConfig;

    private static $user;

    public function __construct()
    {
        parent::__construct();
        self::$user = auth()->user();
    }

    public function getUser()
    {
        return self::$user;
    }

    public function setUser(User $user)
    {
        self::$user = $user;
    }

    public function getProxyText($target, $type = null)
    {
        $servers = $this->getNodeList($type);
        if (empty($servers)) {
            return $this->failedProxyReturn(trans('errors.subscribe.none'), $type);
        }

        if (sysConfig('rand_subscribe')) {// 打乱数组
            $servers = Arr::shuffle($servers);
        }

        $max = (int) sysConfig('subscribe_max');
        if ($max) { // 订阅数量限制
            $servers = Arr::random($servers, $max);
        }

        return $this->clientConfig($servers, $target);
    }

    public function getNodeList($type = null, $isConfig = true)
    {
        $query = self::$user->nodes()->whereIn('is_display', [2, 3]); // 获取这个账号可用节点

        if (isset($type)) {
            if ($type === 1) {
                $query = $query->whereIn('type', [1, 4]);
            } elseif ($type) {
                $query = $query->whereType($type);
            }
        }

        $nodes = $query->orderByDesc('sort')->orderBy('id')->get();

        if ($isConfig) {
            $servers = [];
            foreach ($nodes as $node) {
                $servers[] = $this->getProxyConfig($node);
            }

            return $servers;
        }

        return $nodes;
    }

    public function getProxyConfig(Node $node) // 提取节点信息
    {
        $user = self::$user;
        $config = [
            'id'    => $node->id,
            'name'  => $node->name,
            'area'  => $node->country->name,
            'host'  => $node->host,
            'group' => sysConfig('website_name'),
            'udp'   => $node->is_udp,
        ];

        if ($node->relay_node_id) {
            $parentConfig = $this->getProxyConfig($node->relayNode);
            $config = array_merge($config, Arr::except($parentConfig, ['id', 'name', 'host', 'group', 'udp']));
            if ($parentConfig['type'] === 'trojan') {
                $config['sni'] = $parentConfig['host'];
            }
            $config['port'] = $node->port;
        } else {
            switch ($node->type) {
                case 0:
                    $config = array_merge($config, [
                        'type'   => 'shadowsocks',
                        'passwd' => $user->passwd,
                    ], $node->profile);
                    if ($node->port) {
                        $config['port'] = $node->port;
                    } else {
                        $config['port'] = $user->port;
                    }
                    break;
                case 2:
                    $config = array_merge($config, [
                        'type' => 'v2ray',
                        'port' => $node->port,
                        'uuid' => $user->vmess_id,
                    ], $node->profile);
                    break;
                case 3:
                    $config = array_merge($config, [
                        'type'   => 'trojan',
                        'port'   => $node->port,
                        'passwd' => $user->passwd,
                        'sni'    => '',
                    ], $node->profile);
                    break;
                case 1:
                case 4:
                default:
                    $config = array_merge($config, ['type' => 'shadowsocksr'], $node->profile);
                    if ($node->profile['passwd'] && $node->port) {
                        //单端口使用中转的端口
                        $config['port'] = $node->port;
                        $config['protocol_param'] = $user->port.':'.$user->passwd;
                    } else {
                        $config['port'] = $user->port;
                        $config['passwd'] = $user->passwd;
                        if ($node->type === 1) {
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

    public function failedProxyReturn(string $text, $type = 1): string
    {
        switch ($type) {
            case 2:
                $url = sysConfig('website_url');
                $result = 'vmess://'.base64url_encode(json_encode([
                    'v'    => '2',
                    'ps'   => $text,
                    'add'  => $url,
                    'port' => 0,
                    'id'   => 0,
                    'aid'  => 0,
                    'net'  => 'tcp',
                    'type' => 'none',
                    'host' => $url,
                    'path' => '/',
                    'tls'  => 'tls',
                ], JSON_PRETTY_PRINT));
                break;
            case 3:
                $result = 'trojan://0@0.0.0.0:0?peer=0.0.0.0#'.rawurlencode($text);
                break;
            case 1:
            case 4:
            default:
                $result = 'ssr://'.base64url_encode('0.0.0.0:0:origin:none:plain:'.base64url_encode('0000').'/?obfsparam=&protoparam=&remarks='.base64url_encode($text).'&group='.base64url_encode(sysConfig('website_name')).'&udpport=0&uot=0');
                break;
        }

        return $result.PHP_EOL;
    }

    public function getProxyCode($target, $type = null) // 客户端用代理信息
    {
        $servers = $this->getNodeList($type);
        if (empty($servers)) {
            return null;
        }

        return $this->clientConfig($servers, $target);
    }

    public function getUserProxyConfig(array $server, bool $is_url): ?string // 用户显示用代理信息
    {
        $type = $is_url ? new URLSchemes() : new Text();
        switch ($server['type']) {
            case'shadowsocks':
                $data = $type->buildShadowsocks($server);
                break;
            case 'shadowsocksr':
                $data = $type->buildShadowsocksr($server);
                break;
            case 'v2ray':
                $data = $type->buildVmess($server);
                break;
            case 'trojan':
                $data = $type->buildTrojan($server);
                break;
            default:
                $data = null;
        }

        return $data;
    }
}
