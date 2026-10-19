<?php

namespace Tests\Unit\Clients;

use App\Utils\Clients\Clash;
use App\Utils\Clients\Formatters\Text;
use App\Utils\Clients\Quantumult;
use App\Utils\Clients\Shadowrocket;
use App\Utils\Clients\Surge;
use App\Utils\Clients\V2rayN;
use App\Utils\Library\Templates\Client;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * 订阅出口的入口契约：`ProxyService::buildClientConfig()` 按 UA 命中后统一以 `(servers, user, target)`
 * 三参调用，接口必须把这件事写死，否则五个入口的签名会各自漂移（空接口时 PHP 不做任何校验）。
 */
class ClientContractTest extends TestCase
{
    private const ENTRIES = [Clash::class, Quantumult::class, Shadowrocket::class, Surge::class, V2rayN::class];

    public function test_接口声明了_get_config_三参契约(): void
    {
        $methods = (new ReflectionClass(Client::class))->getMethods();

        $this->assertSame(['getConfig'], array_map(fn ($m) => $m->getName(), $methods), 'Client 接口应当只声明 getConfig 这一份契约');

        $params = $methods[0]->getParameters();
        $this->assertCount(3, $params);
        $this->assertSame('target', $params[2]->getName());
        $this->assertFalse($params[2]->isOptional(), '入口必须拿到 UA，不能靠默认值缺席');
        $this->assertTrue($params[2]->getType()?->isBuiltin(), '$target 必须是原生 string');
        $this->assertFalse($params[2]->getType()->allowsNull(), '$target 可空会逼每个入口补 ?? 兜底');
        $this->assertSame('string', (string) $methods[0]->getReturnType());
    }

    public function test_每个入口都按契约声明签名(): void
    {
        foreach (self::ENTRIES as $class) {
            $method = new ReflectionMethod($class, 'getConfig');

            $this->assertSame(3, $method->getNumberOfParameters(), $class.' 必须收三个参数');
            $this->assertSame('target', $method->getParameters()[2]->getName(), $class.' 的第三参必须叫 $target');
            $this->assertSame('string', (string) $method->getParameters()[2]->getType(), $class.' 的 $target 必须是必填 string');
            // 实际产出都是字符串（订阅响应按 string 返回），array|string 是过宽的自述
            $this->assertSame('string', (string) $method->getReturnType(), $class.' 的返回类型该收敛到 string');
        }
    }

    public function test_文本出口也在同一份格式化契约里(): void
    {
        // 反证用：Text 少实现任何一个 build* 就会在加载期炸，这里确认五个都在
        foreach (['Shadowsocks', 'Shadowsocksr', 'Vmess', 'Trojan', 'Hysteria2'] as $type) {
            $this->assertTrue(method_exists(Text::class, 'build'.$type), 'Text::build'.$type);
        }
    }
}
