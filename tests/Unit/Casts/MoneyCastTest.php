<?php

namespace Tests\Unit\Casts;

use App\Casts\money;
use App\Models\Payment;
use PHPUnit\Framework\TestCase;

/**
 * money 的换算契约：库里以分存、表单以元填，写入必须返回 int。
 */
class MoneyCastTest extends TestCase
{
    private money $cast;

    private Payment $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cast = new money;
        $this->model = new Payment;
    }

    /**
     * @dataProvider providerSet
     */
    public function test_set_stores_yuan_as_fen(mixed $value, int $expected, string $case): void
    {
        $stored = $this->cast->set($this->model, 'amount', $value, []);

        // 返回类型必须是 int：amount 列是 int unsigned，写回 float 会在 PDO 层炸
        $this->assertIsInt($stored, $case);
        $this->assertSame($expected, $stored, $case);
    }

    public static function providerSet(): array
    {
        return [
            [9.9, 990, '常见的表单值'],
            ['9.9', 990, '表单传来的是字符串'],
            [0, 0, ''],
            [0.0, 0, ''],
            [1, 100, ''],
            [12.34, 1234, ''],
            [99999.99, 9999999, ''],
            // round() 是「远离零」进位，不是 PHP 默认的银行家舍入
            [0.005, 1, '半分钱要进位而不是丢掉'],
            [0.015, 2, ''],
            [0.025, 3, ''],
            [0.125, 13, ''],
            [9.995, 1000, ''],
            [99999.995, 10000000, '进位后跨过整数元'],
            // x.xx50 这一族在 (int) round($v * 100) 下会系统性偏小（0.145 会存成 14 分），半分进位不能靠二进制表示的运气
            [0.145, 15, ''],
            [1.005, 101, ''],
            [0.29, 29, '先舍到两位小数再乘 100 仍会掉精度，外面还要再 round 一次'],
            [-9.995, -1000, '负值同样远离零进位'],
        ];
    }

    /** 计算值造出的半分：19.99 元的订单按 0.5 提佣就是 9.995，汇率换算与除法也是这个形状 */
    public function test_set_rounds_a_computed_half_cent_up(): void
    {
        $this->assertSame(1000, $this->cast->set($this->model, 'amount', 19.99 * 0.5, []));
        $this->assertSame(30, $this->cast->set($this->model, 'amount', 0.1 + 0.2, []));
    }

    /**
     * @dataProvider providerGet
     */
    public function test_get_reads_fen_back_as_yuan(mixed $value, float $expected, string $case): void
    {
        $this->assertSame($expected, $this->cast->get($this->model, 'amount', $value, []), $case);
    }

    public static function providerGet(): array
    {
        return [
            [990, 9.9, ''],
            ['990', 9.9, 'PDO 取出的整数列常常是字符串'],
            [0, 0.0, ''],
            [1, 0.01, '最小的非零分'],
            [100, 1.0, ''],
            [9999999, 99999.99, ''],
            [1050, 10.5, ''],
            // 库里存 NULL 时读回 0.0（而不是 null）：渲染价格的模板据此不会拿到 null
            [null, 0.0, 'NULL 列被当成 0 元'],
        ];
    }

    /** 往返一致性：以元录入的值写入再读回，不能漂分 */
    public function test_round_trip_keeps_the_amount(): void
    {
        foreach ([0, 0.01, 0.1, 1, 9.9, 12.34, 88888.88] as $yuan) {
            $stored = $this->cast->set($this->model, 'amount', $yuan, []);

            $this->assertSame(
                round($yuan, 2),
                $this->cast->get($this->model, 'amount', $stored, []),
                $yuan.' 元经过存取之后不该变化'
            );
        }
    }

    /** 读→写 的往返对已经存在的整数分必须幂等，否则「打开后台再保存一次」就会改价 */
    public function test_write_is_idempotent_for_stored_fen(): void
    {
        foreach ([0, 1, 990, 1050, 9999999] as $fen) {
            $yuan = $this->cast->get($this->model, 'amount', $fen, []);

            $this->assertSame($fen, $this->cast->set($this->model, 'amount', $yuan, []));
        }
    }

    /** 亚分位在读取时被抹平：库里理论上不该出现非整数分，出现了也只能显示到分 */
    public function test_get_never_returns_sub_cent_precision(): void
    {
        $this->assertSame(0.01, $this->cast->get($this->model, 'amount', 1.4, []));
        $this->assertSame(1.0, $this->cast->get($this->model, 'amount', 99.5, []));
        $this->assertSame(1.01, $this->cast->get($this->model, 'amount', 100.5, []));
    }

    /**
     * 现状记录：cast 本身不做负数防护，而 amount/credit 都是 unsigned 列，
     * 负值只能靠调用方（退款/扣减）自己保证；这里锁住「乘 100 保持符号」而不是偷偷归零。
     */
    public function test_set_keeps_the_sign(): void
    {
        $this->assertSame(-990, $this->cast->set($this->model, 'amount', -9.9, []));
        $this->assertSame(-9.9, $this->cast->get($this->model, 'amount', -990, []));
    }
}
