<?php

namespace Tests\Unit\Casts;

use App\Casts\data_rate;
use App\Models\Node;
use PHPUnit\Framework\TestCase;

/**
 * data_rate 的换算契约：表单 Mbps 与库里 Byte/s 互换，单位来源是 Mbps 常量。
 */
class DataRateCastTest extends TestCase
{
    private data_rate $cast;

    private Node $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cast = new data_rate;
        $this->model = new Node;
    }

    /** Mbps 常量本身是这个 cast 的唯一单位来源，先钉住它 */
    public function test_mbps_constant_is_bytes_per_second_of_one_megabit(): void
    {
        $this->assertSame(125000, Mbps);
        $this->assertEquals(1000000 / 8, Mbps, '1 Mbps = 1,000,000 bit/s ÷ 8 = 125,000 Byte/s');
    }

    /**
     * @dataProvider providerSet
     */
    public function test_set_stores_megabit_as_bytes_per_second(mixed $value, int $expected, string $case): void
    {
        $stored = $this->cast->set($this->model, 'speed_limit', $value, []);

        // bigint unsigned 列，返回 float 会在写入时出问题
        $this->assertIsInt($stored, $case);
        $this->assertSame($expected, $stored, $case);
    }

    public static function providerSet(): array
    {
        return [
            [1, 125000, '1 Mbps'],
            ['1', 125000, '表单传来的是字符串'],
            [0, 0, '0 在列注释里就是不限速'],
            [2.5, 312500, ''],
            [0.5, 62500, ''],
            [0.01, 1250, '两位小数的下界'],
            [125, 15625000, ''],
            [0.005, 625, '不足两位小数的录入照旧乘算'],
            [-1, -125000, 'cast 不做负数防护，列是 unsigned，靠调用方保证'],
        ];
    }

    /**
     * @dataProvider providerGet
     */
    public function test_get_reads_bytes_back_as_megabit(mixed $value, float $expected, string $case): void
    {
        $this->assertSame($expected, $this->cast->get($this->model, 'speed_limit', $value, []), $case);
    }

    public static function providerGet(): array
    {
        return [
            [125000, 1.0, ''],
            ['125000', 1.0, 'PDO 取出的整数列常常是字符串'],
            [0, 0.0, ''],
            [62500, 0.5, ''],
            [1250, 0.01, ''],
            [1, 0.0, '1 Byte/s 不到 0.005 Mbps，两位小数读回 0：而 0 在列注释里是「不限速」'],
            [624, 0.0, '同上，这是本 cast 已知的语义缺口'],
            [625, 0.01, '0.005 Mbps 向上进位'],
            [15625000, 125.0, ''],
            [null, 0.0, 'NULL 列被当成 0 Mbps'],
        ];
    }

    /** 往返一致性：以 0.01 Mbps 为粒度录入的限速不该漂 */
    public function test_round_trip_keeps_the_limit(): void
    {
        foreach ([0, 0.01, 0.5, 1, 2.5, 100, 125] as $mbps) {
            $stored = $this->cast->set($this->model, 'speed_limit', $mbps, []);

            $this->assertSame(
                round($mbps, 2),
                $this->cast->get($this->model, 'speed_limit', $stored, []),
                $mbps.' Mbps 经过存取之后不该变化'
            );
        }
    }

    /** 读→写 的往返对库里已有的 Byte/s 值必须幂等（商品限速会原样搬到用户身上） */
    public function test_write_is_idempotent_for_stored_bytes(): void
    {
        foreach ([0, 1250, 62500, 125000, 15625000] as $bytes) {
            $mbps = $this->cast->get($this->model, 'speed_limit', $bytes, []);

            $this->assertSame($bytes, $this->cast->set($this->model, 'speed_limit', $mbps, []));
        }
    }

    /** 节点 API 用的是 getRawOriginal()（SS/SSR/V2Ray/Trojan 四个控制器都如此），完全绕过 cast：限速下发不经过这里 */
    public function test_raw_value_is_untouched_by_the_cast(): void
    {
        $node = (new Node)->newFromBuilder(['speed_limit' => 125000]);

        $this->assertSame(125000, $node->getRawOriginal('speed_limit'));
        $this->assertSame(1.0, $node->speed_limit);
    }
}
