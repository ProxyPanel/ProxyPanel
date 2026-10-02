<?php

namespace Tests\Unit\Notifications;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserOauth;
use App\Notifications\PaymentConfirm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Tests\TestCase;

/**
 * PaymentConfirm 的 Telegram 收件人解析.
 *
 * 覆盖一条真实缺陷：旧实现只在「第一位已绑定的超管」处 return，既忽略通知对象自己的绑定、
 * 顺序也不确定（查询没有 orderBy）；完全没有绑定时代码返回 false，而 TelegramChannel 会直接
 * 在返回值上调用 toNotGiven()，在 PHP 8 下是致命错误，而不是「安静地不发消息」。
 *
 * Order / Payment / User 都只用内存对象 + 手工设置的关联，不依赖数据库。
 */
class PaymentConfirmTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['settings.telegram_token' => '123456:AAHfake-token']);

        // routes/web.php 只在 config('app.key') && config('settings') 都为真时注册支付回调路由，
        // 测试环境这两项为空，这里补一条同名路由，让消息里的按钮 URL 能解析出来
        // （名字必须通过 action 数组在注册时传入，事后再 ->name() 不会进 RouteCollection 的名字索引）
        if (! Route::has('payment.notify')) {
            Route::get('callback/notify', ['as' => 'payment.notify', 'uses' => static fn () => null]);
        }
    }

    public function test_优先发给通知对象自己绑定的会话(): void
    {
        $message = $this->notification('-1009999')->toTelegram($this->user('111'));

        $this->assertInstanceOf(TelegramMessage::class, $message);
        $this->assertSame('111', $message->getPayloadValue('chat_id'));
    }

    public function test_通知对象未绑定时退回第一位已绑定的超管(): void
    {
        $message = $this->notification('-1001234567890')->toTelegram($this->user(null));

        $this->assertSame('-1001234567890', $message->getPayloadValue('chat_id'));
    }

    public function test_无人绑定时返回不带接收者的消息而不是_false(): void
    {
        $message = $this->notification(null)->toTelegram($this->user(null));

        $this->assertNotFalse($message);
        $this->assertInstanceOf(TelegramMessage::class, $message);
        $this->assertTrue($message->toNotGiven()); // 交给渠道回落，避免返回 false 触发致命错误
    }

    public function test_无人绑定时真实渠道不会崩且不发请求(): void
    {
        Http::fake();

        $result = (new TelegramChannel(app('events')))->send($this->user(null), $this->notification(null));

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    /** 通知对象：关联已加载，收件人解析不查库 */
    private function user(?string $chatId): User
    {
        $user = new User(['email' => 'admin@example.test']);
        $user->setRelation('userAuths', $chatId === null
            ? collect()
            : collect([new UserOauth(['type' => 'telegram', 'identifier' => $chatId])]));

        return $user;
    }

    /** 用匿名子类替换「已绑定超管」的查询，让收件人解析可以在无数据库下断言 */
    private function notification(?string $fallbackChatId): PaymentConfirm
    {
        $order = new Order(['amount' => 9.99]);
        $order->setRelation('user', new User(['username' => 'admin']));
        $order->setRelation('payment', new Payment(['id' => 7]));
        $order->setRelation('goods', null); // 置空才能避免触发 goods 查询

        return new class($order, $fallbackChatId) extends PaymentConfirm
        {
            public function __construct(Order $order, private readonly ?string $fallbackChatId)
            {
                parent::__construct($order);
            }

            protected function firstBoundAdminChatId(): ?string
            {
                return $this->fallbackChatId;
            }
        };
    }
}
