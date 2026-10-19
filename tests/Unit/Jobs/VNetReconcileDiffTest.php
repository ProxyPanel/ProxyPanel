<?php

namespace Tests\Unit\Jobs;

use App\Jobs\VNet\ReconcileNode;
use PHPUnit\Framework\TestCase;

/**
 * 对账的判据只是两个 uid 集合的差：这里钉住「谁都不缺时不动手」和「节点集合为空时只补不删」。
 */
class VNetReconcileDiffTest extends TestCase
{
    public function test_identical_sets_produce_no_changes(): void
    {
        $this->assertSame(
            ['add' => [], 'del' => []],
            ReconcileNode::diff([11, 12, 13], [13, 12, 11]),
        );
    }

    public function test_empty_node_set_only_needs_additions(): void
    {
        $this->assertSame(
            ['add' => [11, 12], 'del' => []],
            ReconcileNode::diff([11, 12], []),
        );
    }

    public function test_extra_accounts_on_the_node_are_removed(): void
    {
        $this->assertSame(
            ['add' => [], 'del' => [99]],
            ReconcileNode::diff([11, 12], [11, 12, 99]),
        );
    }

    public function test_both_directions_are_reported_at_once(): void
    {
        $this->assertSame(
            ['add' => [12], 'del' => [99]],
            ReconcileNode::diff([11, 12], [11, 99]),
        );
    }
}
