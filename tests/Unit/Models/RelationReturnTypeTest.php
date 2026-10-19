<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * 关系方法的返回类型必须解析得到真实类：写 `: BelongsTo` 却漏 import 时，PHP 会把它按当前命名空间解析成
 * App\Models\BelongsTo，于是任何一次调用（含 with('...') 预加载）都是 TypeError 而不是查出数据。
 */
class RelationReturnTypeTest extends TestCase
{
    public function test_every_typed_model_method_resolves_its_return_type(): void
    {
        $unresolved = [];

        foreach (glob(dirname(__DIR__, 3).'/app/Models/*.php') as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');

            foreach ((new ReflectionClass($class))->getMethods() as $method) {
                $type = $method->getReturnType();

                if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    continue;
                }

                if (! class_exists($type->getName()) && ! interface_exists($type->getName())) {
                    $unresolved[] = $class.'::'.$method->getName().'() → '.$type->getName();
                }
            }
        }

        $this->assertSame([], $unresolved, '这些方法的返回类型解析不到类（多半是缺 use 语句）：'.PHP_EOL.implode(PHP_EOL, $unresolved));
    }

    public function test_the_detector_catches_a_type_resolved_against_the_model_namespace(): void
    {
        // 反向对照：漏 import 的 BelongsTo 在 PHP 里就是这个样子，判据必须认得出
        $this->assertFalse(class_exists('App\Models\BelongsTo'));
        $this->assertTrue(class_exists('Illuminate\Database\Eloquent\Relations\BelongsTo'));
    }
}
