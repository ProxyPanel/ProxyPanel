<?php

namespace Tests\Unit\View;

use FilesystemIterator;
use Illuminate\View\Compilers\BladeCompiler;
use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Blade 只把模板编译成 PHP，语法坏要等页面被请求才炸；这里把编译产物全部解析一遍，
 * 让写坏的指令在 php artisan test 阶段就失败。
 */
class BladeViewSyntaxTest extends TestCase
{
    private function parser(): Parser
    {
        return (new ParserFactory)->createForHostVersion();
    }

    /** 视图里的组件标签要经容器解析，独立 new BladeCompiler 会在 x-admin.* 上抛 BindingResolutionException */
    private function compiler(): BladeCompiler
    {
        return $this->app->make('blade.compiler');
    }

    /** @return array<int, string> 相对 resources/views 的视图路径 */
    private function views(): array
    {
        $root = base_path('resources/views');
        $views = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $views[] = str_replace($root.\DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
        sort($views);

        return $views;
    }

    /** 解析不通过时返回错误信息，通过返回 null */
    private function syntaxError(string $compiled): ?string
    {
        try {
            $this->parser()->parse($compiled);
        } catch (Error $e) {
            return $e->getMessage();
        }

        return null;
    }

    public function test_it_scans_the_views_instead_of_checking_nothing(): void
    {
        // 遍历判据一坏，下面的用例就会因为「零个文件」假绿，这里钉一个下限
        $this->assertGreaterThan(100, count($this->views()), 'resources/views 下应检出上百个 Blade 视图');
    }

    public function test_every_view_compiles_to_parseable_php(): void
    {
        $compiler = $this->compiler();
        $root = base_path('resources/views');
        $broken = [];

        foreach ($this->views() as $view) {
            $error = $this->syntaxError($compiler->compileString(file_get_contents($root.\DIRECTORY_SEPARATOR.$view)));
            if ($error !== null) {
                $broken[] = $view.' -> '.$error;
            }
        }

        $this->assertSame([], $broken, "编译产物语法不通过：\n".implode("\n", $broken));
    }

    public function test_the_gate_rejects_a_multi_line_json_argument(): void
    {
        // Laravel 10 的 @json 用 explode(',') 切参数且只取前三段，多行数组末尾的 ] 会掉在第 4 段上被丢
        $compiled = $this->compiler()->compileString(<<<'BLADE'
const data = @json([
    'a' => 1,
    'b' => 2,
    'c' => 3,
]);
BLADE);

        $this->assertNotNull(
            $this->syntaxError($compiled),
            '多行 @json 本该编译成坏语法，否则这套闸门是在空跑'
        );
    }

    public function test_the_gate_accepts_a_single_variable_json_argument(): void
    {
        $compiled = $this->compiler()->compileString("<?php \$lists = [1, 2]; ?>\nconst data = @json(\$lists);\n");

        $this->assertNull($this->syntaxError($compiled), '单变量 @json 是合法写法，不该被误判');
        $this->assertStringContainsString('json_encode($lists', $compiled);
    }
}
