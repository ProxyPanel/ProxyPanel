<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_ddns' => 'required|boolean',
            'name' => 'required|string',
            'server' => 'required_if:is_ddns,1|nullable|ends_with:'.implode(',', config('domains')),
            'ip' => 'required_if:is_ddns,0|nullable',
            'ipv6' => 'nullable',
            'push_port' => 'numeric|between:1,65535|different:port',
            'traffic_rate' => 'required|numeric|min:0',
            'level' => 'required|exists:level,level',
            'rule_group_id' => 'nullable|exists:rule_group,id',
            'speed_limit' => 'required|numeric|min:0',
            'client_limit' => 'required|numeric|min:0',
            'labels' => 'nullable|exists:label,id',
            'country_code' => 'required|exists:country,code',
            'description' => 'nullable|string',
            'next_renewal_date' => 'nullable|date',
            'subscription_term' => ['nullable', 'string', 'regex:/^\d+\s+(day|days|week|weeks|month|months|year|years)$/'],
            'renewal_cost' => 'nullable|numeric|min:0',
            'sort' => 'required|numeric|between:0,255',
            'is_udp' => 'required|boolean',
            'status' => 'required|boolean',
            'type' => 'required|numeric|between:0,4',
            'method' => 'required_if:type,0,1,4|exists:ss_config,name',
            'protocol' => 'required_if:type,1,4|exists:ss_config,name',
            'protocol_param' => 'nullable|string',
            'obfs' => 'required_if:type,1,4|exists:ss_config,name',
            'obfs_param' => 'nullable|string',
            'is_display' => 'required|numeric|between:0,3',
            'detection_type' => 'required|numeric|between:0,3',
            'single' => 'required_if:type,0,1,4|boolean',
            'port' => 'nullable|numeric|between:1,65535|different:push_port|required_if:single,1|required_if:type,2|required_if:type,3',
            'passwd' => 'exclude_unless:type,1,type,4|required_if:single,1|string|nullable',
            'v2_alter_id' => 'nullable|numeric|between:0,65535',
            'v2_method' => 'required_if:type,2',
            'v2_net' => 'required_if:type,2',
            'v2_type' => 'required_if:type,2',
            'v2_host' => 'nullable|string',
            'v2_path' => 'nullable|string',
            'v2_sni' => 'nullable|string',
            'v2_tls' => 'required_if:type,2|boolean',
            'tls_provider' => 'nullable|json',
            'relay_node_id' => 'nullable|exists:node,id',
        ];
    }

    public function messages(): array
    {
        return [
            'server.required_if' => '开启DDNS， 域名不能为空',
        ];
    }
}
