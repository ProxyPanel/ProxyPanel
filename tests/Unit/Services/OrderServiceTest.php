<?php

namespace Tests\Unit\Services;

use App\Models\Goods;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * 与 OnlineReportTest 同理，继承裸 PHPUnit 的 TestCase：phpunit.xml 的 bootstrap 只有
 * vendor/autoload.php，而启动 Laravel 容器时 SettingServiceProvider 会读取 config 表，
 * 导致每个测试都需要一个可达的数据库。这里只验证不依赖容器的纯集合运算。
 *
 * 覆盖「续费/激活时账号到期日怎么推算」这条规则：它同时影响账号有效期和流量重置日，
 * 算错一次就会让用户少用或多用一整个订阅周期，所以把它从数据库查询里抽出来锁住。
 */
class OrderServiceTest extends TestCase
{
    /**
     * 钉住的「今天」.
     *
     * 到期日推算里有一部分期望值本身就和「今天」有关（没有生效订单时从今天起算）。
     * 这些期望值必须在数据提供器里算好，而提供器先于用例执行——一旦整套测试跨过午夜，
     * 提供器与用例拿到的就不是同一天，用例会无缘无故变红（曾经真的发生过）。
     * 所以这里把日期写成常量，提供器和用例都从它推导，不再读墙上时钟。
     */
    private const FROZEN_TODAY = '2026-09-15';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @dataProvider providerCalculateExpiredAt
     *
     * @param  array<int, array{status: int, days: int, id?: int, expired_at?: string|null}>  $orders
     */
    public function test_calculate_expired_at(array $orders, string $expected): void
    {
        Carbon::setTestNow(self::FROZEN_TODAY);

        $collection = new Collection;

        // 用 newFromBuilder 而不是 new Order([...])：前者与 Eloquent 从数据库水合行的方式一致，
        // 不依赖容器/连接（new 会走 setAttribute，而 casts 要解析连接，脱离 Laravel 直接报错）。
        // 集合下标从 0 开始、数据库主键从 1 开始，两者并不对应。
        foreach ($orders as $index => $row) {
            $order = (new Order)->newFromBuilder(['id' => $row['id'] ?? $index + 100, 'status' => $row['status'], 'expired_at' => $row['expired_at'] ?? null]);
            $order->setRelation('goods', (new Goods)->newFromBuilder(['days' => $row['days']]));

            $collection->push($order);
        }

        $this->assertSame($expected, OrderService::calculateExpiredAt($collection));
    }

    public static function providerCalculateExpiredAt(): array
    {
        return [
            // 当前生效订单的 expired_at 已经含了它自己的天数，再累加就重复计算了
            '生效订单自身的天数不被重复计入' => [
                [
                    ['status' => 2, 'days' => 30, 'expired_at' => '2026-05-01'],
                    ['status' => 3, 'days' => 10],
                ],
                '2026-05-11',
            ],
            // Eloquent\Collection::except() 是按主键排除的（不是数组下标），历史实现传 $current->id 是对的。
            // 保留这个用例，是为了防止以后有人误以为它按下标排除而改错。
            '生效订单的主键与集合下标重合时结果不变' => [
                [
                    ['id' => 1, 'status' => 2, 'days' => 30, 'expired_at' => '2026-05-01'],
                    ['id' => 7, 'status' => 3, 'days' => 10],
                ],
                '2026-05-11',
            ],
            '生效订单排在集合末尾也能正确推算' => [
                [
                    ['status' => 3, 'days' => 10],
                    ['status' => 2, 'days' => 30, 'expired_at' => '2026-05-01'],
                ],
                '2026-05-11',
            ],
            // 还没有生效订单时，所有预支付订单都要从今天开始累加，一条都不能少
            '没有生效订单时从今天起算所有预支付订单' => [
                [
                    ['status' => 3, 'days' => 5],
                    ['status' => 3, 'days' => 7],
                ],
                Carbon::parse(self::FROZEN_TODAY)->addDays(12)->toDateString(),
            ],
            // 后台手工把订单置为生效时不会写 expired_at（LogsController::changeOrderStatus），
            // 这种订单只能从今天起算，且自身天数不在集合里重复累加
            '生效订单缺失到期时间时从今天起算' => [
                [
                    ['status' => 2, 'days' => 30, 'expired_at' => null],
                    ['status' => 3, 'days' => 2],
                ],
                Carbon::parse(self::FROZEN_TODAY)->addDays(2)->toDateString(),
            ],
            '没有任何订单时保持今天' => [[], self::FROZEN_TODAY],
        ];
    }

    /**
     * @dataProvider providerCalculateResetTime
     */
    public function test_calculate_reset_time(?int $period, ?string $expired_at, ?string $expected, string $case): void
    {
        Carbon::setTestNow('2026-05-01');

        $this->assertSame($expected, OrderService::calculateResetTime($expired_at, $period), $case);
    }

    public static function providerCalculateResetTime(): array
    {
        return [
            '重置日早于到期日时生效' => [30, '2026-06-11', '2026-05-31', '今天 2026-05-01 + 30 天'],
            '重置日恰好等于到期日时不生效' => [30, '2026-05-31', null, '重置与到期同日时用户等不到重置，TaskDaily 只处理 expired_at > 今天的账号'],
            '重置日晚于到期日时不生效' => [30, '2026-05-30', null, '套餐比周期短，没有重置一说'],
            '单天周期的重置日' => [1, '2026-06-01', '2026-05-02', ''],
            '到期日就在明天且周期为 1 天' => [1, '2026-05-02', null, ''],
            // 后台商品表单对套餐允许 period 留空（required_unless:type,2 + min:0）。
            // 曾经这里被当成「0 天」算出今天，而判定是 reset_time <= 今天，于是这些用户每天都被清零一次流量。
            '周期留空表示不自动重置' => [null, '2026-12-31', null, '不能因为留空就写入今天的日期'],
            '周期为 0 表示不自动重置' => [0, '2026-12-31', null, 'min:0 允许填 0'],
            '到期日缺失时不设置重置日' => [30, null, null, ''],
        ];
    }

    /** resetTimeAndData() 是激活/续费与每日重置共用的入口，返回的字段顺序与内容都要稳定 */
    public function test_reset_time_and_data_returns_full_payload(): void
    {
        Carbon::setTestNow('2026-05-01');

        $data = $this->service(period: 30, traffic: 100)->resetTimeAndData('2026-06-11');

        $this->assertSame(
            ['u' => 0, 'd' => 0, 'transfer_enable' => 100 * MiB, 'expired_at' => '2026-06-11', 'reset_time' => '2026-05-31'],
            $data
        );
    }

    public function test_reset_time_and_data_skips_reset_date_without_period(): void
    {
        Carbon::setTestNow('2026-05-01');

        $data = $this->service(period: null, traffic: 100)->resetTimeAndData('2026-06-11');

        $this->assertNull($data['reset_time'], '周期为空的套餐不能拿到重置日，否则 TaskDaily 会每天清零它的流量');
        $this->assertSame(100 * MiB, $data['transfer_enable']);
    }

    /** 构造一个不碰数据库的 OrderService：三个关系都在模型里预置好，避免访问关系时去取连接 */
    private function service(?int $period, int $traffic): OrderService
    {
        $order = (new Order)->newFromBuilder(['id' => 1, 'status' => 2]);
        $order->setRelation('user', (new User)->newFromBuilder(['id' => 9]));
        $order->setRelation('goods', (new Goods)->newFromBuilder(['id' => 1, 'type' => 2, 'days' => 30, 'traffic' => $traffic, 'period' => $period]));
        $order->setRelation('payment', null);

        return new OrderService($order);
    }
}
