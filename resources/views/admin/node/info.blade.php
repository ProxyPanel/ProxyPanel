@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/switchery/switchery.min.css" rel="stylesheet">
    <style>
        .hidden {
            display: none
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($node) ? trans('admin.action.edit_item', ['attribute' => trans('model.node.attribute')]) : trans('admin.action.add_item', ['attribute' => trans('model.node.attribute')]) }}
                </h2>
            </div>
            <div class="alert alert-info" role="alert">
                <button class="close" data-dismiss="alert" aria-label="{{ trans('common.close') }}">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">{{ trans('common.close') }}</span>
                </button>
                {!! trans('admin.node.info.hint') !!}
            </div>
            <div class="panel-body">
                <form class="form-horizontal" id="nodeForm">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="example-wrap">
                                <h4 class="example-title">{{ trans('admin.node.info.basic') }}</h4>
                                <div class="example">
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="is_ddns">{{ trans('model.node.ddns') }}</label>
                                        <div class="col-md-9">
                                            <input id="is_ddns" name="is_ddns" data-plugin="switchery" type="checkbox" onchange="switchSetting('is_ddns')">
                                        </div>
                                        <div class="text-help offset-md-3">
                                            {!! trans('admin.node.info.ddns_hint') !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="name"> {{ trans('model.node.name') }} </label>
                                        <input class="form-control col-md-4" id="name" name="name" type="text" required>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="server"> {{ trans('model.node.domain') }} </label>
                                        <input class="form-control col-md-4" id="server" name="server" type="text"
                                               placeholder="{{ trans('admin.node.info.domain_placeholder') }}">
                                        <span class="text-help offset-md-3">{{ trans('admin.node.info.domain_hint') }}</span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="ip"> {{ trans('model.node.ipv4') }} </label>
                                        <input class="form-control col-md-4" id="ip" name="ip" type="text"
                                               placeholder="{{ trans('admin.node.info.ipv4_placeholder') }}" required>
                                        <span class="text-help offset-md-3">{{ trans('admin.node.info.ipv4_hint') }}</span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="ipv6"> {{ trans('model.node.ipv6') }} </label>
                                        <input class="form-control col-md-4" id="ipv6" name="ipv6" type="text"
                                               placeholder="{{ trans('admin.node.info.ipv6_placeholder') }}">
                                        <span class="text-help offset-md-3">{{ trans('admin.node.info.ipv6_hint') }}</span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="push_port"> {{ trans('model.node.push_port') }} </label>
                                        <input class="form-control col-md-4" id="push_port" name="push_port" type="number" value="1080">
                                        <span class="text-help offset-md-3">{{ trans('admin.node.info.push_port_hint') }}</span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="traffic_rate"> {{ trans('model.node.data_rate') }} </label>
                                        <input class="form-control col-md-4" id="traffic_rate" name="traffic_rate" type="number" value="1.0" step="0.01"
                                               required>
                                        <div class="text-help offset-md-3">{{ trans('admin.node.info.data_rate_hint') }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="level">{{ trans('model.common.level') }}</label>
                                        <select class="col-md-5 form-control show-tick" id="level" name="level" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary">
                                            @foreach ($levels as $level)
                                                <option value="{{ $level->level }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="text-help offset-md-3"> {{ trans('admin.node.info.level_hint') }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="rule_group_id">{{ trans('model.rule_group.attribute') }}</label>
                                        <select class="col-md-5 form-control show-tick" id="rule_group_id" name="rule_group_id" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary">
                                            <option value="">{{ trans('common.none') }}</option>
                                            @foreach ($ruleGroups as $ruleGroup)
                                                <option value="{{ $ruleGroup->id }}">{{ $ruleGroup->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="speed_limit">{{ trans('model.node.traffic_limit') }}</label>
                                        <div class="col-md-4 input-group p-0">
                                            <input class="form-control" id="speed_limit" name="speed_limit" type="number" value="1000" required>
                                            <span class="input-group-text">Mbps</span>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="client_limit">{{ trans('model.node.client_limit') }}</label>
                                        <input class="form-control col-md-4" id="client_limit" name="client_limit" type="number" value="1000" required>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="sort">{{ trans('model.common.sort') }}</label>
                                        <input class="form-control col-md-4" id="sort" name="sort" type="text" value="1" required />
                                        <span class="col-md-5"></span>
                                        <div class="text-help offset-md-3"> {{ trans('admin.sort_asc') }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="labels">{{ trans('model.node.label') }}</label>
                                        <select class="col-md-5 form-control show-tick" id="labels" name="labels" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary" multiple>
                                            @foreach ($labels as $label)
                                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="country_code"> {{ trans('model.node.country') }} </label>
                                        <select class="col-md-5 form-control" id="country_code" name="country_code" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary">
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->code }}">{{ $country->code . ' - ' . $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- 节点 细则部分 -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="next_renewal_date">{{ trans('model.node.next_renewal_date') }}</label>
                                        <input class="form-control col-md-4" id="next_renewal_date" name="next_renewal_date" data-plugin="datepicker"
                                               type="text" autocomplete="off" />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="subscription_term_value">
                                            {{ trans('model.node.subscription_term') }}
                                        </label>
                                        <div class="col-md-4 input-group p-0">
                                            <input class="form-control" id="subscription_term_value" type="number" min="1" />
                                            <select class="form-control" id="subscription_term_unit" data-plugin="selectpicker"
                                                    data-style="btn-outline btn-primary">
                                                <option value="days" selected>{{ ucfirst(trans('validation.attributes.day')) }}</option>
                                                <option value="months">{{ ucfirst(trans('validation.attributes.month')) }}</option>
                                                <option value="years">{{ ucfirst(trans('validation.attributes.year')) }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="renewal_cost">{{ trans('model.node.renewal_cost') }}</label>
                                        <div class="col-md-4 input-group p-0">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}
                                                </span>
                                            </div>
                                            <input class="form-control" id="renewal_cost" name="renewal_cost" type="number" step="0.01" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="description"> {{ trans('model.common.description') }} </label>
                                        <input class="form-control col-md-6" id="description" name="description" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="example-wrap">
                                <h4 class="example-title">{{ trans('admin.node.info.extend') }}</h4>
                                <div class="example">
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="is_display">{{ trans('model.node.display') }}</label>
                                        <ul class="col-md-9 list-unstyled list-inline">
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="invisible" name="is_display" type="radio" value="0" />
                                                    <label for="invisible">{{ trans('admin.node.info.display.invisible') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="page_only" name="is_display" type="radio" value="1" />
                                                    <label for="page_only">{{ trans('admin.node.info.display.node') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="sub_only" name="is_display" type="radio" value="2" />
                                                    <label for="sub_only">{{ trans('admin.node.info.display.sub') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="visible" name="is_display" type="radio" value="3" checked />
                                                    <label for="visible">{{ trans('admin.node.info.display.all') }}</label>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="text-help offset-md-3"> {{ trans('admin.node.info.display.hint') }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="detection_type">{{ trans('model.node.detection') }}</label>
                                        <ul class="col-md-9 list-unstyled list-inline">
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="detect_disable" name="detection_type" type="radio" value="0" checked />
                                                    <label for="detect_disable">{{ trans('common.close') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="detect_tcp" name="detection_type" type="radio" value="1" />
                                                    <label for="detect_tcp">{{ trans('admin.node.info.detection.tcp') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="detect_icmp" name="detection_type" type="radio" value="2" />
                                                    <label for="detect_icmp">{{ trans('admin.node.info.detection.icmp') }}</label>
                                                </div>
                                            </li>
                                            <li class="list-inline-item">
                                                <div class="radio-custom radio-primary">
                                                    <input id="detect_all" name="detection_type" type="radio" value="3" />
                                                    <label for="detect_all">{{ trans('admin.node.info.detection.all') }}</label>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="text-help offset-md-3"> {{ trans('admin.node.info.detection.hint') }}</div>
                                    </div>
                                    <!-- 中转 设置部分 -->
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="relay_node_id">{{ trans('model.node.transfer') }}</label>
                                        <select class="col-md-5 form-control show-tick" id="relay_node_id" name="relay_node_id" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary">
                                            <option value="">{{ trans('common.none') }}</option>
                                            @foreach ($nodes as $name => $id)
                                                <option value="{{ $id }}">{{ $id }} - {{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <hr />
                                    <!-- 代理 设置部分 -->
                                    <div class="proxy-config">
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="type">{{ trans('model.common.type') }}</label>
                                            <ul class="col-md-9 list-unstyled list-inline">
                                                <li class="list-inline-item">
                                                    <div class="radio-custom radio-primary">
                                                        <input id="shadowsocks" name="type" type="radio" value="0">
                                                        <label for="shadowsocks">Shadowsocks</label>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item">
                                                    <div class="radio-custom radio-primary">
                                                        <input id="shadowsocksR" name="type" type="radio" value="1">
                                                        <label for="shadowsocksR">ShadowsocksR</label>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item">
                                                    <div class="radio-custom radio-primary">
                                                        <input id="v2ray" name="type" type="radio" value="2">
                                                        <label for="v2ray">V2Ray</label>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item">
                                                    <div class="radio-custom radio-primary">
                                                        <input id="trojan" name="type" type="radio" value="3">
                                                        <label for="trojan">Trojan</label>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item">
                                                    <div class="radio-custom radio-primary">
                                                        <input id="vnet" name="type" type="radio" value="4">
                                                        <label for="vnet">VNET</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <hr />
                                        <!-- SS/SSR 设置部分 -->
                                        <div class="ss-setting">
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="method">{{ trans('model.node.method') }}</label>
                                                <select class="col-md-5 form-control" id="method" name="method" data-plugin="selectpicker"
                                                        data-style="btn-outline btn-primary">
                                                    @foreach (Helpers::methodList() as $method)
                                                        <option value="{{ $method->name }}" @if (!isset($node) && $method->is_default) selected @endif>{{ $method->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="ssr-setting">
                                                <div class="form-group row">
                                                    <label class="col-md-3 col-form-label" for="protocol">{{ trans('model.node.protocol') }}</label>
                                                    <select class="col-md-5 form-control" id="protocol" name="protocol" data-plugin="selectpicker"
                                                            data-style="btn-outline btn-primary">
                                                        @foreach (Helpers::protocolList() as $protocol)
                                                            <option value="{{ $protocol->name }}" @if (!isset($node) && $protocol->is_default) selected @endif>
                                                                {{ $protocol->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-md-3 col-form-label" for="protocol_param"> {{ trans('model.node.protocol_param') }}
                                                    </label>
                                                    <input class="form-control col-md-4" id="protocol_param" name="protocol_param" type="text">
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-md-3 col-form-label" for="obfs">{{ trans('model.node.obfs') }}</label>
                                                    <select class="col-md-5 form-control" id="obfs" name="obfs" data-plugin="selectpicker"
                                                            data-style="btn-outline btn-primary">
                                                        @foreach (Helpers::obfsList() as $obfs)
                                                            <option value="{{ $obfs->name }}" @if (!isset($node) && $obfs->is_default) selected @endif>
                                                                {{ $obfs->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group row obfs_param">
                                                    <label class="col-md-3 col-form-label" for="obfs_param"> {{ trans('model.node.obfs_param') }} </label>
                                                    <textarea class="form-control col-md-8" id="obfs_param" name="obfs_param" rows="5" placeholder="{!! trans('admin.node.info.obfs_param_hint') !!}"></textarea>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-md-3 col-form-label">{{ trans('admin.node.proxy_info') }}</label>
                                                    <div class="text-help col-md-9">
                                                        {!! trans('admin.node.proxy_info_hint') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <hr />
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="single">{{ trans('model.node.single') }}</label>
                                                <div class="col-md-9">
                                                    <input id="single" name="single" data-plugin="switchery" type="checkbox"
                                                           onchange="switchSetting('single')">
                                                </div>
                                                <div class="text-help offset-md-3">
                                                    {!! trans('admin.node.info.additional_ports_hint') !!}
                                                </div>
                                            </div>
                                            <div class="single-setting">
                                                <div class="form-group row">
                                                    <label class="col-md-3 col-form-label" for="single_port">{{ trans('model.node.service_port') }}</label>
                                                    <input class="form-control col-md-4" id="single_port" name="port" type="number" value="443"
                                                           hidden />
                                                    <span class="text-help offset-md-3"> {!! trans('admin.node.info.single_hint') !!}</span>
                                                </div>
                                                <div class="form-group row ssr-setting">
                                                    <label class="col-md-3 col-form-label" for="passwd">{{ trans('model.node.single_passwd') }}</label>
                                                    <input class="form-control col-md-4" id="passwd" name="passwd" type="text"
                                                           placeholder="{{ ucfirst(trans('validation.attributes.password')) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- V2ray 设置部分 -->
                                        <div class="v2ray-setting">
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_alter_id">{{ trans('model.node.v2_alter_id') }}</label>
                                                <input class="form-control col-md-4" id="v2_alter_id" name="v2_alter_id" type="text" value="16" />
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_port">{{ trans('model.node.service_port') }}</label>
                                                <input class="form-control col-md-4" id="v2_port" name="port" type="number" value="10053" hidden />
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_method">{{ trans('model.node.method') }}</label>
                                                <select class="col-md-5 form-control" id="v2_method" name="v2_method" data-plugin="selectpicker"
                                                        data-style="btn-outline btn-primary">
                                                    <option value="none">none</option>
                                                    <option value="auto">auto</option>
                                                    <option value="aes-128-cfb">aes-128-cfb</option>
                                                    <option value="aes-128-gcm">aes-128-gcm</option>
                                                    <option value="chacha20-poly1305">chacha20-poly1305</option>
                                                </select>
                                                <div class="text-help offset-md-3"> {{ trans('admin.node.info.v2_method_hint') }}</div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_net">{{ trans('model.node.v2_net') }}</label>
                                                <select class="col-md-5 form-control" id="v2_net" name="v2_net" data-plugin="selectpicker"
                                                        data-style="btn-outline btn-primary">
                                                    <option value="tcp">TCP</option>
                                                    <option value="http">HTTP/2</option>
                                                    <option value="ws">WebSocket</option>
                                                    <option value="kcp">mKCP</option>
                                                    <option value="domainsocket">DomainSocket</option>
                                                    <option value="quic">QUIC</option>
                                                </select>
                                                <div class="text-help offset-md-3"> {{ trans('admin.node.info.v2_net_hint') }}</div>
                                            </div>
                                            <div class="form-group row v2_type">
                                                <label class="col-md-3 col-form-label" for="v2_type">{{ trans('model.node.v2_cover') }}</label>
                                                <select class="col-md-5 form-control" id="v2_type" name="v2_type" data-plugin="selectpicker"
                                                        data-style="btn-outline btn-primary">
                                                    <option value="none">{{ trans('admin.node.info.v2_cover.none') }}</option>
                                                    <option value="http">{{ trans('admin.node.info.v2_cover.http') }}</option>
                                                    <optgroup id="type_option" label="">
                                                        <option value="srtp">{{ trans('admin.node.info.v2_cover.srtp') }}</option>
                                                        <option value="utp">{{ trans('admin.node.info.v2_cover.utp') }}</option>
                                                        <option value="wechat-video">{{ trans('admin.node.info.v2_cover.wechat') }}</option>
                                                        <option value="dtls">{{ trans('admin.node.info.v2_cover.dtls') }}</option>
                                                        <option value="wireguard">{{ trans('admin.node.info.v2_cover.wireguard') }}</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="form-group row v2_host">
                                                <label class="col-md-3 col-form-label" for="v2_host">{{ trans('model.node.v2_host') }}</label>
                                                <div class="col-md-4 pl-0">
                                                    <input class="form-control" id="v2_host" name="v2_host" type="text">
                                                </div>
                                                <div class="text-help offset-md-3">
                                                    {{ trans('admin.node.info.v2_host_hint') }}
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_path">{{ trans('model.node.v2_path') }}</label>
                                                <input class="form-control col-md-4" id="v2_path" name="v2_path" type="text">
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_sni">{{ trans('model.node.v2_sni') }}</label>
                                                <input class="form-control col-md-4" id="v2_sni" name="v2_sni" type="text">
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="v2_tls">{{ trans('model.node.v2_tls') }}</label>
                                                <div class="col-md-9">
                                                    <input id="v2_tls" name="v2_tls" data-plugin="switchery" type="checkbox"
                                                           onchange="switchSetting('v2_tls')">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="tls_provider">{{ trans('model.node.v2_tls_provider') }}</label>
                                                <input class="form-control col-md-9" id="tls_provider" name="tls_provider" type="text" />
                                                <div class="text-help offset-md-3"> {{ trans('admin.node.info.v2_tls_provider_hint') }}
                                                    <a href="https://proxypanel.gitbook.io/wiki/webapi/webapi-basic-setting#vnet-v2-ray-hou-duan"
                                                       target="_blank">VNET-V2Ray</a>、
                                                    <a href="https://proxypanel.gitbook.io/wiki/webapi/webapi-basic-setting#v-2-ray-poseidon-hou-duan"
                                                       target="_blank">V2Ray-Poseidon</a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Trojan 设置部分 -->
                                        <div class="trojan-setting">
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label" for="trojan_port">{{ trans('model.node.service_port') }}</label>
                                                <input class="form-control col-md-4" id="trojan_port" name="port" type="number" value="443" hidden />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relay-config">
                                        <div class="form-group row">
                                            <label class="col-md-3 col-form-label" for="relay_port">{{ trans('model.node.relay_port') }}</label>
                                            <input class="form-control col-md-4" id="relay_port" name="port" type="number" value="443" hidden />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="is_udp">{{ trans('model.node.udp') }}</label>
                                        <div class="col-md-9">
                                            <input id="is_udp" name="is_udp" data-plugin="switchery" type="checkbox">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="status">{{ trans('common.status.attribute') }}</label>
                                        <div class="col-md-9">
                                            <input id="status" name="status" data-plugin="switchery" type="checkbox">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 form-actions">
                                <div class="float-right">
                                    <a class="btn btn-danger" href="{{ route('admin.node.index') }}">{{ trans('common.back') }}</a>
                                    <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/vendor/switchery/switchery.min.js"></script>
    <script src="/assets/global/js/Plugin/switchery.js"></script>
    <script>
        $('[name="next_renewal_date"]').datepicker({
            format: 'yyyy-mm-dd',
        });

        const string = "{{ strtolower(Str::random()) }}";

        $(document).ready(function() {
            $('.single-setting').hide();
            $('input:radio[name="type"]').on('change', updateServiceType);
            $('#obfs').on('changed.bs.select', toggleObfsParam);
            $('#relay_node_id').on('changed.bs.select', toggleRelayConfig);
            $('#v2_net').on('changed.bs.select', updateV2RaySettings);

            $('#nodeForm').on('submit', function(event) {
                event.preventDefault();
                formSubmit(event);
            });

            $('[name="next_renewal_date"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            toggleObfsParam();
            toggleRelayConfig();

            @isset($node)
                const nodeData = @json($node);
                const {
                    type,
                    labels,
                    relay_node_id,
                    port,
                    profile,
                    tls_provider,
                    details
                } = nodeData;

                ['is_ddns', 'is_udp', 'status'].forEach(prop => nodeData[prop] && $(`#${prop}`).click());
                ['is_display', 'detection_type', 'type'].forEach(prop => $(`input[name="${prop}"][value="${nodeData[prop]}"]`).click());

                ['name', 'server', 'ip', 'ipv6', 'push_port', 'traffic_rate', 'speed_limit', 'client_limit', 'description', 'sort']
                .forEach(prop => $(`#${prop}`).val(nodeData[prop]));

                ['level', 'rule_group_id', 'country_code', 'relay_node_id'].forEach(prop => $(`#${prop}`).selectpicker('val', nodeData[prop]));

                $('#labels').selectpicker('val', labels.map(label => label.id));
                if (details?.next_renewal_date) {
                    $('#next_renewal_date').datepicker('update', details.next_renewal_date);
                }
                if (details?.subscription_term) {
                    setSubscriptionTerm(details.subscription_term)
                }
                if (details?.renewal_cost) {
                    $('#renewal_cost').val(details.renewal_cost);
                }

                if (relay_node_id) {
                    $('#relay_port').val(port);
                } else {
                    const typeHandlers = {
                        0: () => $('#method').selectpicker('val', profile?.method || null),
                        1: setSSRValues,
                        2: setV2RayValues,
                        3: () => $('#trojan_port').val(port),
                        4: setSSRValues
                    };

                    typeHandlers[type] && typeHandlers[type]();
                    $('input[name="port"]').val(port);
                }

                function setSSRValues() {
                    ['protocol', 'obfs'].forEach(prop => $(`#${prop}`).selectpicker('val', profile[prop] || null));
                    ['protocol_param', 'obfs_param'].forEach(prop => $(`#${prop}`).val(profile[prop] || null));
                    if (profile.passwd && port) {
                        $('#single').click();
                        $('#passwd').val(profile.passwd);
                    }
                }

                function setV2RayValues() {
                    ['v2_alter_id', 'v2_host', 'v2_sni', 'v2_path'].forEach(prop => $(`#${prop}`).val(profile[prop] || null));
                    ['v2_net', 'v2_type'].forEach(prop => $(`#${prop}`).selectpicker('val', profile[prop] || null));
                    $('#v2_method').selectpicker('val', profile['method'] || null);

                    $('#v2_port').val(port);
                    profile.v2_tls && $('#v2_tls').click();
                    $('#tls_provider').val(tls_provider);
                }
            @else
                switchSetting('single');
                switchSetting('is_ddns');
                $('input[name="type"][value="0"]').click();
                $('#status, #is_udp').click();
                $('#v2_path').val('/' + string);
            @endisset

            function setSubscriptionTerm(term) {
                const [value, unit] = term.split(' ');

                $('#subscription_term_value').val(value || '');
                $('#subscription_term_unit').selectpicker('val', unit || 'day'); // 默认选择 day
            }
        });

        function formSubmit(event) {
            event.preventDefault(); // 阻止表单的默认提交行为
            const $form = $(event.target); // 获取触发事件的表单

            // 获取所有非 hidden 的表单数据
            const data = Object.fromEntries(
                $form.find('input:not([hidden]), select, textarea')
                .serializeArray()
                .map(item => [item.name, item.value])
            );

            // 拼接 subscription_term
            const termValue = $('#subscription_term_value').val();
            const termUnit = $('#subscription_term_unit').val();
            data['subscription_term'] = termValue ? `${termValue} ${termUnit}` : null;

            // 将序列化的表单数据转换为 JSON 对象
            $form.find('input[type="checkbox"]').each(function() {
                data[this.name] = this.checked ? 1 : 0;
            });

            // 处理多选 select
            $form.find('select[multiple]').each(function() {
                data[this.name] = $(this).val();
            });

            $.ajax({
                url: '{{ isset($node) ? route('admin.node.update', $node) : route('admin.node.store') }}',
                method: '{{ isset($node) ? 'PUT' : 'POST' }}',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({
                            title: ret.message,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false,
                        }).then(() => window.location.href =
                            '{{ route('admin.node.index') . (Request::getQueryString() ? '?' . Request::getQueryString() : '') }}');
                    } else {
                        swal.fire({
                            title: '{{ trans('common.error') }}',
                            text: ret.message,
                            icon: 'error'
                        });
                    }
                },
                error: function(data) {
                    const errors = data.responseJSON?.errors;
                    if (errors) {
                        const errorList = Object.values(errors).map(error => `<li>${error}</li>`).join('');
                        swal.fire({
                            title: '{{ trans('admin.hint') }}',
                            html: `<ul>${errorList}</ul>`,
                            icon: 'error',
                            confirmButtonText: '{{ trans('common.confirm') }}',
                        });
                    }
                },
            });
        }

        function switchSetting(id) {
            const check = document.getElementById(id).checked;
            if (id === 'single') {
                $('.single-setting').toggle(check);
                $('#single_port').attr({
                    'hidden': !check,
                    'required': check
                });
                if (!check) $('#passwd').val('');
            } else if (id === 'is_ddns') {
                $('#ip, #ipv6').attr('readonly', check).val('');
                $('#server').attr('required', check);
            }
        }

        // 设置服务类型
        function updateServiceType() {
            const type = parseInt($(this).val());
            $('.ss-setting, .ssr-setting, .v2ray-setting, .trojan-setting').hide();
            $('#v2_port').removeAttr('required').attr('hidden', true);
            $('#trojan_port').removeAttr('required');
            switch (type) {
                case 0:
                    $('.ss-setting').show();
                    break;
                case 2:
                    $('.v2ray-setting').show();
                    $('#v2_port').removeAttr('hidden').prop('required', true);
                    $('#v2_net').selectpicker('val', 'tcp');
                    break;
                case 3:
                    $('.trojan-setting').show();
                    $('#trojan_port').removeAttr('hidden').prop('required', true);
                    break;
                case 1:
                case 4:
                    $('.ss-setting, .ssr-setting').show();
                    break;
            }
        }

        function toggleObfsParam() {
            const $obfsParam = $('.obfs_param');
            const isPlain = $('#obfs').val() === 'plain';
            $obfsParam.toggle(!isPlain);
            if (isPlain) $('#obfs_param').val('');
        }

        function toggleRelayConfig() {
            const hasRelay = $('#relay_node_id').val() !== '';
            $('.relay-config').toggle(hasRelay);
            $('.proxy-config').toggle(!hasRelay);
            $('#relay_port').attr({
                'hidden': !hasRelay,
                'required': hasRelay
            });
        }

        // 设置V2Ray详细设置
        function updateV2RaySettings() {
            const net = $(this).val();
            const $type = $('.v2_type');
            const $typeOption = $('#type_option');
            const $host = $('.v2_host');
            const $path = $('#v2_path');
            $type.show();
            $host.show();
            if (!$path.val()) {
                $path.val('/' + string);
            }
            switch (net) {
                case 'ws':
                case 'http':
                    $type.hide();
                    break;
                case 'domainsocket':
                    $type.hide();
                    $host.hide();
                    break;
                case 'quic':
                    $typeOption.attr('disabled', false);
                    if (!$path.val()) {
                        $path.val(string);
                    }
                    break;
                case 'kcp':
                case 'tcp':
                default:
                    $typeOption.attr('disabled', true);
                    break;
            }
            $('#v2_type').selectpicker('refresh');
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
                title: '[节点 user-config.json 配置示例]',
                width: '36em',
                html: `
                    <div class="text-left">
                        <ol>
                            <li>请勿直接复制黏贴以下配置，SSR(R)会报错的</li>
                            <li>确保服务器时间为CST</li>
                        </ol>
                        <pre class="bg-grey-800 text-white">${JSON.stringify(jsonConfig, null, 2)}</pre>
                    </div>
                `,
                icon: 'info',
            })
        };

        // 模式提示
        window.showPortsOnlyConfig = function() {
            swal.fire({
                title: '[节点 user-config.json 配置示例]',
                width: '36em',
                html: `
                  <ul class="bg-grey-800 text-white text-left">
                      <li>严格模式："additional_ports_only": "true"</li>
                      <li>兼容模式："additional_ports_only": "false"</li>
                  </ul>
                `,
                icon: 'info',
            });
        }
    </script>
@endsection
