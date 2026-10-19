<?php

namespace Tests\Unit\Http;

use App\Models\Config;
use App\Models\User;
use Database\Seeders\ConfigSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionProperty;
use Tests\TestCase;

/**
 * 系统设置页的渲染不允许在命名空间里留下全局函数：
 * `SystemController::index()` 的方法体里写着 `function parseTime()`，函数在执行到那一行时才登记，
 * 于是同一个 PHP 进程里第二次打开这页就是 `Cannot redeclare function App\Http\Controllers\Admin\parseTime()`
 * 致命（FPM 每请求新进程看不出来；Octane、进程内复用与测试都会踩）。
 *
 * 在近乎空的库上渲染本页，PHPUnit 会额外报一条 risky（输出缓冲没闭合），
 * 与本测试要守的判据无关，断言照常通过。
 */
class SystemConfigPageTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureConfigRowsExist();

        $this->admin = $this->superAdmin();
    }

    public function test_rendering_the_settings_page_leaves_no_namespace_global_function(): void
    {
        $this->actingAs($this->admin)->get(route('admin.system.index'))->assertStatus(200);

        $this->assertFalse(
            function_exists('App\Http\Controllers\Admin\parseTime'),
            'index() 在方法体里声明 parseTime()，同一进程第二次打开这页会 Cannot redeclare 致命'
        );
    }

    /**
     * 视图直接按 `$configs['键']` 取值，库里缺行就抛 Undefined array key（与本页要测的判据无关），
     * 所以按 ConfigSeeder 的键表补齐。
     */
    private function ensureConfigRowsExist(): void
    {
        $keys = (new ReflectionProperty(ConfigSeeder::class, 'configs'))->getValue(new ConfigSeeder);

        Config::insertOrIgnore(array_map(
            static fn (string $name): array => ['name' => $name, 'value' => null],
            $keys
        ));
    }
}
