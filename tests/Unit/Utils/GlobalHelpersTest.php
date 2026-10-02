<?php

namespace Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;

/**
 * 全局 helper 的换算与清洗判据：formatBytes、base64url、array_clean、string_urlsafe、reverb 路径。
 */
class GlobalHelpersTest extends TestCase
{
    /**
     * @dataProvider providerFormatBytes
     */
    public function test_format_bytes_picks_binary_unit(int $bytes, string $expected, string $case): void
    {
        $this->assertSame($expected, formatBytes($bytes), $case);
    }

    public static function providerFormatBytes(): array
    {
        return [
            '零字节' => [0, '0 B', ''],
            // 内部 max($bytes, 0)：负增量（回滚、手工改小）不该渲染成 -100 B
            '负数归零' => [-100, '0 B', ''],
            '一字节' => [1, '1 B', ''],
            '同单位上界' => [1023, '1023 B', ''],
            '跨到 KiB' => [1024, '1 KiB', ''],
            '刚好一 MiB' => [MiB, '1 MiB', ''],
            '刚好一 GiB' => [GiB, '1 GiB', ''],
            '刚好一 TiB' => [TiB, '1 TiB', ''],
            '差一字节不到 MiB' => [MiB - 1, '1 MiB', 'round(1023.9990, 2) 会进位到 1024 KiB，凑满就跨一格'],
            '非整数值' => [123456789, '117.74 MiB', ''],
            '整数倍不补小数' => [5 * MiB, '5 MiB', ''],
            'PiB 之上继续升档' => [1024 * TiB, '1 PiB', ''],
            // 单位表到 YiB（2^80）为止，最小档越界前会先被 min() 夹住
            'int 上限也不会越界取键' => [PHP_INT_MAX, '8 EiB', ''],
        ];
    }

    /** precision 只影响小数位，不能影响选中的单位 */
    public function test_format_bytes_honours_precision(): void
    {
        $this->assertSame('118 MiB', formatBytes(123456789, null, 0));
        $this->assertSame('117.7 MiB', formatBytes(123456789, null, 1));
        $this->assertSame('117.74 MiB', formatBytes(123456789, null, 2));
        $this->assertSame('117.738 MiB', formatBytes(123456789, null, 3));
    }

    /**
     * $base 的既有语义是「传入值本身就以该单位计量」，Goods::trafficLabel()（流量以 MiB 存）
     * 与 InviteController 的推荐流量都依赖它，不能改成「按 base 单位渲染」。
     *
     * @dataProvider providerFormatBytesWithBase
     */
    public function test_format_bytes_treats_base_as_input_unit(int $value, string $base, string $expected): void
    {
        $this->assertSame($expected, formatBytes($value, $base));
    }

    public static function providerFormatBytesWithBase(): array
    {
        return [
            [100, 'MiB', '100 MiB'],
            [2048, 'MiB', '2 GiB'],
            [1048576, 'MiB', '1 TiB'],
            [500, 'KiB', '500 KiB'],
            [1500, 'KiB', '1.46 MiB'],
            // 单位名不在表里时 array_search 返回 false，max() 之后等价于不偏移
            [500, 'KB', '500 B'],
            [4096, 'KB', '4 KiB'],
        ];
    }

    /** 三个 URL 不安全字符 + 尾部填充，是 base64url 的全部替换表 */
    public function test_base64url_encode_replaces_url_unsafe_characters(): void
    {
        // 0xfb 0xff 0xff 的 base64 是 '+///'，全部命中替换表
        $this->assertSame('-___', base64url_encode("\xfb\xff\xff"));
        $this->assertSame('YQ', base64url_encode('a'), '填充 = 要被去掉');
        $this->assertSame('YWI', base64url_encode('ab'));
        $this->assertSame('YWJj', base64url_encode('abc'), '无需补长时不应有任何改动');

        foreach (['+', '/', '='] as $char) {
            $this->assertStringNotContainsString($char, base64url_encode(random_bytes(48)), $char.' 必须被替换掉');
        }
    }

    /**
     * @dataProvider providerBase64urlRoundTrip
     */
    public function test_base64url_round_trip(string $raw): void
    {
        $this->assertSame($raw, base64url_decode(base64url_encode($raw)), '去填充后的字符串仍要能被解回来');
    }

    public static function providerBase64urlRoundTrip(): array
    {
        return [
            [''],
            ['a'],
            ['ab'],
            ['abc'],
            ['https://example.com/sub?x=1&y=2'],
            ["\x00\x01\xfb\xff"],
            [random_bytes(48)],
            [json_encode(['协议' => 'vless', '端口' => 443])],
        ];
    }

    public function test_array_clean_removes_only_unfilled_values_recursively(): void
    {
        $input = [
            'keep' => 'value',
            'empty_string' => '',
            'null_value' => null,
            // 0 / '0' / false 是合法填写值：节点的续费成本、优惠券的折扣都允许取 0
            // （NodeRequest 写着 renewal_cost => numeric|min:0），不能当成没填而吞掉
            'zero' => 0,
            'zero_string' => '0',
            'false_flag' => false,
            'space' => ' ',
            'nested' => ['gone' => '', 'stay' => 2, 'deeper' => ['also_gone' => '']],
            'now_keep_zero_cost' => ['renewal_cost' => 0],
        ];

        $result = array_clean($input);

        $this->assertSame(
            [
                'keep' => 'value',
                'zero' => 0,
                'zero_string' => '0',
                'false_flag' => false,
                'space' => ' ',
                'nested' => ['stay' => 2],
                'now_keep_zero_cost' => ['renewal_cost' => 0],
            ],
            $result
        );
        // 按引用取参：调用方不接返回值时（NodeController、CouponController 就是这么用的）也必须生效
        $this->assertSame($result, $input);
    }

    public function test_array_clean_drops_a_branch_that_becomes_empty(): void
    {
        $input = ['only_unfilled' => ['a' => '', 'b' => null]];

        $this->assertSame([], array_clean($input));
    }

    /**
     * @dataProvider providerStringUrlsafe
     */
    public function test_string_urlsafe(string $input, bool $force_lowercase, bool $anal, string $expected, string $case): void
    {
        $this->assertSame($expected, string_urlsafe($input, $force_lowercase, $anal), $case);
    }

    public static function providerStringUrlsafe(): array
    {
        return [
            '标签被剥掉' => ['<b>bold</b> text', true, false, 'bold-text', 'strip_tags 先于符号替换'],
            '符号转下划线' => ['Foo Bar&Baz', true, false, 'foo-bar_baz', ''],
            '连续空白合成一个连字符' => ['  Multi   Space  ', true, false, '-multi-space-', '首尾空白也变成 -，调用方自行 trim'],
            '感叹号是符号不是空白' => ['Hello World!', true, false, 'hello-world_', ''],
            '保留大小写' => ['Foo Bar&Baz', false, false, 'Foo-Bar_Baz', ''],
            '中文不被当成符号' => ['日本語 タグ <script>x</script>', true, false, '日本語-タグ-x', '符号表只含 ASCII'],
            // anal 模式跑在空白折叠之后，所以 - 也会被剥掉：'a b' => 'ab'
            'anal 模式把连字符一起清掉' => ['Foo Bar&Baz', true, true, 'foobarbaz', ''],
            'anal 模式只留 ASCII 字母数字' => ['日本語 タグ x1', true, true, 'x1', ''],
            'anal 配合保留大小写' => ['Foo Bar&Baz', false, true, 'FooBarBaz', ''],
            '空串还是空串' => ['', true, false, '', ''],
        ];
    }

    /**
     * @dataProvider providerReverbPrefix
     */
    public function test_reverb_prefix_is_canonical(?string $path, string $expected, string $case): void
    {
        $this->assertSame($expected, reverb_prefix($path), $case);
    }

    public static function providerReverbPrefix(): array
    {
        return [
            [null, '', '未配置子路径（独立端口/子域）'],
            ['', '', ''],
            ['/', '', '只有一个斜杠也算未配置'],
            ['//', '', ''],
            ['casting', '/casting', ''],
            ['/casting', '/casting', ''],
            ['casting/', '/casting', '尾斜杠必须被去掉'],
            ['/casting/', '/casting', ''],
            ['  /casting/  ', '/casting', '空白与斜杠一起 trim'],
            ['/a/b', '/a/b', '多级路径保持原样'],
            ['/a/b//', '/a/b', ''],
        ];
    }

    /**
     * @dataProvider providerReverbClientPath
     */
    public function test_reverb_client_path_adds_trailing_slash(?string $path, string $expected): void
    {
        $this->assertSame($expected, reverb_client_path($path));
    }

    public static function providerReverbClientPath(): array
    {
        return [
            [null, ''],
            ['', ''],
            ['/', ''],
            ['casting', '/casting/'],
            ['/casting', '/casting/'],
            ['casting/', '/casting/'],
            ['/a/b', '/a/b/'],
        ];
    }

    /** 两个函数是同一前缀的两种形态：客户端形态多一个尾斜杠，其余必须完全一致 */
    public function test_reverb_client_path_and_prefix_stay_consistent(): void
    {
        foreach (['', null, '/', 'casting', '/casting', 'casting/', '/a/b//'] as $path) {
            $prefix = reverb_prefix($path);
            $client = reverb_client_path($path);

            if ($prefix === '') {
                $this->assertSame('', $client, '空值在两种形态下都必须是空串');

                continue;
            }

            $this->assertSame($prefix.'/', $client);
            $this->assertStringStartsWith('/', $prefix);
            $this->assertStringNotContainsString('//', trim($prefix, '/'));
            // 拿归一化后的值再喂回去，结果不能变（安装脚本与 .env 之间会重复归一化）
            $this->assertSame($prefix, reverb_prefix($prefix));
            $this->assertSame($client, reverb_client_path($client));
        }
    }
}
