<?php

namespace App\Services;

use App\Components\IP;
use App\Models\Node;

class NodeService
{
    public function getNodeGeo($id = false): int
    {
        if ($id) {
            $nodes = Node::whereStatus(1)->whereId($id)->get();
        } else {
            $nodes = Node::whereStatus(1)->get();
        }

        $result = 0;
        foreach ($nodes as $node) {
            $data = IP::IPSB($node->is_ddns ? gethostbyname($node->server) : $node->ip);
            if ($data && $node->update(['geo' => $data['latitude'].','.$data['longitude']])) {
                $result++;
            }
        }

        return $result;
    }
}
