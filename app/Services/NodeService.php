<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Database\Eloquent\Builder;

class NodeService
{
    public function getActiveNodeTypes(?Builder $nodes = null): array
    {
        if (! $nodes) {
            $nodes = Node::whereStatus(1);
        }
        $types = $nodes->pluck('type')->unique();

        if ($types->contains(0)) {
            $data[] = 'ss';
        }
        if ($types->contains(1) || $types->contains(4)) {
            $data[] = 'ssr';
        }
        if ($types->contains(2)) {
            $data[] = 'v2';
        }
        if ($types->contains(3)) {
            $data[] = 'trojan';
        }

        return $data ?? [];
    }
}
