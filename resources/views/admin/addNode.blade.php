@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="tab-pane active">
                    <div class="portlet light bordered">
                        <div class="portlet-body form">
                            <!-- BEGIN FORM-->
                            <form action="{{url('admin/addNode')}}" method="post" class="form-horizontal" onsubmit="return do_submit();">
                                <div class="form-body">
                                    <div class="alert alert-danger alert-dismissable">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                        <strong>注意：</strong> 添加节点后自动生成的<code>ID</code>，即为该节点后端部署SSR时<code>usermysql.json</code>的<code>node_id</code>的值
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- BEGIN SAMPLE FORM PORTLET-->
                                            <div class="portlet light bordered">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <span class="caption-subject font-dark bold uppercase">基础信息</span>
                                                    </div>
                                                </div>
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="name" class="col-md-3 control-label"> 节点名称 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="name" id="name" placeholder="" autofocus required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="server" class="col-md-3 control-label"> 域名地址 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="server" id="server" placeholder="服务器域名地址，填写则优先取域名地址">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="ip" class="col-md-3 control-label"> IP地址 </label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="ip" id="ip" placeholder="服务器IP地址" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status" class="col-md-3 control-label">标签</label>
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
                                                            <span class="help-block">订阅时分组展示</span>
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
                                                            <select class="form-control" name="status" id="status">
                                                                <option value="1" selected>正常</option>
                                                                <option value="0">维护</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                    <div class="form-group">
                                                        <label for="single" class="col-md-3 control-label">单端口</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="single" id="single">
                                                                <option value="0" selected>关闭</option>
                                                                <option value="1">启用</option>
                                                            </select>
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
                                                            <span class="help-block"> 严格模式：用户的端口无法连接，只能通过以下指定的端口号进行连接（<a href="javascript:showPortsOnlyConfig();">如何配置</a>）</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_port" class="col-md-3 control-label">[单] 端口号</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="single_port" value="" id="single_port" placeholder="443">
                                                            <span class="help-block"> 推荐80或443，后端需要配置 </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden single-setting">
                                                        <label for="single_passwd" class="col-md-3 control-label">[单] 密码</label>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control" name="single_passwd" value="" id="single_passwd" placeholder="password">
                                                            <span class="help-block"> 展示和生成配置用，后端配置注意保持一致 </span>
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
                                                            <span class="help-block"> 展示和生成配置用，后端配置注意保持一致 </span>
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
                                                            <span class="help-block"> 展示和生成配置用，后端配置注意保持一致 </span>
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
                                                            <span class="help-block"> 展示和生成配置用，后端配置注意保持一致 </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END SAMPLE FORM PORTLET-->
                                        </div>
                                        <div class="col-md-6">
                                            <!-- BEGIN SAMPLE FORM PORTLET-->
                                            <div class="portlet light bordered">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <span class="caption-subject font-dark bold">扩展信息</span>
                                                    </div>
                                                </div>
                                                <div class="portlet-body">
                                                    <div class="form-group">
                                                        <label for="compatible" class="col-md-3 control-label">兼容SS</label>
                                                        <div class="col-md-8">
                                                            <select class="form-control" name="compatible" id="compatible">
                                                                <option value="0" selected>否</option>
                                                                <option value="1">是</option>
                                                            </select>
                                                            <span class="help-block"> 如果兼容请在服务端配置协议和混淆时加上<span style="color:red">_compatible</span> </span>
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
                                                            <input type="text" class="form-control right" name="monitor_url" value="" id="monitor_url" placeholder="">
                                                            <span class="help-block"> 例如：http://us1.xxx.com/monitor.php </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END SAMPLE FORM PORTLET-->
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
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/layer/layer.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 用户标签选择器
        $('#labels').select2({
            placeholder: '设置后则可见相同标签的节点',
            allowClear: true
        });

        // ajax同步提交
        function do_submit() {
            var name = $('#name').val();
            var labels = $("#labels").val();
            var group_id = $("#group_id option:selected").val();
            var country_code = $("#country_code option:selected").val();
            var server = $('#server').val();
            var ip = $('#ip').val();
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
            var compatible = $('#compatible').val();
            var single = $('#single').val();
            var single_force = $('#single_force').val();
            var single_port = $('#single_port').val();
            var single_passwd = $('#single_passwd').val();
            var single_method = $('#single_method').val();
            var single_protocol = $('#single_protocol').val();
            var single_obfs = $('#single_obfs').val();
            var sort = $('#sort').val();
            var status = $('#status').val();

            $.ajax({
                type: "POST",
                url: "{{url('admin/addNode')}}",
                async: false,
                data: {_token:'{{csrf_token()}}', name: name, labels:labels, group_id:group_id, country_code:country_code, server:server, ip:ip, desc:desc, method:method, traffic_rate:traffic_rate, protocol:protocol, protocol_param:protocol_param, obfs:obfs, obfs_param:obfs_param, bandwidth:bandwidth, traffic:traffic, monitor_url:monitor_url, compatible:compatible, single:single, single_force:single_force, single_port:single_port, single_passwd:single_passwd, single_method:single_method, single_protocol:single_protocol, single_obfs:single_obfs, sort:sort, status:status},
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
        $("#single").on('change', function() {
            var single = parseInt($(this).val());

            if (single) {
                $(".single-setting").removeClass('hidden');
            } else {
                $(".single-setting").removeClass('hidden');
                $(".single-setting").addClass('hidden');
            }
        });

        // 服务条款
        function showTnc() {
            var content = '1、请勿直接复制黏贴以下配置，SSR(R)会报错的；'
                + '<br>2、确保服务器时间为CST或UTC；'
                + '<br>3、请看<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91" target="_blank">WIKI</a>；'
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