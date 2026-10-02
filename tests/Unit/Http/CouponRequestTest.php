<?php

namespace Tests\Unit\Http;

use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * 券码查重：核销按 sn 取 first()，两条有效行同码时命中的是哪条只由 priority 决定。
 */
class CouponRequestTest extends TestCase
{
    use DatabaseTransactions;

    private string $sn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sn = 'SN'.strtoupper(substr(uniqid(), -6));
    }

    public function test_duplicate_live_code_is_rejected_for_every_type(): void
    {
        $this->coupon($this->sn);

        foreach ([1, 2, 3] as $type) {
            $this->assertNotEmpty($this->snErrors($this->sn, $type), "type={$type} 的重复券码必须被拦下");
        }
    }

    public function test_code_of_a_deleted_coupon_can_be_reused(): void
    {
        $this->coupon($this->sn)->delete();

        $this->assertEmpty($this->snErrors($this->sn, 1));
    }

    public function test_blank_code_is_left_to_the_generator(): void
    {
        $this->coupon($this->sn);

        $this->assertEmpty($this->snErrors('', 1));
    }

    private function snErrors(string $sn, int $type): array
    {
        $payload = [
            'name' => '测试券',
            'sn' => $sn,
            'type' => $type,
            'value' => 5,
            'num' => 1,
            'start_time' => '2026-01-01',
            'end_time' => '2026-12-31',
        ];

        return Validator::make($payload, (new CouponRequest)->rules())->errors()->get('sn');
    }

    private function coupon(string $sn): Coupon
    {
        return Coupon::create([
            'sn' => $sn,
            'name' => '已存在的券',
            'type' => 1,
            'value' => 100,
            'usable_times' => 1,
            'status' => 0,
        ]);
    }
}
