<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProxyType;
use App\Utils\VNet\Supported;
use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
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
            'type' => 'required|in:'.implode(',', ProxyType::values()),
            'method' => 'required_if:type,0,1,4|exists:ss_config,name',
            'protocol' => 'required_if:type,1,4|exists:ss_config,name',
            'protocol_param' => 'nullable|string',
            'obfs' => 'required_if:type,1,4',
            'obfs_param' => 'nullable|string',
            'is_display' => 'required|numeric|between:0,3',
            'detection_type' => 'required|numeric|between:0,3',
            'single' => 'required_if:type,0,1,4|boolean',
            'port' => 'nullable|numeric|between:1,65535|different:push_port|required_if:single,1|required_if:type,2,3,5',
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
            'upload_mbps' => 'nullable|numeric|min:0',
            'download_mbps' => 'nullable|numeric|min:0',
            'ignore_client_bandwidth' => 'nullable|boolean',
            'allow_insecure' => 'nullable|boolean',
            'ports' => 'nullable|string',
        ];

        // 对于 Hysteria2，当 obfs 不为 'none' 时，obfs_param 是必填的
        if ((int) $this->type === ProxyType::HYSTERIA2->value && ! empty($this->obfs)) {
            $rules['obfs_param'] = 'required|string';
        }

        // VNET 由 VNet-SSR 承载，它只实现了 Supported 里那几组名字。名单外的值（auth_chain_b、
        // http_post、camellia-* 等）不会让节点报错下线：节点拒绝建连接，心跳照常上报，
        // 后台看到的是「在线、无人」，所以这里不能用 exists:ss_config,name 放过。
        if ((int) $this->type === ProxyType::VNET->value) {
            $rules['method'] = 'required|in:'.implode(',', Supported::METHODS);
            $rules['protocol'] = 'required|in:'.implode(',', Supported::PROTOCOLS);
            $rules['obfs'] = 'required|in:'.implode(',', Supported::OBFS);

            // 单端口（passwd 非空即 isSinglePort）靠 auth 包头的 4 字节身份编号区分用户，origin 携带不了它：
            // 选了 origin 的「单端口」节点上所有人共用节点密码，摘掉的账号也还能连
            if ($this->filled('passwd')) {
                $authOnly = array_values(array_filter(
                    Supported::PROTOCOLS,
                    fn (string $name): bool => str_starts_with($name, 'auth_')
                ));
                $rules['protocol'] = 'required|in:'.implode(',', $authOnly);
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'server.required_if' => '开启DDNS， 域名不能为空',
        ];
    }
}
