<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\ProxyService;
use ErrorException;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;

/**
 * 订阅生成最容易出错的两点：节点缓存必须按协议类型分开、换用户必须作废。
 *
 * 与 OnlineReportTest 同理继承裸 PHPUnit 的 TestCase —— 启动 Laravel 容器会读取 config 表
 * （需要一个可达的数据库）。这里只挂一个最小的 config 容器，让 app_path()/sysConfig() 可用，
 * 取节点的动作由子类替换掉，因此整个过程不碰数据库。
 */
class ProxyServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $app = new Application(dirname(__DIR__, 3));
        $app->instance('config', new Repository(['settings' => ['website_name' => 'Panel', 'website_url' => 'https://panel.test']]));

        // 没有可用节点时 getServers() 会走 failedProxyReturn(trans(...))，需要一个 translator
        $app->instance('translator', new Translator(new ArrayLoader, 'en'));
    }

    public function test_每种协议类型各取一次节点(): void
    {
        $service = new ProxyServiceProbe($this->user(7));

        $first = $this->decode($service->buildClientConfig(null, 1));
        $vnet = $this->decode($service->buildClientConfig(null, 4));
        $vmess = $this->decode($service->buildClientConfig(null, 2));

        $this->assertSame([1, 4, 2], $service->fetchedTypes, '每种 type 都要各自取一次节点，不能复用上一次的结果');
        $this->assertStringContainsString('host1.test', $first);
        $this->assertStringContainsString('host4.test', $vnet, '同一实例换个 type 后必须重新取节点');
        $this->assertStringContainsString('host2.test', $vmess);

        // 同一类型重复请求才走缓存
        $service->buildClientConfig(null, 1);
        $this->assertSame([1, 4, 2], $service->fetchedTypes);
    }

    public function test_换用户必须作废节点缓存(): void
    {
        $service = new ProxyServiceProbe($this->user(7));
        $this->assertStringContainsString('pass-user7', $this->credentials($service->buildClientConfig(null, 1)));

        $service->setUser($this->user(8));

        $this->assertStringContainsString('pass-user8', $this->credentials($service->buildClientConfig(null, 1)), '换用户后必须重新取节点，否则会把上一个用户的凭据发给新用户');
        $this->assertSame([1, 1], $service->fetchedTypes);
    }

    /**
     * 节点全不可用时给的占位配置必须与请求的协议类型一致（键是协议编号，不是顺序数组）。
     * 这里锁住 VNET（4）被错当成 hysteria2、以及 Hysteria2（5）越界的回归。
     */
    public function test_无可用节点时按协议类型返回占位配置(): void
    {
        // 占位配置缺字段时只会抛 "Undefined array key" 警告（例如 hysteria2 一度写成 insecure 而不是
        // allow_insecure），输出仍是一份能解析的订阅，所以这里把警告升级成异常，不让字段错位悄悄溜过。
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $cases = [
                [null, 'ss://'],
                [0, 'ss://'],
                [1, 'ssr://'],
                [2, 'vmess://'],
                [3, 'trojan://'],
                [4, 'ssr://'], // VNET 由 SSR 协议承载，与 config/common.proxy_protocols 一致
                [5, 'hysteria2://'],
            ];

            foreach ($cases as [$type, $scheme]) {
                $payload = $this->decode((new ProxyServiceEmptyProbe($this->user(7)))->buildClientConfig(null, $type));

                $this->assertStringStartsWith($scheme, $payload, 'type='.var_export($type, true).' 的占位配置协议错位');
            }

            // Hysteria2 的占位配置要能解出 insecure 参数（字段名错了就变成默认值，看不出异常）
            $hysteria2 = $this->decode((new ProxyServiceEmptyProbe($this->user(7)))->buildClientConfig(null, 5));
            $this->assertStringContainsString('insecure=0', $hysteria2);

            // VNET 与 SSR 共用同一套字段（方法/混淆/协议），应给出一模一样的占位配置
            $this->assertSame(
                $this->decode((new ProxyServiceEmptyProbe($this->user(7)))->buildClientConfig(null, 1)),
                $this->decode((new ProxyServiceEmptyProbe($this->user(7)))->buildClientConfig(null, 4))
            );
        } finally {
            restore_error_handler();
        }
    }

    private function user(int $id): User
    {
        return (new User)->newFromBuilder(['id' => $id, 'level' => 0, 'port' => 10000 + $id, 'passwd' => 'pass-user'.$id, 'vmess_id' => 'uuid-'.$id]);
    }

    private function decode(string $payload): string
    {
        return base64_decode($payload, true) ?: $payload;
    }

    /** 订阅整体是 base64，其中 ss:// 的「加密方式:密码」又是 base64url，两层都解开才能断言凭据 */
    private function credentials(string $payload): string
    {
        preg_match('#ss://([^@]+)@#', $this->decode($payload), $matches);

        return base64url_decode($matches[1] ?? '');
    }
}

/** 只替换「取节点」这一步：返回带用户与类型标记的节点，便于观察缓存是否失效 */
class ProxyServiceProbe extends ProxyService
{
    /** @var array<int, int|null> 每次实际访问数据库取节点的 type */
    public array $fetchedTypes = [];

    public function __construct(private User $probeUser)
    {
        parent::__construct($probeUser);
    }

    public function setUser(User $user): void
    {
        parent::setUser($user);

        // 子类读不到父类的 private $user，这里自己记一份，用来生成可辨识的节点
        $this->probeUser = $user;
    }

    public function fetchAvailableNodes(?int $type = null, bool $withConfigs = true): array|Collection
    {
        $this->fetchedTypes[] = $type;
        $tag = $type ?? 'all';

        return [[
            'id' => $type ?? 0,
            'name' => 'node-'.$tag.'-user'.$this->probeUser->id,
            'type' => 'shadowsocks',
            'host' => 'host'.$tag.'.test',
            'port' => 8388,
            'method' => 'aes-256-gcm',
            'passwd' => $this->probeUser->passwd,
            'udp' => 1,
        ]];
    }
}

/** 模拟「该用户没有任何可用节点」，用来观察占位配置 */
class ProxyServiceEmptyProbe extends ProxyService
{
    public function fetchAvailableNodes(?int $type = null, bool $withConfigs = true): array
    {
        return [];
    }
}
