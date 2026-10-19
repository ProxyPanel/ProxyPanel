<?php

namespace Tests\Unit\Services;

use App\Enums\ProxyType;
use App\Models\Country;
use App\Models\Node;
use App\Models\User;
use App\Services\ProxyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

/**
 * 客户端订阅配置端到端生成：真实用户与节点经 ProxyService 落到 Clash / V2rayN / Quantumult X 三种客户端格式.
 */
class SubscriptionGenerationTest extends TestCase
{
    use DatabaseTransactions;

    private const UUID = 'd5f2a1b6-7c8e-4f90-9a1b-2c3d4e5f6071';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 测试进程已有输出，Clash/Quantumult 客户端里的 header() 会报「headers already sent」警告；
        // 这里只吞这一条，其余错误仍交回 PHPUnit 的处理器
        $previous = set_error_handler(null);
        restore_error_handler();
        set_error_handler(static function (int $severity, string $message) use ($previous): bool {
            if ($severity === E_WARNING && str_contains($message, 'Cannot modify header information')) {
                return true;
            }

            return $previous ? (bool) $previous(...func_get_args()) : false;
        });

        // Clash 格式化器把当前主机名写进 DIRECT 规则，服务层调用时没有真实请求
        $_SERVER['HTTP_HOST'] = 'panel.test';

        // 钉住排序与数量上限：种子里的 subscribe_max=3 会截掉夹具节点
        config(['settings.rand_subscribe' => 0, 'settings.subscribe_max' => 0]);

        Country::firstOrCreate(['code' => 'us'], ['name' => '美国']);

        $this->user = User::withoutEvents(fn () => User::create([
            'username' => 'subgen-'.Str::random(8).'@example.com',
            'password' => 'login-pass',
            'nickname' => 'subgen',
            'port' => 12351,
            'passwd' => 'genPass1',
            'vmess_id' => self::UUID,
            'method' => 'aes-256-cfb',
            'protocol' => 'auth_aes128_md5',
            'obfs' => 'tls1.2_ticket_auth',
            'speed_limit' => 5, // data_rate cast：库里落 5*Mbps=5242880 字节/秒
            'level' => 0,
            'status' => 1,
            'enable' => 1,
        ]));

        $this->makeNode(ProxyType::SHADOWSOCKS->value, 'ss-user-port', 'ss-user.example.com', null, ['method' => 'aes-256-gcm']);
        $this->makeNode(ProxyType::SHADOWSOCKSR->value, 'ssr-user-port', 'ssr-user.example.com', null, []);
        $this->makeNode(ProxyType::V2RAY->value, 'vmess-node', 'vmess.example.com', 20000, [
            'method' => 'auto', 'v2_alter_id' => 0, 'v2_net' => 'ws', 'v2_type' => 'none',
            'v2_host' => 'vmess.example.com', 'v2_path' => '/ray', 'v2_tls' => '',
        ]);
        // 单端口 VNET：profile.passwd 填了才算单端口，端口用节点自己的
        $this->makeNode(ProxyType::VNET->value, 'vnet-single', 'vnet-single.example.com', 9001, [
            'method' => 'aes-256-cfb', 'protocol' => 'auth_aes128_md5', 'obfs' => 'tls1.2_ticket_auth',
            'obfs_param' => '', 'passwd' => 'vnetSinglePass',
        ], 10); // 节点限速：库里落 10*Mbps=10485760 字节/秒
        // 每用户端口 VNET：算法只能来自节点 profile（type=4 不吃用户的 method/protocol/obfs）
        $this->makeNode(ProxyType::VNET->value, 'vnet-user-port', 'vnet-user.example.com', null, [
            'method' => 'aes-128-cfb', 'protocol' => 'auth_chain_a', 'obfs' => 'plain', 'obfs_param' => '',
        ]);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_HOST']);
        restore_error_handler();

        parent::tearDown();
    }

    public function test_clash_订阅按客户端字段下发各协议条目(): void
    {
        $yaml = Yaml::parse((new ProxyService($this->user))->buildClientConfig('clashforwindows'));
        $find = function (string $needle) use ($yaml): ?array {
            $proxy = collect($yaml['proxies'])->first(fn ($p) => str_contains($p['name'], $needle));
            $this->assertNotNull($proxy, "Clash 输出缺少 $needle 条目");

            return $proxy;
        };

        // SS 每用户端口：端口与密码都取用户的，加密方式取节点 profile
        $ss = $find('ss-user-port');
        $this->assertSame('ss', $ss['type']);
        $this->assertSame('ss-user.example.com', $ss['server']);
        $this->assertSame(12351, $ss['port']);
        $this->assertSame('aes-256-gcm', $ss['cipher']);
        $this->assertSame('genPass1', $ss['password']);
        $this->assertSame(1, $ss['udp']);

        // SSR 每用户端口：加密三件套取用户设置
        $ssr = $find('ssr-user-port');
        $this->assertSame('ssr', $ssr['type']);
        $this->assertSame(12351, $ssr['port']);
        $this->assertSame('genPass1', $ssr['password']);
        $this->assertSame('aes-256-cfb', $ssr['cipher']);
        $this->assertSame('auth_aes128_md5', $ssr['protocol']);
        $this->assertSame('tls1.2_ticket_auth', $ssr['obfs']);

        // 单端口 VNET 以 SSR 形状下发：节点端口 + 节点密码，protocol-param 带用户端口号:密码
        $vnetSingle = $find('vnet-single');
        $this->assertSame('ssr', $vnetSingle['type'], 'VNET 必须由 SSR 承载');
        $this->assertSame('vnet-single.example.com', $vnetSingle['server']);
        $this->assertSame(9001, $vnetSingle['port'], '单端口节点必须用节点自己的端口');
        $this->assertSame('vnetSinglePass', $vnetSingle['password']);
        $this->assertSame('12351:genPass1', $vnetSingle['protocol-param']);

        // 每用户端口 VNET：端口密码取用户的，算法仍取节点 profile
        $vnetUser = $find('vnet-user-port');
        $this->assertSame('ssr', $vnetUser['type']);
        $this->assertSame(12351, $vnetUser['port']);
        $this->assertSame('genPass1', $vnetUser['password']);
        $this->assertSame('aes-128-cfb', $vnetUser['cipher']);
        $this->assertSame('auth_chain_a', $vnetUser['protocol']);
        $this->assertSame('plain', $vnetUser['obfs']);

        $vmess = $find('vmess-node');
        $this->assertSame('vmess', $vmess['type']);
        $this->assertSame('vmess.example.com', $vmess['server']);
        $this->assertSame(20000, $vmess['port']);
        $this->assertSame(self::UUID, $vmess['uuid']);
        $this->assertSame(0, $vmess['alterId']);
        $this->assertSame('auto', $vmess['cipher']);
        $this->assertSame('ws', $vmess['network']);
        $this->assertSame('/ray', $vmess['ws-opts']['path']);
        $this->assertSame('vmess.example.com', $vmess['ws-opts']['headers']['Host']);
    }

    public function test_v2rayn_订阅以_url_scheme_下发凭据与单端口参数(): void
    {
        $payload = base64_decode((new ProxyService($this->user))->buildClientConfig('v2rayn'));

        // ss:// 行：「加密方式:密码」再套一层 base64url，主机与端口为明文
        $this->assertStringContainsString(
            'ss://'.base64url_encode('aes-256-gcm:genPass1').'@ss-user.example.com:12351#'.rawurlencode('ss-user-port'),
            $payload
        );

        preg_match('#vmess://([A-Za-z0-9+/=]+)#', $payload, $matches);
        $vmess = json_decode(base64_decode($matches[1] ?? ''), true);
        $this->assertSame('vmess-node', $vmess['ps']);
        $this->assertSame('vmess.example.com', $vmess['add']);
        $this->assertSame(20000, $vmess['port']);
        $this->assertSame(self::UUID, $vmess['id']);
        $this->assertSame(0, $vmess['aid']);
        $this->assertSame('ws', $vmess['net']);
        $this->assertSame('/ray', $vmess['path']);

        // 单端口 VNET 走 ssr://：节点端口 + 节点密码，protoparam 是用户端口号:密码
        $single = $this->decodeSsr($payload, 'vnet-single.example.com');
        $this->assertSame('vnet-single.example.com:9001:auth_aes128_md5:aes-256-cfb:tls1.2_ticket_auth:', $single['prefix']);
        $this->assertSame('vnetSinglePass', $single['passwd']);
        $this->assertSame('12351:genPass1', base64url_decode($single['params']['protoparam']));
        $this->assertSame('vnet-single', base64url_decode($single['params']['remarks']));

        // 每用户端口 VNET：端口与密码都来自用户，算法来自节点 profile
        $perUser = $this->decodeSsr($payload, 'vnet-user.example.com');
        $this->assertSame('vnet-user.example.com:12351:auth_chain_a:aes-128-cfb:plain:', $perUser['prefix']);
        $this->assertSame('genPass1', $perUser['passwd']);
        $this->assertSame('', $perUser['params']['protoparam']);
    }

    public function test_quantumult_x_订阅按文本行下发各协议(): void
    {
        $text = base64_decode((new ProxyService($this->user))->buildClientConfig('quantumult%20x'));

        $this->assertStringContainsString('shadowsocks=ss-user.example.com:12351,method=aes-256-gcm,password=genPass1,fast-open=true,udp-relay=1,tag=ss-user-port', $text);
        $this->assertStringContainsString('shadowsocks=vnet-single.example.com:9001,method=aes-256-cfb,password=vnetSinglePass,ssr-protocol=auth_aes128_md5,ssr-protocol-param=12351:genPass1', $text);
        $this->assertStringContainsString('tag=vnet-single', $text);
        $this->assertStringContainsString('shadowsocks=vnet-user.example.com:12351,method=aes-128-cfb,password=genPass1,ssr-protocol=auth_chain_a', $text);
        $this->assertStringContainsString('vmess=vmess.example.com:20000,method=auto,password='.self::UUID, $text);
        $this->assertStringContainsString('tag=vmess-node,obfs=ws', $text);
    }

    public function test_订阅按类型筛选时_vnet_与_ssr_归到同一承载协议(): void
    {
        // type=4 的筛选必须把 SSR(1) 与 VNET(4) 一起取（carrier 归组），其余协议不下发
        foreach ([ProxyType::VNET->value, ProxyType::SHADOWSOCKSR->value] as $type) {
            $payload = base64_decode((new ProxyService($this->user))->buildClientConfig(null, $type));

            $lines = array_values(array_filter(explode(PHP_EOL, $payload)));
            $ssrLines = array_values(array_filter($lines, fn ($l) => str_starts_with($l, 'ssr://')));
            $hosts = array_map(fn ($l) => explode(':', base64url_decode(substr($l, 6)))[0], $ssrLines);
            sort($hosts);

            $this->assertSame(['ssr-user.example.com', 'vnet-single.example.com', 'vnet-user.example.com'], $hosts, 'type='.$type.' 的承载协议筛选结果不符');
            $this->assertSame([], array_values(array_filter($lines, fn ($l) => str_starts_with($l, 'ss://') || str_starts_with($l, 'vmess://'))));
        }
    }

    public function test_客户端订阅格式不携带限速字段(): void
    {
        // 限速以字节/秒只经节点侧 payload 下发（speed_limit_bytes）；客户端三种格式都不该出现限速键或字节值
        $outputs = [
            'clash' => (new ProxyService($this->user))->buildClientConfig('clashforwindows'),
            'v2rayn' => base64_decode((new ProxyService($this->user))->buildClientConfig('v2rayn')),
            'quantumult' => base64_decode((new ProxyService($this->user))->buildClientConfig('quantumult%20x')),
        ];

        foreach ($outputs as $client => $output) {
            foreach (['speed_limit', '5242880', '10485760'] as $needle) {
                $this->assertStringNotContainsString($needle, $output, $client.' 输出不应携带限速字段');
            }
        }
    }

    private function makeNode(int $type, string $name, string $host, ?int $port, array $profile, int $speedLimit = 0): Node
    {
        // 建节点会触发广播与推送任务，这里只要一行可查的节点数据
        return Node::withoutEvents(fn () => Node::create([
            'name' => $name,
            'type' => $type,
            'server' => $host,
            'port' => $port,
            'level' => 0,
            'status' => 1,
            'is_display' => 3,
            'is_udp' => 1,
            'country_code' => 'us',
            'profile' => $profile,
            'speed_limit' => $speedLimit,
        ]));
    }

    /** 解出一条 ssr:// 行：prefix 为「host:port:protocol:method:obfs:」，passwd 与 params 均已还原明文 */
    private function decodeSsr(string $payload, string $host): array
    {
        foreach (explode(PHP_EOL, $payload) as $line) {
            if (! str_starts_with($line, 'ssr://')) {
                continue;
            }

            $decoded = base64url_decode(substr($line, 6));
            if (! str_contains($decoded, $host)) {
                continue;
            }

            [$body, $query] = explode('/?', $decoded, 2);
            parse_str($query, $params);
            $segments = explode(':', $body, 6);

            return [
                'prefix' => implode(':', array_slice($segments, 0, 5)).':',
                'passwd' => base64url_decode($segments[5]),
                'params' => $params,
            ];
        }

        $this->fail("订阅输出中没有主机为 $host 的 ssr:// 条目");
    }
}
