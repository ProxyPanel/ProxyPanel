<?php

namespace Tests\Unit\Clients;

use App\Utils\Clients\Formatters\Clash;
use App\Utils\Clients\Formatters\ClashMeta;
use App\Utils\Clients\Formatters\Stash;
use App\Utils\Clients\Formatters\Surge;
use App\Utils\Clients\Formatters\Text;
use PHPUnit\Framework\TestCase;

/**
 * 四个此前无人断言的客户端出口（Surge / Stash / ClashMeta / Text）按现状钉住：
 * 每个出口支持哪些协议、必填键缺失时是否还出得来。输入用的是 `ProxyService::generateNodeConfig()`
 * 的真实产出形状，因此这些断言即是「收 Clash 家族」重构前的行为基线。
 */
class ClientFormatterOutputTest extends TestCase
{
    private const UUID = 'd5f2a1b6-7c8e-4f90-9a1b-2c3d4e5f6071';

    /** 每种协议一条，形状与 generateNodeConfig 的输出一致 */
    private function servers(): array
    {
        $base = ['id' => 1, 'country' => 'us', 'labels' => [], 'area' => '美国', 'group' => 'Probe', 'udp' => 1];

        return [
            $base + ['type' => 'shadowsocks', 'name' => 'ss-node', 'host' => 'ss-user.example.com', 'port' => 12351, 'passwd' => 'genPass1', 'method' => 'aes-256-gcm'],
            $base + ['type' => 'shadowsocksr', 'name' => 'ssr-node', 'host' => 'ssr-user.example.com', 'port' => 12351, 'passwd' => 'genPass1', 'method' => 'aes-256-cfb', 'protocol' => 'auth_aes128_md5', 'obfs' => 'tls1.2_ticket_auth'],
            $base + ['type' => 'vmess', 'name' => 'vmess-node', 'host' => 'vmess.example.com', 'port' => 20000, 'uuid' => self::UUID, 'method' => 'auto', 'v2_alter_id' => 0, 'v2_net' => 'ws', 'v2_type' => 'none', 'v2_host' => 'vmess.example.com', 'v2_path' => '/ray', 'v2_tls' => ''],
            $base + ['type' => 'trojan', 'name' => 'trojan-node', 'host' => 'trojan.example.com', 'port' => 443, 'passwd' => 'genPass1', 'allow_insecure' => 0],
            $base + ['type' => 'hysteria2', 'name' => 'h2-node', 'host' => 'h2.example.com', 'port' => 8443, 'passwd' => '12351:genPass1', 'allow_insecure' => 0, 'sni' => 'h2.example.com', 'obfs' => 'salamander', 'obfs_param' => 'x', 'ports' => '1000-2000'],
        ];
    }

    private function names(array $out): array
    {
        return array_column($out['proxies'], 'name');
    }

    public function test_clash_出口按类型名单排除_hysteria2(): void
    {
        $this->assertSame(['ss-node', 'ssr-node', 'vmess-node', 'trojan-node'], $this->names(Clash::build($this->servers())));
    }

    public function test_clashmeta_是家族里唯一带混淆也下发_hysteria2_的出口(): void
    {
        $out = ClashMeta::build($this->servers());

        $this->assertSame(['ss-node', 'ssr-node', 'vmess-node', 'trojan-node', 'h2-node'], $this->names($out));

        $h2 = collect($out['proxies'])->firstWhere('name', 'h2-node');
        $this->assertSame('hysteria2', $h2['type']);
        $this->assertSame('1000-2000', $h2['ports']);
        // obfs 存在时混淆密码必须跟着下去，否则客户端握手会失败
        $this->assertSame('x', $h2['obfs-password']);
    }

    public function test_stash_的_hysteria2_只在下发无混淆的节点时出得来(): void
    {
        // 名单含 hysteria2，但构建器自己按 obfs 键闸门：带混淆就整条丢弃（Stash 侧不支持 obfs）
        $names = $this->names(Stash::build($this->servers()));
        $this->assertSame(['ss-node', 'ssr-node', 'vmess-node', 'trojan-node'], $names, '基线节点带 obfs，h2 必须被丢弃');

        $withoutObfs = $this->servers();
        unset($withoutObfs[4]['obfs']);
        $out = Stash::build($withoutObfs);

        $this->assertSame(['ss-node', 'ssr-node', 'vmess-node', 'trojan-node', 'h2-node'], $this->names($out));

        $h2 = collect($out['proxies'])->firstWhere('name', 'h2-node');
        // Stash 用 auth= 而不是 ClashMeta 的 password=，密钥名写错客户端直接认不出
        $this->assertSame('12351:genPass1', $h2['auth']);
        $this->assertArrayNotHasKey('password', $h2);
        $this->assertSame('1000-2000', $h2['ports']);
    }

    public function test_surge_出口不支持_ssr_但支持其余四种(): void
    {
        $out = Surge::build($this->servers());

        $this->assertStringNotContainsString('ssr-user.example.com', $out['proxies']);
        $this->assertStringContainsString('ss-node=ss,ss-user.example.com,12351,encrypt-method=aes-256-gcm,password=genPass1', $out['proxies']);
        $this->assertStringContainsString('vmess-node=vmess,vmess.example.com,20000,username='.self::UUID, $out['proxies']);
        $this->assertStringContainsString('trojan-node=trojan,trojan.example.com,443', $out['proxies']);
        $this->assertStringContainsString('h2-node', $out['name']);
    }

    public function test_text_出口逐协议给出可读配置(): void
    {
        [$ss, $ssr, $vmess, $trojan, $h2] = $this->servers();

        $this->assertStringContainsString('加密：aes-256-gcm', Text::buildShadowsocks($ss));
        $this->assertStringContainsString('用户ID：'.self::UUID, Text::buildVmess($vmess));
        $this->assertStringContainsString('传输协议：ws', Text::buildVmess($vmess));
        // 单端口 H2 的密码是 port:passwd 拼接，混淆密码可选
        $this->assertStringContainsString('端口跳跃：1000-2000', $this->textAsInHttpRequest(fn () => Text::buildHysteria2($h2)));
        $this->assertStringContainsString('混淆密码：x', $this->textAsInHttpRequest(fn () => Text::buildHysteria2($h2)));
    }

    public function test_text_在可选参数缺行时仍要出得来(): void
    {
        // 非中转的 trojan 节点没有 sni 键、每用户端口的 ssr 节点没有 protocol_param/obfs_param 键：
        // 其余五个格式化器一律 isset() 或 ?? '' 兜底，只有 Text 直读，HTTP 路径上 E_WARNING 会被转成 ErrorException
        [$ss, $ssr, $vmess, $trojan, $h2] = $this->servers();
        $this->assertArrayNotHasKey('sni', $trojan);
        $this->assertArrayNotHasKey('protocol_param', $ssr);
        unset($h2['obfs_param']);

        $trojanText = $this->textAsInHttpRequest(fn () => Text::buildTrojan($trojan));
        $this->assertStringContainsString('密码：genPass1', $trojanText);
        $this->assertStringNotContainsString('SNI：', $trojanText, '没有 sni 就不该写这一行，与同类一致');

        $ssrText = $this->textAsInHttpRequest(fn () => Text::buildShadowsocksr($ssr));
        $this->assertStringContainsString('协议：auth_aes128_md5', $ssrText);
        $this->assertStringContainsString('混淆：tls1.2_ticket_auth', $ssrText);

        $this->assertStringContainsString('混淆密码：', $this->textAsInHttpRequest(fn () => Text::buildHysteria2($h2)));
    }

    /**
     * 与 HTTP 路径同构：`HandleExceptions::handleError()` 在 error_reporting(-1) 下把 E_WARNING 抛成
     * ErrorException，所以这里不能让 PHPUnit 只把警告记一笔。
     */
    private function textAsInHttpRequest(callable $fn): string
    {
        set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0): bool {
            if (error_reporting() & $severity) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }

            return false;
        });

        try {
            return $fn();
        } finally {
            restore_error_handler();
        }
    }
}
