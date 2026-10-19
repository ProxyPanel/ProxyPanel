<?php

namespace Tests\Unit\Utils;

use App;
use Tests\TestCase;

/**
 * localized_date() 的入参契约：视图里既传日期字符串也传库里存的 unix 时间戳。
 */
class LocalizedDateTest extends TestCase
{
    protected function tearDown(): void
    {
        App::setLocale(config('app.locale'));

        parent::tearDown();
    }

    public function test_unix_timestamp_matches_the_same_moment_as_a_string(): void
    {
        $ts = 1759568405;

        $this->assertSame(localized_date(date('Y-m-d H:i:s', $ts)), localized_date($ts));
    }

    public function test_unfilled_values_render_as_empty_string(): void
    {
        $this->assertSame('', localized_date(null));
        $this->assertSame('', localized_date(0));
        $this->assertSame('', localized_date(''));
    }

    public function test_output_follows_the_active_locale(): void
    {
        App::setLocale('zh_CN');
        $zh = localized_date('2025-10-04 09:00:05');

        App::setLocale('en');
        $en = localized_date('2025-10-04 09:00:05');

        $this->assertStringContainsString('年', $zh);
        $this->assertStringContainsString('October', $en);
    }

    public function test_day_only_values_do_not_gain_a_time(): void
    {
        App::setLocale('en');

        $this->assertStringNotContainsString('AM', localized_date('2025-10-04'));
        $this->assertStringContainsString('9:00', localized_date('2025-10-04 09:00:00'));
    }
}
