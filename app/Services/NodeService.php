<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Database\Eloquent\Builder;

class NodeService
{
    public function getActiveNodeTypes(?Builder $nodes = null): array
    {
        $types = ($nodes ?? Node::whereStatus(1))->pluck('type');

        $map = [0 => 'ss', 1 => 'ssr', 2 => 'v2', 3 => 'trojan', 4 => 'ssr'];

        return $types->intersect(array_keys($map))->map(fn ($type) => $map[$type])->unique()->values()->all();
    }
}
