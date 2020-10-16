<?php

namespace App\Services;

use App\Components\IP;
use App\Models\Node;
use App\Models\NodeLabel;

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
            if ($data && Node::whereId($node->id)->update(['geo' => $data['latitude'].','.$data['longitude']])) {
                $result++;
            }
        }

        return $result;
    }

    // 生成节点标签
    public function makeLabels($nodeId, $labels): void
    {
        // 先删除所有该节点的标签
        NodeLabel::whereNodeId($nodeId)->delete();

        if (! empty($labels) && is_array($labels)) {
            foreach ($labels as $label) {
                $nodeLabel = new NodeLabel();
                $nodeLabel->node_id = $nodeId;
                $nodeLabel->label_id = $label;
                $nodeLabel->save();
            }
        }
    }
}
