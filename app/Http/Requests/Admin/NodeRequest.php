<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type'           => 'required|between:1,3',
            'name'           => 'required',
            'country_code'   => 'required',
            'server'         => 'required_if:is_ddns,1|nullable|ends_with:'.implode(",", config('domains')),
            'push_port'      => 'numeric|between:0,65535',
            'traffic_rate'   => 'required|numeric|min:0',
            'level'          => 'required|numeric|between:0,255',
            'speed_limit'    => 'required|numeric|min:0',
            'client_limit'   => 'required|numeric|min:0',
            'port'           => 'nullable|numeric|between:0,65535',
            'ip'             => 'ipv4|required_if:is_ddns,0|nullable',
            'ipv6'           => 'nullable|ipv6',
            'relay_server'   => 'required_if:is_relay,1',
            'relay_port'     => 'required_if:is_relay,1|numeric|between:0,65535',
            'method'         => 'required_if:type,1',
            'protocol'       => 'required_if:type,1',
            'obfs'           => 'required_if:type,1',
            'is_subscribe'   => 'boolean',
            'is_ddns'        => 'boolean',
            'is_relay'       => 'boolean',
            'is_udp'         => 'boolean',
            'detection_type' => 'between:0,3',
            'compatible'     => 'boolean',
            'single'         => 'boolean',
            'sort'           => 'required|numeric|between:0,255',
            'status'         => 'boolean',
            'v2_alter_id'    => 'required_if:type,2|numeric|between:0,65535',
            'v2_port'        => 'required_if:type,2|numeric|between:0,65535',
            'v2_method'      => 'required_if:type,2',
            'v2_net'         => 'required_if:type,2',
            'v2_type'        => 'required_if:type,2',
            'v2_tls'         => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'server.required_if' => '开启DDNS， 域名不能为空',
        ];
    }
}
