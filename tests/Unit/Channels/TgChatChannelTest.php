<?php

namespace Tests\Unit\Channels;

use App\Channels\TgChatChannel;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Requests\Admin\SystemRequest;
use App\Models\Config;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\Custom;
use DB;
use Http;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

/**
 * TgChat 渠道的行为验证.
 *
 * 该渠道从第三方中继改成了 Telegram 官方 Bot API，这里用 Laravel 的通知层真实驱动它，
 * 并把发出的 HTTP 请求拦下来断言（和 Stripe 那个 wire 测试同一套办法）——因为这个项目
 * 已经出现过「测试 fixture 照着 bug 写、于是永远绿」的先例。
 */
class TgChatChannelTest extends TestCase
{
    use DatabaseTransactions;

    private const BOT_TOKEN = '123456:AAHfake-token';

    private const CHAT_ID = '-1001234567890';

    /**
     * 跑的是 .env 里那个已迁移的真实库：表只在缺失时建，用例的写入靠事务回滚。
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('notification_log')) {
            Schema::create('notification_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('msg_id')->nullable();
                $table->unsignedTinyInteger('type')->default(1);
                $table->string('address')->default('admin');
                $table->string('title');
                $table->text('content');
                $table->tinyInteger('status')->default(0);
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('config')) {
            Schema::create('config', function (Blueprint $table) {
                $table->string('name')->primary();
                $table->text('value')->nullable();
            });
        }

        config([
            'settings.telegram_token' => self::BOT_TOKEN,
            'settings.tg_chat_token' => self::CHAT_ID,
        ]);
    }

    public function test_成功路径_请求发往官方_bot_api_并记录成功日志(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 42]], 200)]);

        $this->sendNotification('到期提醒', '你的账号将在 3 天后过期');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.telegram.org/bot'.self::BOT_TOKEN.'/sendMessage'
                && ! str_contains($request->url(), '?') // 令牌与 chat_id 不该出现在 query 里
                && $request['chat_id'] === self::CHAT_ID
                && str_contains($request['text'], '到期提醒')
                && str_contains($request['text'], '你的账号将在 3 天后过期');
        });
        Http::assertSentCount(1);

        $log = NotificationLog::sole();
        $this->assertSame(6, $log->type); // 6 = tg_chat，见 config/common.php notification.labels
        $this->assertSame(1, $log->status);
        $this->assertSame('到期提醒', $log->title);
    }

    public function test_ok_false_的失败会写成失败日志而不是被吞掉(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response([
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: chat not found',
        ], 400)]);

        $this->sendNotification('到期提醒', '内容');

        $log = NotificationLog::sole();
        $this->assertSame(-1, $log->status);
        $this->assertStringContainsString('chat not found', (string) $log->error);
    }

    public function test_http_200_但_body_里_ok_为_false_也算失败(): void
    {
        // Telegram 的失败不一定带非 2xx 状态码，只看 $response->ok() 会把它当成功记成 status=1
        Http::fake(['api.telegram.org/*' => Http::response([
            'ok' => false,
            'error_code' => 403,
            'description' => 'Forbidden: bot was blocked by the user',
        ], 200)]);

        $this->sendNotification('到期提醒', '内容');

        $log = NotificationLog::sole();
        $this->assertSame(-1, $log->status);
        $this->assertStringContainsString('bot was blocked by the user', (string) $log->error);
    }

    public function test_限流响应被记录为失败且不会重试风暴(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response([
            'ok' => false,
            'error_code' => 429,
            'description' => 'Too Many Requests: retry after 17',
        ], 429)]);

        $this->sendNotification('到期提醒', '内容');

        $log = NotificationLog::sole();
        $this->assertSame(-1, $log->status);
        $this->assertStringContainsString('retry after 17', (string) $log->error);
        Http::assertSentCount(1);
    }

    public function test_传输异常不会冒泡_且留下_critical_日志(): void
    {
        Http::fake(static function (): void {
            throw new ConnectionException('cURL error 28: Connection timed out');
        });
        Log::spy();

        $result = $this->callChannelDirectly('到期提醒', '内容');

        $this->assertFalse($result); // 通知失败不能把调用方（队列任务）炸掉
        Log::shouldHaveReceived('critical')->once();
        $this->assertSame(0, NotificationLog::count()); // 本次没有成功记录
    }

    public function test_缺少任一配置就不发请求(): void
    {
        Http::fake();

        config(['settings.tg_chat_token' => null]);
        $this->assertFalse($this->callChannelDirectly('标题', '内容'), '缺少 chat_id 时应返回 false');

        config(['settings.telegram_token' => null, 'settings.tg_chat_token' => self::CHAT_ID]);
        $this->assertFalse($this->callChannelDirectly('标题', '内容'), '缺少 bot token 时应返回 false');

        Http::assertNothingSent();
        $this->assertSame(0, NotificationLog::count());
    }

    public function test_渠道启用门槛要求_bot_token_与_chat_id_同时存在(): void
    {
        $channels = fn (): array => (new ReflectionMethod(SystemController::class, 'getNotifyChannels'))->invoke(new SystemController);

        $this->assertNotContains('tgChat', $channels(), '什么都没配时不应启用');

        DB::table('config')->updateOrInsert(['name' => 'tg_chat_token'], ['value' => self::CHAT_ID]);
        $this->assertNotContains('tgChat', $channels(), '只有 chat_id、没有 bot token 时不应启用');

        DB::table('config')->updateOrInsert(['name' => 'telegram_token'], ['value' => self::BOT_TOKEN]);
        $this->assertContains('tgChat', $channels(), '两项都配好后才应启用');

        $this->assertContains('database', $channels()); // 兜底渠道始终在列
        $this->assertContains('mail', $channels());
    }

    public function test_只有整数_chat_id_才合法_旧版中继_token_会被判非法(): void
    {
        $this->assertTrue(TgChatChannel::isValidChatId('123456789'));
        $this->assertTrue(TgChatChannel::isValidChatId('-1001234567890'), '群/超级群为负值');
        $this->assertTrue(TgChatChannel::isValidChatId(' -100123 '), '容忍两端空白');

        // 旧版本这个配置项存的是第三方中继的 token（32 位十六进制），中继停服后 Telegram 会回 400
        $this->assertFalse(TgChatChannel::isValidChatId('a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6'));
        $this->assertFalse(TgChatChannel::isValidChatId(''));
        $this->assertFalse(TgChatChannel::isValidChatId(null));
        $this->assertFalse(TgChatChannel::isValidChatId('abc'));
        $this->assertFalse(TgChatChannel::isValidChatId('1.5'));
        $this->assertFalse(TgChatChannel::isValidChatId('-100 123'));
    }

    public function test_格式不对的_chat_id_不发请求也不写成功日志(): void
    {
        Http::fake();
        config(['settings.tg_chat_token' => 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6']);

        $this->assertFalse($this->callChannelDirectly('标题', '内容'));
        Http::assertNothingSent();
        $this->assertSame(0, NotificationLog::count());
    }

    public function test_后台保存时会拦下非整数的_chat_id_并放行整数(): void
    {
        // 保证这一行存在（后台保存走 update）；已 seed 过的库里它已经有了，直接 insert 会撞主键
        DB::table('config')->updateOrInsert(['name' => 'tg_chat_token'], ['value' => null]);
        $controller = new SystemController;

        // 包 withoutEvents：ConfigObserver 会跑 optimize:clear/optimize，把缓存写进 bootstrap/cache
        $rejected = Config::withoutEvents(fn () => $controller->setConfig($this->configRequest('a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6')));
        $this->assertSame('fail', $rejected->getData(true)['status']);
        $this->assertNull(DB::table('config')->where('name', 'tg_chat_token')->value('value'), '非法值不应写库');

        $accepted = Config::withoutEvents(fn () => $controller->setConfig($this->configRequest('-1001234567890')));
        $this->assertSame('success', $accepted->getData(true)['status']);
        $this->assertSame('-1001234567890', DB::table('config')->where('name', 'tg_chat_token')->value('value'));
    }

    private function configRequest(string $value): SystemRequest
    {
        return SystemRequest::create('/admin/system/setConfig', 'POST', ['name' => 'tg_chat_token', 'value' => $value]);
    }

    /** 走 Laravel 通知层：Notification::sendNow → 渠道容器的 send() */
    private function sendNotification(string $title, string $content): void
    {
        Notification::sendNow(new User(['email' => 'admin@example.test']), new Custom($title, $content, [TgChatChannel::class]));
    }

    /** 直接调用渠道，用于断言返回值 */
    private function callChannelDirectly(string $title, string $content): mixed
    {
        $notification = new Custom($title, $content, [TgChatChannel::class]);
        $this->assertInstanceOf(BaseNotification::class, $notification);

        return (new TgChatChannel)->send(new User(['email' => 'admin@example.test']), $notification);
    }
}
