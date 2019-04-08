@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="note note-info">
                            <p><strong>注意：</strong> 添加节点后自动生成的<code>ID</code>，即为该节点部署ShadowsocksR Python版后端时<code>usermysql.json</code>中的<code>node_id</code>的值，同时也是部署V2Ray后端时的<code>nodeId</code>的值；</p>
                            <p>V2Ray Go版节点<a href="https://github.com/ssrpanel/SSRPanel/wiki/V2Ray%E5%AE%8C%E6%95%B4%E9%85%8D%E7%BD%AE%E7%A4%BA%E4%BE%8B%EF%BC%88Go%E7%89%88%EF%BC%89" target="_blank">部署教程</a>；
                                Shadowsocks Go版节点<a href="https://github.com/ssrpanel/SSRPanel/wiki/SS-Go%E7%89%88%E8%8A%82%E7%82%B9%E9%83%A8%E7%BD%B2" target="_blank">部署教程</a>；
                                更改服务器的SSH端口<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E6%9C%8D%E5%8A%A1%E5%99%A8%E7%A6%81%E6%AD%A2PING%E3%80%81%E6%94%B9SSH%E7%AB%AF%E5%8F%A3%E5%8F%B7" target="_blank">教程</a>；</p>
                            <p>中转节点需要自行配置服务器端口转发，TCP阻断检测无效，务必填写域名； </p>
                            <p>NAT节点需要<a href="https://github.com/ssrpanel/SSRPanel/wiki/NAT-VPS%E9%85%8D%E7%BD%AE%E6%95%99%E7%A8%8B" target="_blank">配置DDNS</a>，TCP阻断检测无效，务必填写域名； </p>
                        </div>
                    </div>
                </div>
                <div class="portlet light bordered">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="{{url('admin/addNode')}}" method="post" class="form-horizontal" onsubmit="return do_submit();">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <span class="caption-subject font-dark bold uppercase">基础信息</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="form-group">
                                                    <label for="is_transit" class="col-md-3 control-label">中转</label>
                                                    <div class="col-md-8">
                                                        <div class="mt-radio-inline">
                                                            <label class="mt-radio">
                                                                <input type="radio" name="is_transit" value="1"> 是
                                                                <span></span>
                                                            </label>
                                                            <label class="mt-radio">
                                                                <input type="radio" name="is_transit" value="0" checked> 否
                                                                <span></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="is_nat" class="col-md-3 control-label">NAT</label>
                                                    <div class="col-md-8">
                                                        <div class="mt-radio-inline">
                                                            <label class="mt-radio">
                                                                <input type="radio" name="is_nat" value="1"> 是
                                                                <span></span>
                                                            </label>
                                                            <label class="mt-radio">
                                                                <input type="radio" name="is_nat" value="0" checked> 否
                                                                <span></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name" class="col-md-3 control-label"> 节点名称 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="name" id="name" placeholder="" autofocus required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="server" class="col-md-3 control-label"> 域名 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="server" id="server" placeholder="服务器域名地址，填则优先取域名地址">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ip" class="col-md-3 control-label"> IPv4地址 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="ip" id="ip" placeholder="服务器IPv4地址" required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ipv6" class="col-md-3 control-label"> IPv6地址 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="ipv6" id="ipv6" placeholder="服务器IPv6地址，填写则用户可见，域名无效">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ssh_port" class="col-md-3 control-label"> SSH端口 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="ssh_port" value="22" id="ssh_port" placeholder="服务器SSH端口" required>
                                                        <span class="help-block">请务必正确填写此值，否则TCP阻断检测可能误报</span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="traffic_rate" class="col-md-3 control-label"> 流量比例 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="traffic_rate" value="1.0" id="traffic_rate" placeholder="" required>
                                                        <span class="help-block"> 举例：0.1用100M结算10M，5用100M结算500M </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="labels" class="col-md-3 control-label">标签</label>
                                                    <div class="col-md-8">
                                                        <select id="labels" class="form-control select2-multiple" name="labels[]" multiple>
                                                            @foreach($label_list as $label)
                                                                <option value="{{$label->id}}">{{$label->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="group_id" class="col-md-3 control-label"> 所属分组 </label>
                                                    <div class="col-md-8">
                                                        <select class="form-control" name="group_id" id="group_id">
                                                            <option value="0">请选择</option>
                                                            @if(!$group_list->isEmpty())
                                                                @foreach($group_list as $group)
                                                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="country_code" class="col-md-3 control-label"> 国家/地区 </label>
                                                    <div class="col-md-8">
                                                        <select class="form-control" name="country_code" id="country_code">
                                                            <option value="">请选择</option>
                                                            @if(!$country_list->isEmpty())
                                                                @foreach($country_list as $country)
                                                                    <option value="{{$country->country_code}}">{{$country->country_code}} - {{$country->country_name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="desc" class="col-md-3 control-label"> 描述 </label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="desc" id="desc" placeholder="简单描述">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sort" class="col-md-3 control-label">排序</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="sort" value="1" id="sort" placeholder="">
                                                        <span class="help-block"> 值越大排越前 </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status" class="col-md-3 control-label">状态</label>
                                                    <div class="col-md-8">
                                                        <div class="mt-radio-inline">
                                                            <label class="mt-radio">
                                                                <input type="radio" name="status" value="1" checked> 正常
                                                                <span></span>
                                                            </label>
                                                            <label class="mt-radio">
                                                                <input type="radio" name="status" value="0"> 维护
                                                                <span></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--
                                                <div class="form-group">
                                                    <label for="bandwidth" class="col-md-3 control-label">出口带宽</label>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="bandwidth" value="1000" id="bandwidth" placeholder="" required>
                                                            <span class="input-group-addon">M</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="traffic" class="col-md-3 control-label">每月可用流量</label>
                                                    <div class="col-md-8">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control right" name="traffic" value="1000" id="traffic" placeholder="" required>
                                                            <span class="input-group-addon">G</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="monitor_url" class="col-md-3 control-label">监控地址</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control right" name="monitor_url" value="" id="monitor_url" placeholder="节点实时监控地址">
                                                        <span class="help-block"> 例如：http://us1.ssrpanel.com/api/monitor </span>
                                                    </div>
                                                </div>
                                                -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <span class="caption-subject font-dark bold">扩展信息</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="form-group">
                                                    <label for="service" class="col-md-3 control-label">类型</label>
                                                    <div class="col-md-8">
                                                        <div class="mt-radio-inline">
                                                            <label class="mt-radio">
                                                                <input type="radio" name="service" value="1" checked> Shadowsocks(R)
                                                                <span></span>
                                                            </label>
                                                            <label class="mt-radio">
                                                                <input type="radio" name="service" value="2"> V2Ray
                                                                <span></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr />
                                                <!-- SS/SSR 设置部分 -->
                                                <div class="ssr-setting">
                                                    <div class="form-group">
                                                        <label for="method" class="col-md-3 control-label">加密方式</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="method" id="method">
                                                                @foreach ($method_list as $method)
                                                                    <option value="{{$method->name}}" @if($method->is_default) selected @endif>{{$method->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="protocol" class="col-md-3 control-label">协议</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="protocol" id="protocol">
                                                                @foreach ($protocol_list as $protocol)
                                                                    <option value="{{$protocol->name}}" @if($protocol->is_default) selected @endif>{{$protocol->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="protocol_param" class="col-md-3 control-label"> 协议参数 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="protocol_param" id="protocol_param" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="obfs" class="col-md-3 control-label">混淆</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="obfs" id="obfs">
                                                                @foreach ($obfs_list as $obfs)
                                                                    <option value="{{$obfs->name}}" @if($obfs->is_default) selected @endif>{{$obfs->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="obfs_param" class="col-md-3 control-label"> 混淆参数 </label>
                                                        <div class="col-md-8">
                                                            <textarea class="form-control" rows="5" name="obfs_param" id="obfs_param"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="compatible" class="col-md-3 control-label">兼容SS</label>
                                                        <div class="col-md-8">
                                                            <div class="mt-radio-inline">
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="compatible" value="1"> 是
                                                                    <span></span>
                                                                </label>
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="compatible" value="0" checked> 否
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                            <span class="help-block"> 如果兼容请在服务端配置协议和混淆时加上<span style="color:red">_compatible</span> </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_subscribe" class="col-md-3 control-label">订阅</label>
                                                        <div class="col-md-8">
                                                            <div class="mt-radio-inline">
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="is_subscribe" value="1" checked> 允许
                                                                    <span></span>
                                                                </label>
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="is_subscribe" value="0"> 不允许
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="is_tcp_check" class="col-md-3 control-label">TCP阻断检测</label>
                                                        <div class="col-md-8">
                                                            <div class="mt-radio-inline">
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="is_tcp_check" value="1" checked> 开启
                                                                    <span></span>
                                                                </label>
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="is_tcp_check" value="0"> 关闭
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                            <span class="help-block"> 每30~60分钟随机进行TCP阻断检测 </span>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                    <div class="form-group">
                                                        <label for="single" class="col-md-3 control-label">单端口</label>
                                                        <div class="col-md-8">
                                                            <div class="mt-radio-inline">
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="single" value="0" checked> 关闭
                                                                    <span></span>
                                                                </label>
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="single" value="1"> 启用
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                            <span class="help-block"> 如果启用请配置服务端的<span style="color:red"> <a href="javascript:showTnc();">additional_ports</a> </span>信息 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_force" class="col-md-3 control-label">[单] 模式</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="single_force" id="single_force">
                                                                <option value="0" selected>兼容模式</option>
                                                                <option value="1">严格模式</option>
                                                            </select>
                                                            <span class="help-block"> 严格模式：用户的端口无法连接，只能通过以下指定的端口进行连接（<a href="javascript:showPortsOnlyConfig();">如何配置</a>）</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_port" class="col-md-3 control-label">[单] 端口</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="single_port" value="" id="single_port" placeholder="443">
                                                            <span class="help-block"> 推荐80或443，服务端需要配置 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_passwd" class="col-md-3 control-label">[单] 密码</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="single_passwd" value="" id="single_passwd" placeholder="password">
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_method" class="col-md-3 control-label">[单] 加密方式</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="single_method" id="single_method">
                                                                @foreach ($method_list as $method)
                                                                    <option value="{{$method->name}}" @if($method->is_default) selected @endif>{{$method->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_protocol" class="col-md-3 control-label">[单] 协议</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="single_protocol" id="single_protocol">
                                                                <option value="origin">origin</option>
                                                                <option value="verify_deflate">verify_deflate</option>
                                                                <option value="auth_sha1_v4">auth_sha1_v4</option>
                                                                <option value="auth_aes128_md5">auth_aes128_md5</option>
                                                                <option value="auth_aes128_sha1">auth_aes128_sha1</option>
                                                                <option value="auth_chain_a" selected>auth_chain_a</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_obfs" class="col-md-3 control-label">[单] 混淆</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="single_obfs" id="single_obfs">
                                                                <option value="plain">plain</option>
                                                                <option value="http_simple">http_simple</option>
                                                                <option value="random_head">random_head</option>
                                                                <option value="tls1.2_ticket_auth" selected>tls1.2_ticket_auth</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- V2ray 设置部分 -->
                                                <div class="v2ray-setting hidden">
                                                    <div class="form-group">
                                                        <label for="v2_alter_id" class="col-md-3 control-label">额外ID</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_alter_id" value="16" id="v2_alter_id" placeholder="16">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_port" class="col-md-3 control-label">端口</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_port" value="10087" id="v2_port" placeholder="10087">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_method" class="col-md-3 control-label">加密方式</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="v2_method" id="v2_method">
                                                                <option value="none">none</option>
                                                                <option value="aes-128-cfb">aes-128-cfb</option>
                                                                <option value="aes-128-gcm" selected>aes-128-gcm</option>
                                                                <option value="chacha20-poly1305">chacha20-poly1305</option>
                                                            </select>
                                                            <span class="help-block"> 使用WebSocket传输协议时不要使用none </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_net" class="col-md-3 control-label">传输协议</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="v2_net" id="v2_net">
                                                                <option value="tcp" selected>TCP</option>
                                                                <option value="kcp">mKCP（kcp）</option>
                                                                <option value="ws">WebSocket（ws）</option>
                                                                <option value="h2">HTTP/2（h2）</option>
                                                            </select>
                                                            <span class="help-block"> 使用WebSocket传输协议时请启用TLS </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_type" class="col-md-3 control-label">伪装类型</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="v2_type" id="v2_type">
                                                                <option value="none" selected>无伪装</option>
                                                                <option value="http">HTTP数据流</option>
                                                                <option value="srtp">视频通话数据 (SRTP)</option>
                                                                <option value="utp">BT下载数据 (uTP)</option>
                                                                <option value="wechat-video">微信视频通话</option>
                                                                <option value="dtls">DTLS1.2数据包</option>
                                                                <option value="wireguard">WireGuard数据包</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_host" class="col-md-3 control-label">伪装域名</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_host" id="v2_host">
                                                            <span class="help-block"> 伪装类型为http时多个伪装域名逗号隔开，使用WebSocket传输协议时只允许单个 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_path" class="col-md-3 control-label">ws/h2路径</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_path" id="v2_path">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_tls" class="col-md-3 control-label">TLS</label>
                                                        <div class="col-md-8">
                                                            <div class="mt-radio-inline">
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="v2_tls" value="1"> 是
                                                                    <span></span>
                                                                </label>
                                                                <label class="mt-radio">
                                                                    <input type="radio" name="v2_tls" value="0" checked> 否
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_insider_port" class="col-md-3 control-label">内部端口</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_insider_port" value="10550" id="v2_insider_port" placeholder="10550">
                                                            <span class="help-block"> 内部监听，当端口为0时启用，仅支持<a href="https://github.com/rico93/pay-v2ray-sspanel-v3-mod_Uim-plugin/" target="_blank">rico93版</a> </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="v2_outsider_port" class="col-md-3 control-label">内部端口</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="v2_outsider_port" value="443" id="v2_outsider_port" placeholder="443">
                                                            <span class="help-block"> 外部覆盖，当端口为0时启用，仅支持<a href="https://github.com/rico93/pay-v2ray-sspanel-v3-mod_Uim-plugin/" target="_blank">rico93版</a> </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn green">提 交</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 用户标签选择器
        $('#labels').select2({
            theme: 'bootstrap',
            placeholder: '设置后则可见相同标签的节点',
            allowClear: true,
            width:'100%'
        });

        // ajax同步提交
        function do_submit() {
            var name = $('#name').val();
            var labels = $("#labels").val();
            var group_id = $("#group_id option:selected").val();
            var country_code = $("#country_code option:selected").val();
            var server = $('#server').val();
            var ip = $('#ip').val();
            var ipv6 = $('#ipv6').val();
            var desc = $('#desc').val();
            var method = $('#method').val();
            var traffic_rate = $('#traffic_rate').val();
            var protocol = $('#protocol').val();
            var protocol_param = $('#protocol_param').val();
            var obfs = $('#obfs').val();
            var obfs_param = $('#obfs_param').val();
            var bandwidth = $('#bandwidth').val();
            var traffic = $('#traffic').val();
            var monitor_url = $('#monitor_url').val();
            var is_subscribe = $("input:radio[name='is_subscribe']:checked").val();
            var is_nat = $("input:radio[name='is_nat']:checked").val();
            var is_transit = $("input:radio[name='is_transit']:checked").val();
            var ssh_port = $('#ssh_port').val();
            var compatible = $("input:radio[name='compatible']:checked").val();
            var single = $("input:radio[name='single']:checked").val();
            var single_force = $('#single_force').val();
            var single_port = $('#single_port').val();
            var single_passwd = $('#single_passwd').val();
            var single_method = $('#single_method').val();
            var single_protocol = $('#single_protocol').val();
            var single_obfs = $('#single_obfs').val();
            var sort = $('#sort').val();
            var status = $("input:radio[name='status']:checked").val();
            var is_tcp_check = $("input:radio[name='is_tcp_check']:checked").val();

            var service = $("input:radio[name='service']:checked").val();
            var v2_alter_id = $('#v2_alter_id').val();
            var v2_port = $('#v2_port').val();
            var v2_method = $("#v2_method option:selected").val();
            var v2_net = $('#v2_net').val();
            var v2_type = $('#v2_type').val();
            var v2_host = $('#v2_host').val();
            var v2_path = $('#v2_path').val();
            var v2_tls = $("input:radio[name='v2_tls']:checked").val();
            var v2_insider_port = $('#v2_insider_port').val();
            var v2_outsider_port = $('#v2_outsider_port').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addNode')}}",
                async: false,
                data: {
                    _token: '{{csrf_token()}}',
                    name: name,
                    labels: labels,
                    group_id: group_id,
                    country_code: country_code,
                    server: server,
                    ip: ip,
                    ipv6: ipv6,
                    desc: desc,
                    method: method,
                    traffic_rate: traffic_rate,
                    protocol: protocol,
                    protocol_param: protocol_param,
                    obfs: obfs,
                    obfs_param: obfs_param,
                    bandwidth: bandwidth,
                    traffic: traffic,
                    monitor_url: monitor_url,
                    is_subscribe: is_subscribe,
                    is_nat: is_nat,
                    is_transit: is_transit,
                    ssh_port: ssh_port,
                    compatible: compatible,
                    single: single,
                    single_force: single_force,
                    single_port: single_port,
                    single_passwd: single_passwd,
                    single_method: single_method,
                    single_protocol: single_protocol,
                    single_obfs: single_obfs,
                    sort: sort,
                    status: status,
                    is_tcp_check: is_tcp_check,
                    type: service,
                    v2_alter_id: v2_alter_id,
                    v2_port: v2_port,
                    v2_method: v2_method,
                    v2_net: v2_net,
                    v2_type: v2_type,
                    v2_host: v2_host,
                    v2_path: v2_path,
                    v2_tls: v2_tls,
                    v2_insider_port: v2_insider_port,
                    v2_outsider_port: v2_outsider_port
                },
                dataType: 'json',
                success: function (ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.href = '{{url('admin/nodeList')}}';
                        }
                    });
                }
            });

            return false;
        }

        // 设置单端口多用户
        $("input:radio[name='single']").on('change', function() {
            var single = parseInt($(this).val());

            if (single) {
                $(".single-setting").removeClass('hidden');
            } else {
                $(".single-setting").removeClass('hidden');
                $(".single-setting").addClass('hidden');
            }
        });

        // 设置服务类型
        $("input:radio[name='service']").on('change', function() {
            var service = parseInt($(this).val());

            if (service === 1) {
                $(".ssr-setting").removeClass('hidden');
                $(".v2ray-setting").addClass('hidden');
            } else {
                $(".ssr-setting").addClass('hidden');
                $(".v2ray-setting").removeClass('hidden');
            }
        });

        // 设置是否为NAT
        $("input:radio[name='is_nat']").on('change', function() {
            var is_nat = parseInt($(this).val());

            if (is_nat === 1) {
                $("#ip").val("1.1.1.1").attr("readonly", "readonly");
                $("#server").attr("required", "required");
            } else {
                $("#ip").val("").removeAttr("readonly");
                $("#server").removeAttr("required");
            }
        });

        // 服务条款
        function showTnc() {
            var content = '1.请勿直接复制黏贴以下配置，SSR(R)会报错的；'
                + '<br>2.确保服务器时间为CST；'
                + '<br>3.具体请看<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91" target="_blank">WIKI</a>；'
                + '<br>'
                + '<br>additional_ports" : {'
                + '<br>&ensp;&ensp;&ensp;&ensp;"80": {'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"passwd": "password",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"method": "aes-128-ctr",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"protocol": "auth_aes128_md5",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"protocol_param": "#",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"obfs": "tls1.2_ticket_auth",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"obfs_param": ""'
                + '<br>&ensp;&ensp;&ensp;&ensp;},'
                + '<br>&ensp;&ensp;&ensp;&ensp;"443": {'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"passwd": "password",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"method": "aes-128-ctr",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"protocol": "auth_aes128_sha1",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"protocol_param": "#",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"obfs": "tls1.2_ticket_auth",'
                + '<br>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;"obfs_param": ""'
                + '<br>&ensp;&ensp;&ensp;&ensp;}'
                + '<br>},';

            layer.open({
                type: 1
                ,title: '[节点 user-config.json 配置示例]'
                ,closeBtn: false
                ,area: '400px;'
                ,shade: 0.8
                ,id: 'tnc' //设定一个id，防止重复弹出
                ,resize: false
                ,btn: ['确定']
                ,btnAlign: 'c'
                ,moveType: 1 //拖拽模式，0或者1
                ,content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">' + content + '</div>'
                ,success: function(layero){
                    //
                }
            });
        }

        // 模式提示
        function showPortsOnlyConfig() {
            var content = '严格模式：'
                + '<br>'
                + '"additional_ports_only": "true"'
                + '<br><br>'
                + '兼容模式：'
                + '<br>'
                + '"additional_ports_only": "false"';

            layer.open({
                type: 1
                ,title: '[节点 user-config.json 配置示例]'
                ,closeBtn: false
                ,area: '400px;'
                ,shade: 0.8
                ,id: 'po-cfg' //设定一个id，防止重复弹出
                ,resize: false
                ,btn: ['确定']
                ,btnAlign: 'c'
                ,moveType: 1 //拖拽模式，0或者1
                ,content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">' + content + '</div>'
                ,success: function(layero){
                    //
                }
            });
        }
    </script>
@endsection