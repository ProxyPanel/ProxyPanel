<?php

namespace Tests\Unit\Utils;

use App\Http\Requests\Admin\RuleRequest;
use PHPUnit\Framework\TestCase;

/**
 * 规则方言判据：RE2 不支持的构造在 Go 节点会被静默跳过（规则失效不报错），
 * 面板保存时必须挡下；RE2 兼容子集在 Python 后端同样能跑，两个后端共用一份判据。
 */
class RuleDialectTest extends TestCase
{
    public function test_re2_compatible_patterns_pass(): void
    {
        $ok = [
            '(.*.||)(baidu|qq|bilibili).(org|com|net|cn)',
            '(torrent|.torrent|peer_id=|info_hash)',
            '^([a-z0-9-]+\.)+example\.com$',
            'ulog.imap.baidu',
        ];
        foreach ($ok as $pattern) {
            $this->assertTrue(RuleRequest::re2Compatible($pattern), "不该误伤 RE2 兼容写法: {$pattern}");
        }
    }

    public function test_re2_incompatible_patterns_are_rejected(): void
    {
        $bad = [
            '(?=foo)bar',
            '(?!foo)bar',
            '(?<=foo)bar',
            '(?<!foo)bar',
            '(?>a+)b',
            '(?(1)a|b)',
            '(a)\1',
            '\Ganchor',
        ];
        foreach ($bad as $pattern) {
            $this->assertFalse(RuleRequest::re2Compatible($pattern), "应挡掉 RE2 不支持构造: {$pattern}");
        }
    }
}
