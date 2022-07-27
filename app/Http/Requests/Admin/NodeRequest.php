<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_ddns'        => 'required|boolean',
            'name'           => 'required|string',
            'server'         => 'required_if:is_ddns,1|nullable|ends_with:'.implode(',', config('domains')),
            'ip'             => 'required_if:is_ddns,0|nullable',
            'ipv6'           => 'nullable',
            'push_port'      => 'numeric|between:1,65535|different:port',
            'traffic_rate'   => 'required|numeric|min:0',
            'level'          => 'required|numeric|exists:level,level',
            'rule_group_id'  => 'nullable|exists:rule_group,id',
            'speed_limit'    => 'required|numeric|min:0',
            'client_limit'   => 'required|numeric|min:0',
            'labels'         => 'nullable|exists:label,id',
            'country_code'   => 'required|exists:country,code',
            'description'    => 'nullable|string',
            'sort'           => 'required|numeric|between:0,255',
            'is_udp'         => 'required|boolean',
            'status'         => 'required|boolean',
            'type'           => 'required|numeric|between:0,4',
            'method'         => 'required|exists:ss_config,name',
            'protocol'       => 'required_if:type,1,4|exists:ss_config,name',
            'protocol_param' => 'nullable|string',
            'obfs'           => 'required_if:type,1,4|exists:ss_config,name',
            'obfs_param'     => 'nullable|string',
            'is_subscribe'   => 'required|boolean',
            'detection_type' => 'required|numeric|between:0,3',
            'single'         => 'required|boolean',
            'port'           => 'required_unless:single,0|numeric|between:1,65535|different:push_port',
            'passwd'         => 'exclude_unless:type,1,type,4|required_if:single,1|string|nullable',
            'v2_alter_id'    => 'required_if:type,2|numeric|between:0,65535',
            'v2_method'      => 'required_if:type,2',
            'v2_net'         => 'required_if:type,2',
            'v2_type'        => 'required_if:type,2',
            'v2_host'        => 'string|nullable',
            'v2_path'        => 'string|nullable',
            'v2_sni'         => 'string|nullable',
            'v2_tls'         => 'required_if:type,2|boolean',
            'tls_provider'   => 'json|nullable',
            'relay_node_id'  => 'nullable|exists:node,id',
        ];
    }

    public function messages()
    {
        return [
            'server.required_if' => '开启DDNS， 域名不能为空',
        ];
    }
}
