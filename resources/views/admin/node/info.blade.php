@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/switchery/switchery.min.css" rel="stylesheet">
    <style>
        .hidden {
            display: none
        }

        .bootstrap-select .dropdown-menu {
            max-height: 50vh !important;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel :title="trans(isset($node) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.node.attribute')])">
            <x-alert type="info" :message="trans('admin.node.info.hint')" />
            <x-admin.form.container handler="Submit()" enctype="true">
                <div class="row">
                    <div class="col-lg-6">
                        <h4 class="example-title">{{ trans('admin.node.info.basic') }}</h4>
                        <x-admin.form.input name="is_ddns" type="checkbox" :label="trans('model.node.ddns')" attribute="data-plugin=switchery onchange=switchSetting('is_ddns')"
                                            :help="trans('admin.node.info.ddns_hint')" />
                        <x-admin.form.input name="name" :label="trans('model.node.name')" required />
                        <x-admin.form.input name="server" :label="trans('model.node.domain')" :placeholder="trans('admin.node.info.domain_placeholder')" :help="trans('admin.node.info.domain_hint')" />
                        <x-admin.form.input name="ip" :label="trans('model.node.ipv4')" :placeholder="trans('admin.node.info.ipv4_placeholder')" required :help="trans('admin.node.info.ipv4_hint')" />
                        <x-admin.form.input name="ipv6" :label="trans('model.node.ipv6')" :placeholder="trans('admin.node.info.ipv6_placeholder')" :help="trans('admin.node.info.ipv6_hint')" />
                        <x-admin.form.input name="push_port" type="number" :label="trans('model.node.push_port')" :help="trans('admin.node.info.push_port_hint')" />
                        <x-admin.form.input name="traffic_rate" type="number" :label="trans('model.node.data_rate')" step="0.01" required :help="trans('admin.node.info.data_rate_hint')" />

                        <x-admin.form.select name="level" :label="trans('model.common.level')" :options="$levels" :help="trans('admin.node.info.level_hint')" />
                        <x-admin.form.select name="rule_group_id" :label="trans('model.rule_group.attribute')" :options="$ruleGroups" :placeholder="trans('common.none')" />
                        <x-admin.form.input-group name="speed_limit" type="number" :label="trans('model.node.traffic_limit')" append="Mbps" required />
                        <x-admin.form.input name="client_limit" type="number" :label="trans('model.node.client_limit')" required />
                        <x-admin.form.input name="sort" type="number" :label="trans('model.common.sort')" required />
                        <x-admin.form.select name="labels" :label="trans('model.node.label')" :options="$labels" multiple />
                        <x-admin.form.select name="country_code" :label="trans('model.node.country')">
                            @foreach ($countries as $country)
                                <option data-icon="fi fis fi-{{ $country->code }}" value="{{ $country->code }}">
                                    {{ strtoupper($country->code) . ' - ' . $country->name }}
                                </option>
                            @endforeach
                        </x-admin.form.select>

                        <!-- 节点 细则部分 -->
                        <x-admin.form.input-group name="next_renewal_date" attribute="data-plugin=datepicker" :label="trans('model.node.next_renewal_date')" />
                        <x-admin.form.skeleton name="subscription_term_value" :label="trans('model.node.subscription_term')">
                            <div class="input-group">
                                <input class="form-control" id="subscription_term_value" type="number" min="1" />
                                <select class="form-control" id="subscription_term_unit" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                    <option value="days">{{ ucfirst(trans('validation.attributes.day')) }}</option>
                                    <option value="months">{{ ucfirst(trans('validation.attributes.month')) }}</option>
                                    <option value="years">{{ ucfirst(trans('validation.attributes.year')) }}</option>
                                </select>
                            </div>
                        </x-admin.form.skeleton>
                        <x-admin.form.input name="renewal_cost" type="number" :label="trans('model.node.renewal_cost')" step="0.01" />
                        <x-admin.form.textarea name="description" :label="trans('model.common.description')" />
                    </div>

                    <div class="col-lg-6">
                        <h4 class="example-title">{{ trans('admin.node.info.extend') }}</h4>
                        <x-admin.form.radio-group name="is_display" :label="trans('model.node.display')" :options="[
                            0 => trans('admin.node.info.display.invisible'),
                            1 => trans('admin.node.info.display.node'),
                            2 => trans('admin.node.info.display.sub'),
                            3 => trans('admin.node.info.display.all'),
                        ]" :help="trans('admin.node.info.display.hint')" />
                        <x-admin.form.radio-group name="detection_type" :label="trans('model.node.detection')" :options="[
                            0 => trans('common.close'),
                            1 => trans('admin.node.info.detection.tcp'),
                            2 => trans('admin.node.info.detection.icmp'),
                            3 => trans('admin.node.info.detection.all'),
                        ]" :help="trans('admin.node.info.detection.hint')" />

                        <!-- 中转 设置部分 -->
                        <x-admin.form.select name="relay_node_id" :label="trans('model.node.transfer')" :options="$nodes" :placeholder="trans('common.none')" />

                        <hr />
                        <div class="relay-config">
                            <x-admin.form.input name="port" type="number" :label="trans('model.node.relay_port')" />
                        </div>
                        <!-- 代理 设置部分 -->
                        <div class="proxy-config">
                            <x-admin.form.radio-group name="type" :label="trans('model.common.type')" :options="[0 => 'Shadowsocks', 1 => 'ShadowsocksR', 2 => 'V2Ray', 3 => 'Trojan', 4 => 'VNET']" />
                            <hr />
                            <!-- SS/SSR 设置部分 -->
                            <div class="ss-setting">
                                <x-admin.form.select name="method" :label="trans('model.node.method')" :options="$methods" />
                                <!-- TODO: Supporting SS plugin -->
                                {{--                                <x-admin.form.select name="plugin" :label="trans('model.node.plugin')" :options="['none'=>'None', 'kcptun'=>'Kcptun', 'v2ray-plugin' => 'V2ray-plugin', 'cloak'=> 'Cloak', 'shadow-tls' => 'Shadow-tls']" /> --}}
                                {{--                                <x-admin.form.textarea name="plugin_opts" :label="trans('model.node.plugin_opts')" /> --}}

                                <div class="ssr-setting">
                                    <x-admin.form.select name="protocol" :label="trans('model.node.protocol')" :options="$protocols" />
                                    <x-admin.form.textarea name="protocol_param" :label="trans('model.node.protocol_param')" />
                                    <x-admin.form.select name="obfs" :label="trans('model.node.obfs')" :options="$obfs" />
                                    <x-admin.form.textarea name="obfs_param" :label="trans('model.node.obfs_param')" :placeholder="trans('admin.node.info.obfs_param_hint')" />
                                    <x-admin.form.skeleton name="proxy_info" :label="trans('admin.node.proxy_info')">
                                        <div class="text-help">
                                            {!! trans('admin.node.proxy_info_hint') !!}
                                        </div>
                                    </x-admin.form.skeleton>
                                </div>

                                <hr />
                                <x-admin.form.input name="single" type="checkbox" :label="trans('model.node.single')"
                                                    attribute="data-plugin=switchery onchange=switchSetting('single')" :help="trans('admin.node.info.single_hint')" />

                                <div class="single-setting">
                                    <x-admin.form.input name="port" type="number" :label="trans('model.node.service_port')" :help="trans('admin.node.info.single_hint')" />
                                    <x-admin.form.input name="passwd" :label="trans('model.node.single_passwd')" />
                                </div>
                            </div>

                            <!-- V2ray TODO: Supporting new feature -->
                            <div class="v2ray-setting">
                                <x-admin.form.input name="v2_alter_id" :label="trans('model.node.v2_alter_id')" />
                                <x-admin.form.input name="port" type="number" :label="trans('model.node.service_port')" />
                                <x-admin.form.select name="v2_method" :label="trans('model.node.method')" :options="[
                                    'none' => 'none',
                                    'auto' => 'auto',
                                    'zero' => 'zero',
                                    'aes-128-gcm' => 'aes-128-gcm',
                                    'chacha20-poly1305' => 'chacha20-poly1305',
                                ]" :help="trans('admin.node.info.v2_method_hint')" />
                                <x-admin.form.select name="v2_net" :label="trans('model.node.v2_net')" :options="[
                                    'tcp' => 'TCP',
                                    'kcp' => 'mKCP',
                                    'ws' => 'WebSocket',
                                    'httpupgrade' => 'HTTPUpgrade',
                                    'xhttp' => 'xHTTP   ',
                                    'h2' => 'HTTP/2',
                                    'quic' => 'QUIC',
                                    'domainsocket' => 'DomainSocket',
                                    'grpc' => 'gRPC',
                                ]" :help="trans('admin.node.info.v2_net_hint')" />
                                <x-admin.form.select name="v2_type" :label="trans('model.node.v2_cover')" :options="[
                                    'none' => trans('admin.node.info.v2_cover.none'),
                                    'http' => trans('admin.node.info.v2_cover.http'),
                                    'srtp' => trans('admin.node.info.v2_cover.srtp'),
                                    'utp' => trans('admin.node.info.v2_cover.utp'),
                                    'wechat-video' => trans('admin.node.info.v2_cover.wechat'),
                                    'dtls' => trans('admin.node.info.v2_cover.dtls'),
                                    'wireguard' => trans('admin.node.info.v2_cover.wireguard'),
                                ]" />
                                <x-admin.form.input name="v2_host" :label="trans('model.node.v2_host')" :help="trans('admin.node.info.v2_host_hint')" />
                                <x-admin.form.input name="v2_path" :label="trans('model.node.v2_path')" />
                                <x-admin.form.input name="v2_sni" :label="trans('model.node.v2_sni')" />
                                <x-admin.form.input name="v2_tls" type="checkbox" :label="trans('model.node.v2_tls')"
                                                    attribute="data-plugin=switchery onchange=switchSetting('v2_tls')" />
                                <x-admin.form.input name="tls_provider" :label="trans('model.node.v2_tls_provider')" :help="trans('admin.node.info.v2_tls_provider_hint')" />
                                {{--                                <x-admin.form.input name="mux" type="checkbox" :label="trans('model.node.mux')" attribute="data-plugin=switchery onchange=switchSetting('mux')" --}}
                                {{--                                                    :help="trans('admin.node.info.mux')" /> --}}
                            </div>

                            <!-- Trojan 设置部分 -->
                            <div class="trojan-setting">
                                <x-admin.form.input name="port" type="number" :label="trans('model.node.service_port')" />
                            </div>
                        </div>

                        <x-admin.form.input name="is_udp" type="checkbox" :label="trans('model.node.udp')" attribute="data-plugin=switchery" />
                        <x-admin.form.input name="status" type="checkbox" :label="trans('common.status.attribute')" attribute="data-plugin=switchery" />

                        <div class="col-12 form-actions text-right">
                            <a class="btn btn-secondary" href="{{ route('admin.node.index') }}">{{ trans('common.back') }}</a>
                            <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                        </div>
                    </div>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/vendor/switchery/switchery.min.js"></script>
    <script src="/assets/global/js/Plugin/switchery.js"></script>
    <script>
        const string = "{{ strtolower(Str::random()) }}";

        function calculateNextNextRenewalDate() {
            const nextRenewalDate = $("#next_renewal_date").val();
            const termValue = parseInt($("#subscription_term_value").val() || 0);
            const termUnit = $("#subscription_term_unit").val();
            const nextNextRenewalDate = $("#next_next_renewal_date");

            if (!nextRenewalDate || termValue <= 0) {
                nextNextRenewalDate.val("");
                return;
            }

            const currentDate = new Date(nextRenewalDate);
            const originalDay = currentDate.getDate();

            if (termUnit === "months") {
                // 获取当前月份和年份
                let targetMonth = currentDate.getMonth() + termValue;
                let targetYear = currentDate.getFullYear() + Math.floor(targetMonth / 12);
                targetMonth = targetMonth % 12;

                // 先将日期设置为目标月的同一天
                currentDate.setFullYear(targetYear, targetMonth, originalDay);

                // 检查是否因月份天数不同而被自动调整
                if (currentDate.getMonth() !== targetMonth) {
                    // 如果被调整，说明目标月份的天数比原始日期少
                    // 将日期设置为目标月份的最后一天
                    currentDate.setFullYear(targetYear, targetMonth + 1, 0);
                }
            } else {
                // 处理天数和年份的情况
                const adjustments = {
                    days: "Date",
                    years: "FullYear"
                };
                currentDate[`set${adjustments[termUnit]}`](
                    currentDate[`get${adjustments[termUnit]}`]() + termValue
                );
            }

            // 显示计算结果（如果需要）
            if ($("#next_next_renewal_date").length) {
                nextNextRenewalDate.val(currentDate.toISOString().split("T")[0]);
            }
        }

        $(document).ready(function() {
            // 初始化UI元素
            initializeUI();

            // 绑定事件
            bindEvents();

            // 准备节点数据
            let nodeData = {
                is_ddns: 0,
                push_port: 1080,
                traffic_rate: 1.0,
                level: 0,
                speed_limit: 1000,
                client_limit: 1000,
                is_display: 3,
                detection_type: 0,
                is_udp: 1,
                status: 1,
                sort: 1,
                method: '{{ $methodDefault }}',
                protocol: '{{ $protocolDefault }}',
                obfs: '{{ $obfsDefault }}',
                relay_node_id: '',
                type: 1
            };
            @isset($node)
                // 反向解析节点数据以    适配表单字段
                const node = @json($node);
                nodeData = {
                    single: node.type === 0 || node.type === 1 || node.type === 4 ? (node.passwd ? 1 : 0) : undefined,
                    ...node,
                    v2_tls: node.type === 2 ? (node?.v2_tls === 'tls' ? 1 : 0) : undefined,
                };

                // 处理订阅期限字段
                if (node.subscription_term) {
                    const [value, unit] = node.subscription_term.split(" ");
                    nodeData.subscription_term_value = value;
                    nodeData.subscription_term_unit = unit;
                }
            @endisset

            // 自动填充表单
            autoPopulateForm(nodeData);
            calculateNextNextRenewalDate();
        });

        function initializeUI() {
            $(".single-setting").hide();
            $("#v2_path").val("/" + string);
        }

        function bindEvents() {
            $("input:radio[name='type']").on("change", updateServiceType);
            $("#obfs").on("changed.bs.select", toggleObfsParam);
            $("#relay_node_id").on("changed.bs.select", toggleRelayConfig);
            $("#v2_net").on("changed.bs.select", updateV2RaySettings);
            $(document).on("change", "#next_renewal_date, #subscription_term_value, #subscription_term_unit", calculateNextNextRenewalDate);
        }

        function switchSetting(id) {
            const check = document.getElementById(id).checked;
            if (id === "single") {
                $(".single-setting").toggle(check);
                $("#single_port").attr({
                    "hidden": !check,
                    "required": check
                });
                if (!check) $("#passwd").val("");
            } else if (id === "is_ddns") {
                $("#ip, #ipv6").attr("readonly", check).val("");
                $("#server").attr("required", check);
            }
        }

        // 设置服务类型
        function updateServiceType() {
            const type = parseInt($(this).val());
            const settingsMap = {
                0: [".ss-setting"],
                1: [".ss-setting", ".ssr-setting"],
                2: [".v2ray-setting", "#v2_port"],
                3: [".trojan-setting", "#trojan_port"],
                4: [".ss-setting", ".ssr-setting"]
            };
            $(".ss-setting, .ssr-setting, .v2ray-setting, .trojan-setting").hide();
            Object.keys(settingsMap).forEach(key => $(settingsMap[key].join(",")).hide());
            (settingsMap[type] || []).forEach(selector => $(selector).show());
        }

        function toggleObfsParam() {
            const $obfsParam = $("#obfs_param");
            const show = $("#obfs").val() !== "plain";
            $obfsParam.closest('.form-group').toggle(show);
            if (!show) $obfsParam.val("");
        }

        function toggleRelayConfig() {
            const hasRelay = $("#relay_node_id").val() !== "";
            $(".relay-config").toggle(hasRelay);
            $(".proxy-config").toggle(!hasRelay);
            $("#relay_port").attr({
                hidden: !hasRelay,
                required: hasRelay
            });
        }

        // 设置V2Ray详细设置
        function updateV2RaySettings() {
            const net = $(this).val();
            const $type = $(".v2_type");
            const $typeOption = $("#type_option");
            const $host = $(".v2_host");
            const $path = $("#v2_path");
            $type.show();
            $host.show();
            if (!$path.val()) {
                $path.val("/" + string);
            }
            switch (net) {
                case "ws":
                case "http":
                    $type.hide();
                    break;
                case "domainsocket":
                    $type.hide();
                    $host.hide();
                    break;
                case "quic":
                    $typeOption.attr("disabled", false);
                    if (!$path.val()) {
                        $path.val(string);
                    }
                    break;
                case "kcp":
                case "tcp":
                default:
                    $typeOption.attr("disabled", true);
                    break;
            }
            $("#v2_type").selectpicker("refresh");
        }

        // ajax同步提交
        function Submit() {
            // 收集表单数据
            const data = collectFormData('.form-horizontal');

            // 拼接 subscription_term
            const termValue = $("#subscription_term_value").val();
            const termUnit = $("#subscription_term_unit").val();
            data["subscription_term"] = termValue ? `${termValue} ${termUnit}` : null;

            // 处理端口字段（根据节点类型选择正确的端口字段）
            switch (parseInt(data.type)) {
                case 0: // Shadowsocks
                    data.port = 0; // SS类型不需要端口设置
                    break;
                case 1: // ShadowsocksR
                case 4: // VNET
                    if (data.single !== 1) {
                        data.port = 0; // 非single模式不需要端口设置
                    }
                    break;
            }

            // 发送 AJAX 请求
            ajaxRequest({
                url: '{{ isset($node) ? route('admin.node.update', $node['id']) : route('admin.node.store') }}',
                method: '{{ isset($node) ? 'PUT' : 'POST' }}',
                data: data,
                success: function(ret) {
                    handleResponse(ret, {
                        redirectUrl: '{{ route('admin.node.index') . (Request::getQueryString() ? '?' . Request::getQueryString() : '') }}'
                    });
                },
                error: function(xhr) {
                    handleErrors(xhr, {
                        form: '.form-horizontal'
                    });
                }
            });

            return false;
        }

        // 服务条款
        window.showTnc = function() {
            const jsonConfig = {
                "additional_ports": {
                    "443": {
                        "passwd": "ProxyPanel",
                        "method": "none",
                        "protocol": "auth_chain_a",
                        "protocol_param": "#",
                        "obfs": "plain",
                        "obfs_param": "fe2.update.microsoft.com"
                    }
                }
            };

            swal.fire({
                title: "[节点 user-config.json 配置示例]",
                width: "36em",
                html: `
                    <div class="text-left">
                        <ol>
                            <li>请勿直接复制黏贴以下配置，SSR(R)会报错的</li>
                            <li>确保服务器时间为CST</li>
                        </ol>
                        <pre class="bg-grey-800 text-white">${JSON.stringify(jsonConfig, null, 2)}</pre>
                    </div>
                `,
                icon: "info"
            });
        };

        // 模式提示
        window.showPortsOnlyConfig = function() {
            swal.fire({
                title: "[节点 user-config.json 配置示例]",
                width: "36em",
                html: `
                  <ul class="bg-grey-800 text-white text-left">
                      <li>严格模式："additional_ports_only": "true"</li>
                      <li>兼容模式："additional_ports_only": "false"</li>
                  </ul>
                `,
                icon: "info"
            });
        };
    </script>
@endsection
