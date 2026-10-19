<?php

namespace Tests\Unit\Http;

use DB;
use RuntimeException;

/**
 * 以 exit() 收尾的网关 notify() 无法在本进程做 HTTP 往返（exit 会带走 phpunit），
 * 由 CLI 子进程代发一次真实请求：建夹具→算签名→派发→在 shutdown 钩子里出 JSON 报告并回滚（commit 模式除外）。
 * Stripe 直接读 php://input，CLI 下恒为空，子进程用换包装器的方式注入原文。
 */
trait PaymentNotifyChildRunner
{
    /** @var list<array{user_id: int, callback_ids: list<int>}> */
    private array $committedChildren = [];

    /**
     * 跑一个子进程往返并解析其报告；commit 模式的产物登记进 $committedChildren 供 tearDown 清理。
     */
    private function runNotifyChild(array $spec): array
    {
        $spec['base_path'] = base_path();
        if (! isset($spec['uniq'])) {
            $spec['uniq'] = strtoupper(substr(md5(uniqid('pnr', true)), 0, 10));
        }

        $specFile = tempnam(sys_get_temp_dir(), 'pnr-spec');
        $scriptFile = tempnam(sys_get_temp_dir(), 'pnr-run').'.php';
        file_put_contents($specFile, (string) json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        file_put_contents($scriptFile, $this->childScript());

        $env = getenv();
        foreach ([
            'APP_ENV' => 'testing',
            'CACHE_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'MAIL_MAILER' => 'array',
            'BROADCAST_DRIVER' => 'null',
            'TELESCOPE_ENABLED' => 'false',
        ] as $name => $value) {
            $env[$name] = $value;
        }

        $process = proc_open(
            [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'error_reporting=24575', $scriptFile, $specFile],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            base_path(),
            $env
        );

        if (! is_resource($process)) {
            throw new RuntimeException('无法启动支付回调子进程');
        }

        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        @unlink($specFile);
        @unlink($scriptFile);

        $marker = "\n===REPORT===\n";
        $position = strrpos($stdout, $marker);
        if ($position === false) {
            throw new RuntimeException("子进程未回报告 (exit={$exitCode})\nstdout: {$stdout}\nstderr: {$stderr}");
        }

        $report = json_decode(substr($stdout, $position + strlen($marker)), true);
        if (! is_array($report)) {
            throw new RuntimeException('子进程报告不是合法 JSON: '.substr($stdout, $position));
        }

        if (! empty($spec['commit']) && isset($report['user_id'])) {
            $this->committedChildren[] = [
                'user_id' => $report['user_id'],
                'callback_ids' => array_column($report['callbacks'], 'id'),
            ];
        }

        return $report;
    }

    /** 清理 commit 模式子进程留在库里的夹具与台账行；走独立连接，父进程事务回滚不会把它们留下来. */
    private function purgeCommittedChildren(): void
    {
        $connection = DB::connection('cleanup');
        foreach ($this->committedChildren as $child) {
            if ($child['callback_ids'] !== []) {
                $connection->table('payment_callback')->whereIn('id', $child['callback_ids'])->delete();
            }
            $connection->table('user_credit_log')->where('user_id', $child['user_id'])->delete();
            $connection->table('payment')->where('user_id', $child['user_id'])->delete();
            $connection->table('order')->where('user_id', $child['user_id'])->delete();
            $connection->table('user')->where('id', $child['user_id'])->delete();
        }
        $this->committedChildren = [];
    }

    /** 既往异常中断留下的已提交夹具兜底清理：子进程用户名固定 notify-rt- 前缀. */
    private function purgeStaleChildFixtures(): void
    {
        $connection = DB::connection('cleanup');
        $userIds = $connection->table('user')->where('username', 'like', 'notify-rt-%')->pluck('id')->all();
        if ($userIds === []) {
            return;
        }

        $tradeNos = $connection->table('payment')->whereIn('user_id', $userIds)->pluck('trade_no')->all();
        if ($tradeNos !== []) {
            $connection->table('payment_callback')->whereIn('trade_no', $tradeNos)->delete();
        }
        $connection->table('user_credit_log')->whereIn('user_id', $userIds)->delete();
        $connection->table('payment')->whereIn('user_id', $userIds)->delete();
        $connection->table('order')->whereIn('user_id', $userIds)->delete();
        $connection->table('user')->whereIn('id', $userIds)->delete();
    }

    private function childScript(): string
    {
        return <<<'CHILD_SCRIPT'
            <?php

            // 支付网关 notify() 往返的 CLI 子进程：每个进程只派发一次请求（网关以 exit() 收尾），
            // 结果在 shutdown 钩子里汇总成 ===REPORT=== 后的 JSON；默认回滚，commit 模式留给父进程清理。

            use App\Models\Order;
            use App\Models\Payment;
            use App\Models\User;
            use App\Notifications\Custom;
            use App\Notifications\PaymentReceived;
            use App\Utils\Library\PaymentHelper;
            use Illuminate\Http\Request;

            /** php://input 的替身：CLI 下它恒为空，Stripe 的原文只能从这里注入；其余 php:// 路径委托回内置包装器. */
            class RawInputStream
            {
                public $context;

                private int $position = 0;

                private string $data = '';

                /** @var resource|null */
                private $fallback = null;

                public function stream_open(string $path, string $mode, int $options, ?string &$openedPath = null): bool
                {
                    if ($path === 'php://input') {
                        $this->data = (string) ($GLOBALS['__RAW_INPUT'] ?? '');
                        $this->position = 0;

                        return true;
                    }

                    stream_wrapper_restore('php');
                    $this->fallback = fopen($path, $mode);
                    stream_wrapper_unregister('php');

                    return $this->fallback !== false;
                }

                public function stream_read(int $count): string
                {
                    if ($this->fallback !== null) {
                        return (string) fread($this->fallback, $count);
                    }

                    $chunk = substr($this->data, $this->position, $count);
                    $this->position += strlen($chunk);

                    return $chunk;
                }

                public function stream_eof(): bool
                {
                    return $this->fallback !== null ? feof($this->fallback) : $this->position >= strlen($this->data);
                }

                public function stream_write(string $data): int
                {
                    return $this->fallback !== null ? (int) fwrite($this->fallback, $data) : 0;
                }

                public function stream_close(): void
                {
                    if ($this->fallback !== null) {
                        fclose($this->fallback);
                    }
                }

                public function stream_stat(): array
                {
                    return [];
                }

                public function stream_set_option(int $option, int $arg1, int $arg2): bool
                {
                    return false;
                }
            }

            $spec = json_decode((string) file_get_contents($argv[1]), true);

            require $spec['base_path'].'/vendor/autoload.php';
            $app = require $spec['base_path'].'/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

            // HTTP Kernel 只在 handle() 里绑 request，而个别 provider 的 boot 就要用它（column-sortable）
            $app->instance('request', Request::create('http://localhost/'));
            Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
            $kernel->bootstrap();

            Notification::fake();
            config(['broadcasting.default' => 'null']);
            foreach ($spec['config'] ?? [] as $name => $value) {
                config(['settings.'.$name => $value]);
            }

            DB::beginTransaction();

            // 未匹配提醒的收件人固定是 id=1 的管理员
            $admin = User::find(1) ?? User::withoutEvents(fn () => User::create([
                'id' => 1,
                'username' => 'admin-'.uniqid().'@example.com',
                'passwd' => bcrypt('secret-123'),
                'port' => 20001,
            ]));

            $fixture = $spec['fixture'] ?? ['mode' => 'create'];
            $amount = $fixture['amount'] ?? 9.9;
            $user = null;
            $order = null;
            $payment = null;

            if (($fixture['mode'] ?? 'create') === 'create') {
                $user = User::withoutEvents(fn () => User::create([
                    'username' => 'notify-rt-'.$spec['uniq'].'@example.com',
                    'passwd' => bcrypt('secret'),
                ]));
                $order = Order::create([
                    'sn' => 'SNRT'.$spec['uniq'],
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'origin_amount' => $amount,
                ]);
                $payment = PaymentHelper::createPayment($user->id, $order->id, $amount);
            } elseif (($fixture['mode'] ?? '') === 'reuse') {
                $payment = Payment::whereTradeNo($fixture['trade_no'])->firstOrFail();
                $user = $payment->user;
                $order = $payment->order;
            }

            $tradeNo = $payment?->trade_no ?? '';
            $placeholder = fn ($value) => str_replace(['{trade_no}', '{uniq}'], [$tradeNo, $spec['uniq']], (string) $value);

            $payload = [];
            foreach ($spec['payload'] ?? [] as $key => $value) {
                $payload[$key] = $placeholder($value);
            }

            $rawSign = null;
            $content = null;
            $server = [];
            $sign = $spec['sign'] ?? null;
            if ($sign !== null && $sign['algo'] === 'ali') {
                $rawSign = PaymentHelper::aliStyleSign($payload, $sign['key'], $sign['filter'] ?? true);
                $payload[$sign['field'] ?? 'sign'] = $rawSign;
            } elseif ($sign !== null && $sign['algo'] === 'stripe') {
                $content = $placeholder($spec['raw_body']);
                $GLOBALS['__RAW_INPUT'] = $content;
                $timestamp = time();
                $digestSecret = ! empty($sign['tampered']) ? 'whsec-not-the-real-secret' : $sign['secret'];
                $rawSign = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$content, $digestSecret);
                $_SERVER['HTTP_STRIPE_SIGNATURE'] = $rawSign;
                $server['HTTP_STRIPE_SIGNATURE'] = $rawSign;
                $server['CONTENT_TYPE'] = 'application/json';
            }

            $callbackMarker = (int) (DB::table('payment_callback')->max('id') ?? 0);

            $done = false;
            $finish = function () use (&$done, $spec, $user, $order, $tradeNo, $callbackMarker, $rawSign) {
                if ($done) {
                    return;
                }
                $done = true;

                // exit() 的文本落在输出缓冲里，这里取出后即丢弃，报告只走标记后的 JSON
                $body = ob_get_level() > 0 ? (string) ob_get_clean() : '';

                $customAlerts = 0;
                $fulfillNotices = 0;
                // sentNotifications() 结构：[notifiable 类][notifiable 键][通知类] => 条目
                foreach (Notification::getFacadeRoot()->sentNotifications() as $perNotifiable) {
                    foreach ($perNotifiable as $perNotificationClass) {
                        foreach ($perNotificationClass as $class => $items) {
                            if ($class === Custom::class) {
                                $customAlerts += count($items);
                            } elseif ($class === PaymentReceived::class) {
                                $fulfillNotices += count($items);
                            }
                        }
                    }
                }

                $callbacks = DB::table('payment_callback')->where('id', '>', $callbackMarker)->orderBy('id')->get()->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'method' => $row->method,
                    'trade_no' => $row->trade_no,
                    'out_trade_no' => $row->out_trade_no,
                    'amount' => (int) $row->amount,
                    'status' => (int) $row->status,
                    'sign_masked' => str_contains($row->payload, "'sign' => '******'"),
                    'sign_leaked' => $rawSign !== null && str_contains($row->payload, $rawSign),
                    'payload_length' => mb_strlen($row->payload),
                ])->all();

                $report = [
                    'body' => $body,
                    'http_status' => http_response_code() ?: 200,
                    'uniq' => $spec['uniq'],
                    'trade_no' => $tradeNo,
                    'user_id' => $user?->id,
                    'order_id' => $order?->id,
                    'order_status' => $order !== null ? (int) Order::find($order->id)->status : null,
                    'credit_raw' => $user !== null ? (int) DB::table('user')->where('id', $user->id)->value('credit') : null,
                    'credit_logs' => $user !== null ? DB::table('user_credit_log')->where('user_id', $user->id)->count() : null,
                    'callbacks' => $callbacks,
                    'custom_alerts' => $customAlerts,
                    'fulfill_notices' => $fulfillNotices,
                ];

                if (! empty($spec['commit'])) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }

                echo "\n===REPORT===\n".json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            };

            register_shutdown_function($finish);

            $uri = route('payment.notify', ['method' => $spec['gateway']]);
            $request = Request::create($uri, 'POST', $content === null ? $payload : [], [], [], $server, $content);

            if ($content !== null) {
                stream_wrapper_unregister('php');
                stream_wrapper_register('php', RawInputStream::class);
            }

            ob_start();
            $kernel->handle($request);

            $finish();
            CHILD_SCRIPT;
    }
}
