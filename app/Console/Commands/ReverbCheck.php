<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;
use Throwable;

/**
 * Reverb 广播自检。
 *
 * 生产上「保存节点/用户时报 Pusher error: Authentication signature invalid」这类故障，
 * 根因几乎都在「签名的一方」和「验签的一方」配置不一致，而不是业务代码：
 *  - 应用侧用 broadcasting.connections.reverb 的凭据签名（PHP-FPM 读 config 缓存）
 *  - Reverb 进程用自己启动时加载的 reverb.apps.apps.0 验签
 * 改过 .env / 跑过 optimize 之后没重启 reverb，两边就会分叉，于是所有后台操作一起报错。
 *
 * 检查顺序从便宜到贵：驱动 → 凭据一致性 → 路径前缀 → config 缓存 → 服务端 /up → 端到端签名广播。
 * 全程只打印掩码后的凭据，不输出 secret 明文。
 */
class ReverbCheck extends Command
{
    /**
     * laravel/reverb 1.9.0（PR #368）起，服务端验签前会剥掉 reverb.servers.reverb.path；
     * 1.8.x 及更早版本直接用原始请求路径验签。这条差异决定了子路径部署该怎么配，见 checkPaths()。
     */
    private const REVERB_PREFIX_AWARE = '1.9.0';

    protected $signature = 'reverb:check
                            {--connection= : 要检查的广播连接，默认取 broadcasting.default}
                            {--skip-trigger : 只做配置与连通性检查，不发送签名广播}
                            {--timeout=5 : 网络探测超时（秒）}';

    protected $description = 'Reverb 广播自检：凭据一致性、config 缓存、服务连通性与签名广播';

    /** 任意关键检查失败就置为 false，供脚本按退出码判断 */
    private bool $healthy = true;

    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('broadcasting.default');

        $this->line('广播连接：'.$connection.'（broadcasting.default = '.config('broadcasting.default').'）');

        if (! $this->checkDriver($connection)) {
            return self::FAILURE;
        }

        $driver = config("broadcasting.connections.{$connection}.driver");
        $dispatches = ! in_array($driver, ['null', 'log'], true);

        $this->newLine();
        $this->line('一、凭据与路径');
        $this->checkCredentials($connection, $driver === 'reverb');
        $this->checkPaths($connection, $driver === 'reverb');

        $this->newLine();
        $this->line('二、config 缓存');
        $this->checkCache();

        $this->newLine();
        $this->line('三、Reverb 服务端健康检查');
        $this->checkServerHealth((int) $this->option('timeout'));

        $triggered = false;

        if ($dispatches && ! $this->option('skip-trigger')) {
            $this->newLine();
            $this->line('四、端到端签名广播');
            $triggered = true;
            $this->checkTrigger($connection);
        } elseif ($dispatches) {
            $this->newLine();
            $this->warn('已按 --skip-trigger 跳过端到端签名广播。');
        }

        $this->newLine();

        if (! $this->healthy) {
            $this->error('自检发现问题，详见上面的提示。');

            return self::FAILURE;
        }

        if (! $dispatches) {
            $this->info('配置检查通过，但当前驱动不做真实推送，未验证签名广播。');
        } elseif (! $triggered) {
            $this->info('配置检查通过；签名广播未测试（--skip-trigger）。');
        } else {
            $this->info('自检通过：广播链路可用。');
        }

        return self::SUCCESS;
    }

    /**
     * 驱动检查：null / log 时不会真推送，后台操作也不会因为广播失败而 500.
     */
    private function checkDriver(string $connection): bool
    {
        $driver = config("broadcasting.connections.{$connection}.driver");

        if (! $driver) {
            $this->error("找不到广播连接 [{$connection}]，请检查 config/broadcasting.php 或 --connection 参数。");

            return false;
        }

        $this->line('驱动：'.$driver);

        if (in_array($driver, ['null', 'log'], true)) {
            $this->warn('  该驱动不做真实推送：后台保存不会再因广播失败报错，但前端也收不到实时事件。');
            $this->line('  要排查推送问题，请先把 BROADCAST_DRIVER 设为 reverb 并重建配置缓存。');
        } elseif ($driver !== 'reverb') {
            $this->warn("  驱动为 {$driver} 而不是 reverb：本命令按 reverb 的配置结构核对，结论仅供参考。");
        }

        return true;
    }

    /**
     * 凭据一致性：签名侧（应用）与验签侧（Reverb 进程）必须完全相同；
     * 生效值与 .env 不一致（配置缓存过期）同样会造成两边各拿一套。
     * $strict 为 false（当前不是 reverb 驱动）时结论只作参考，不影响退出码。
     */
    private function checkCredentials(string $connection, bool $strict): void
    {
        $signing = config("broadcasting.connections.{$connection}", []);
        $verifying = config('reverb.apps.apps.0', []);

        $rows = [];
        $broken = [];
        $stale = [];
        $duplicated = [];

        foreach (['app_id' => 'REVERB_APP_ID', 'key' => 'REVERB_APP_KEY', 'secret' => 'REVERB_APP_SECRET'] as $field => $envKey) {
            $a = (string) ($signing[$field] ?? '');
            $b = (string) ($verifying[$field] ?? '');
            $occurrences = $this->envValues($envKey);
            $env = $occurrences === [] ? null : end($occurrences);
            $envDiffers = $env !== null && ! str_contains($env, '${') && $this->unquote($env) !== $a;

            if (count($occurrences) > 1 && count(array_unique(array_map([$this, 'unquote'], $occurrences))) > 1) {
                $duplicated[] = $envKey;
            }

            if ($env === null) {
                $envCell = '未定义';
            } elseif (str_contains($env, '${')) {
                $envCell = '含变量引用，跳过比对';
            } else {
                $envCell = $envDiffers ? '不同（缓存可能过期）' : '一致';
            }

            $rows[] = [
                $field,
                $a === '' ? '（空）' : $this->mask($a),
                $a === '' || $b === '' ? '缺失' : ($a === $b ? '一致' : '不一致'),
                $envCell,
            ];

            if ($a === '' || $b === '' || $a !== $b) {
                $broken[] = $field;
            }

            if ($envDiffers) {
                $stale[] = $field;
            }
        }

        $this->table(['项', '当前值（签名侧）', '签名/验签', '.env 与配置'], $rows);

        foreach ($broken as $field) {
            $message = "{$field} 在「应用签名」与「Reverb 验签」之间缺失或不一致。";

            if ($strict) {
                $this->error('  关键问题：'.$message);
            } else {
                $this->warn('  '.$message.'（当前驱动不是 reverb，仅作参考）');
            }
        }

        if (in_array('secret', $broken, true)) {
            $this->line('  → secret 不一致正是「Pusher error: Authentication signature invalid」的直接原因：');
            $this->line('    改完 .env 要执行 php artisan optimize，再重启 Reverb：supervisorctl restart reverb（或 php artisan reverb:restart）。');
        }

        if ($stale) {
            // 缓存里的凭据与 .env 不同：Web 端（读缓存）与 Reverb 进程（读 .env）很可能各拿一套，
            // 这正是生产上最常见的「验签失败但配置看起来都对」的形态
            $message = implode('、', $stale).' 的生效值与 .env 不同：Web 端读的是 config 缓存，Reverb 进程读的是 .env，两边很容易各拿一套。';

            if ($strict) {
                $this->error('  关键问题：'.$message);
                $this->line('  → 执行 php artisan optimize 重建缓存，然后 supervisorctl restart reverb，让两边收敛到同一套凭据。');
            } else {
                $this->warn('  '.$message);
            }
        }

        foreach ($duplicated as $envKey) {
            $this->error("  关键问题：{$envKey} 在 .env 里定义了多次且取值不同，phpdotenv 以最后一处为准，很容易改了不生效。");
        }

        if (($broken || $stale || $duplicated) && $strict) {
            $this->healthy = false;
        }
    }

    /**
     * 子路径部署的前缀关系。关键点：前缀既要出现在请求 URL 里，又决定了要不要进 HMAC 签名，
     * 而「进不进签名」取决于 Reverb 版本（见 REVERB_PREFIX_AWARE）：
     *  - ≥1.9.0：服务端验签前剥掉 REVERB_SERVER_PATH，签名的路径必须是 /apps/{app_id}/events（不带前缀）
     *            → 前缀只能放在「只做 base_uri」的 options.path（REVERB_PATH）；
     *  - ≤1.8.x：服务端按原始请求路径验签 → 前缀必须进签名，只能用 options.base_path。
     * 两种配法的请求 URL 完全一样，只有签名不同，所以版本一变就会以 401 的形式炸掉。
     */
    private function checkPaths(string $connection, bool $strict): void
    {
        $options = config("broadcasting.connections.{$connection}.options", []);
        // 与 config/broadcasting.php、config/reverb.php 用同一套归一化（app/helpers.php），
        // 避免检查器和运行时各算一套前缀
        $urlPrefix = reverb_prefix($options['path'] ?? null);
        $signedPrefix = reverb_prefix(preg_replace('#/apps/[^/]+$#', '', (string) ($options['base_path'] ?? '')));
        $serverPrefix = reverb_prefix(config('reverb.servers.reverb.path'));

        $version = $this->reverbVersion();
        $prefixAware = $version === null ? null : version_compare($version, self::REVERB_PREFIX_AWARE, '>=');

        if ($version === null) {
            $this->warn('  读不到 laravel/reverb 版本（composer.lock 与 vendor/composer/installed.json 都没有），按 ≥1.9.0 判断。');
        } else {
            $this->line('  laravel/reverb '.$version.'：'.($prefixAware
                ? '≥ '.self::REVERB_PREFIX_AWARE.'，验签前会剥掉服务端前缀（前缀不能进签名）'
                : '< '.self::REVERB_PREFIX_AWARE.'，按原始请求路径验签（前缀必须进签名）'));
        }

        if ($urlPrefix === '' && $signedPrefix === '' && $serverPrefix === '') {
            $this->line('  路径前缀：都为空（最省心的部署方式：Reverb 挂在独立端口或子域上）。');

            // 即使没有前缀也校验 .env 的写法，顺手把「写了个 / 又删掉」这类残留挑出来
            $this->warnNonCanonicalEnvPaths();

            return;
        }

        $this->line('  路径前缀：请求 URL=「'.$urlPrefix.'」，签名路径=「'.$signedPrefix.'」，服务端=「'.$serverPrefix.'」');
        $failed = false;

        if ($prefixAware !== false && $signedPrefix !== '') {
            $failed = true;
            $this->error('  关键问题：前缀进了签名路径（options.base_path）—— 新版 Reverb 验签前会剥掉服务端前缀，两边必然对不上。');
            $this->line('  → 改成只影响 URL 的 options.path：\'path\' => reverb_client_path(env(\'REVERB_PATH\'))；升级 laravel/reverb 到 ^1.9。');
        }

        if ($prefixAware === false && ($urlPrefix !== '' || $signedPrefix === '')) {
            $failed = true;
            $this->error('  关键问题：laravel/reverb '.$version.' 按原始请求路径验签，前缀必须进签名，只能用 options.base_path。');
            $this->line('  → 把前缀写进 base_path（即 1.9.0 之前的 config/broadcasting.php 写法），或升级 laravel/reverb 到 ^1.9 后改用 options.path。');
        }

        $this->warnNonCanonicalEnvPaths();

        if ($prefixAware !== false && $urlPrefix === '' && $signedPrefix === '' && $serverPrefix !== '') {
            $this->warn('  服务端监听在「'.$serverPrefix.'」下但请求地址没有前缀：除非 nginx 会把前缀剥掉再转发，否则会得到 404。');
        }

        if ($prefixAware !== false && $urlPrefix !== '' && $serverPrefix !== '' && $urlPrefix !== $serverPrefix) {
            $this->warn('  REVERB_PATH 与 REVERB_SERVER_PATH 不一致：只有「nginx 原样转发前缀」时两者相等才成立；');
            $this->line('  若 nginx 会剥掉前缀，则 REVERB_PATH 保留、REVERB_SERVER_PATH 必须为空，否则会得到 404 或 401。');
        }

        if ($failed && $strict) {
            $this->healthy = false;
        }
    }

    /**
     * .env 里手写的 REVERB_PATH / REVERB_SERVER_PATH 是否写成规范形式。
     *
     * 写歪了不会致命（三处消费者都会归一化，见 app/helpers.php），但会让人对不上账，
     * 所以只提醒、不判关键问题。
     */
    private function warnNonCanonicalEnvPaths(): void
    {
        foreach (['REVERB_PATH', 'REVERB_SERVER_PATH'] as $key) {
            $raw = trim((string) env($key));
            $canonical = reverb_prefix($raw);

            if ($raw === '' || $raw === $canonical) {
                continue;
            }

            $hint = $canonical === '' ? '建议留空（表示不使用子路径）' : "建议写为「{$canonical}」";
            $this->warn("  {$key}＝「{$raw}」不是规范形式，{$hint}（前导 /、结尾不带 /）。");
        }
    }

    /**
     * 读已安装的 laravel/reverb 版本（先 composer.lock，再 vendor/composer/installed.json）.
     */
    private function reverbVersion(): ?string
    {
        $lock = base_path('composer.lock');

        if (is_file($lock)) {
            $data = json_decode((string) file_get_contents($lock), true);

            foreach (['packages', 'packages-dev'] as $section) {
                foreach ((array) ($data[$section] ?? []) as $package) {
                    if (($package['name'] ?? null) === 'laravel/reverb') {
                        return ltrim((string) ($package['version'] ?? ''), 'v') ?: null;
                    }
                }
            }
        }

        $installed = base_path('vendor/composer/installed.json');

        if (is_file($installed)) {
            $data = json_decode((string) file_get_contents($installed), true);
            $packages = $data['packages'] ?? $data;

            foreach ((array) $packages as $package) {
                if (($package['name'] ?? null) === 'laravel/reverb') {
                    return ltrim((string) ($package['version'] ?? ''), 'v') ?: null;
                }
            }
        }

        return null;
    }

    /**
     * config 缓存：本面板生产环境大量使用 optimize，缓存与 .env 脱节时 Web 端会用旧凭据签名.
     */
    private function checkCache(): void
    {
        $path = base_path('bootstrap/cache/config.php');

        if (! is_file($path)) {
            $this->line('  config 缓存未生成：每次请求直接读 .env。');
            $this->line('  提醒：reverb:start 进程只在启动时读一次配置，改完 .env 务必重启它。');

            return;
        }

        $cachedAt = filemtime($path);
        $envAt = is_file(base_path('.env')) ? filemtime(base_path('.env')) : null;

        $this->line('  '.$path.'（'.date('Y-m-d H:i:s', $cachedAt).'）');

        if ($envAt !== null && $envAt > $cachedAt) {
            $this->warn('  .env 的修改时间晚于 config 缓存：Web 端可能仍在用旧凭据，请执行 php artisan optimize 后重启 Reverb。');
        }
    }

    /**
     * 直接探测 Reverb 进程本身（走服务端监听地址），用于区分「进程没起」和「验签失败」.
     */
    private function checkServerHealth(int $timeout): void
    {
        $server = config('reverb.servers.reverb', []);
        $host = (string) ($server['host'] ?? '127.0.0.1');

        if (in_array($host, ['', '0.0.0.0', '::', '::0'], true)) {
            $host = '127.0.0.1'; // 监听 0.0.0.0 时本机就是 127.0.0.1
        }

        $port = (int) ($server['port'] ?? 8080);
        $prefix = trim((string) ($server['path'] ?? ''), '/');
        $url = 'http://'.$host.':'.$port.($prefix === '' ? '' : '/'.$prefix).'/up';

        try {
            $response = (new Client(['timeout' => $timeout, 'http_errors' => false]))->get($url);
            $status = $response->getStatusCode();

            $this->line('  '.$url.' → HTTP '.$status);

            if ($status === 200) {
                return;
            }

            $this->warn('  健康检查没有返回 200：确认服务端 REVERB_SERVER_PATH 与实际访问路径是否一致。');
        } catch (Throwable $e) {
            $this->error('  无法连接 '.$url.'：'.$e->getMessage());
            $this->line('  提示：supervisorctl status reverb / supervisorctl restart reverb，并核对 REVERB_SERVER_HOST、REVERB_SERVER_PORT。');
        }
    }

    /**
     * 端到端验证：走应用真实的 broadcast()，和 NodeController 保存节点时完全同一条代码路径.
     */
    private function checkTrigger(string $connection): void
    {
        $channel = 'broadcast.check.'.Str::random(8);

        try {
            Broadcast::connection($connection)->broadcast([$channel], 'connectivity.check', ['time' => now()->timestamp]);
        } catch (BroadcastException $e) {
            $this->reportTriggerFailure($e->getMessage());

            return;
        } catch (Throwable $e) {
            $this->reportTriggerFailure($e->getMessage());

            return;
        }

        $this->info('  签名广播成功（频道 '.$channel.'）：Reverb 已接受这次请求，凭据、路径与网络都是通的。');
    }

    private function reportTriggerFailure(string $rawMessage): void
    {
        // SDK 的报错里会带完整的签名 URL，去掉查询串并限长，避免把签名写进日志/工单
        $message = Str::limit((string) preg_replace('/\?[^\s]*/', '?…', $rawMessage), 300);

        $this->error('  签名广播失败：'.$message);
        $this->healthy = false;

        $lower = strtolower($message);

        if (str_contains($lower, 'signature invalid')) {
            $this->line('  → 两种常见原因：①应用签名用的 secret 与 Reverb 进程验签用的不同（先 php artisan optimize，再 supervisorctl restart reverb）；');
            $this->line('    ②子路径前缀进/没进签名，与 laravel/reverb 版本不匹配（见上面「凭据与路径」里的版本说明）。');
        } elseif (str_contains($lower, 'no matching application')) {
            $this->line('  → REVERB_APP_ID 与 Reverb 进程加载的不一致，检查 .env 里是否有重复的 REVERB_APP_ID 行。');
        } elseif (str_contains($lower, 'null given') || str_contains($lower, 'must be of type string')) {
            $this->line('  → REVERB_APP_ID / REVERB_APP_KEY / REVERB_APP_SECRET 有空值，先在 .env 补全再重建缓存。');
        } elseif (str_contains($lower, 'curl error') || str_contains($lower, 'connection refused')
            || str_contains($lower, 'could not resolve') || str_contains($lower, 'timed out')) {
            $this->line('  → 连不上 Reverb：确认服务在运行，并核对 REVERB_HOST / REVERB_PORT / REVERB_SCHEME 是否指向它。');
        } else {
            $this->line('  → 请结合上面的健康检查与 Reverb 日志（storage/logs/reverb.log）继续定位。');
        }
    }

    /**
     * 直接读 .env，列出某个键的全部取值。
     *
     * 实测（本项目的 phpdotenv + Laravel 的 immutable/putenv 仓库）重复定义时**最后一处生效**，
     * 所以这里返回的是原始行序，调用方取 end() 才是真正生效的值。
     *
     * @return array<int, string>
     */
    private function envValues(string $key): array
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            if ($name === $key) {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function unquote(string $value): string
    {
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"'))
            || ($value[0] === "'" && str_ends_with($value, "'")))) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * 只暴露长度与首尾少量字符，避免把 secret 打进日志.
     */
    private function mask(string $value): string
    {
        if (strlen($value) <= 6) {
            return '***（长度 '.strlen($value).'）';
        }

        return substr($value, 0, 2).'***'.substr($value, -2).'（长度 '.strlen($value).'）';
    }
}
