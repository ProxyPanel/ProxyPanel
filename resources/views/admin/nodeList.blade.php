@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="note note-info">
                    <p>节点绑定域名推荐使用<a href="https://www.namesilo.com/?rid=326ec20pa" target="_blank">Namesilo</a>，本面板支持自动更新DNS <a href="https://github.com/ssrpanel/SSRPanel/wiki/%E8%B4%AD%E4%B9%B0%E5%9F%9F%E5%90%8D%EF%BC%88%E8%87%AA%E5%B8%A6%E9%9A%90%E7%A7%81%E4%BF%9D%E6%8A%A4%EF%BC%89" target="_blank" style="color:red;">[购买域名]</a></p>
                    <p>状态显示为'离线'：1.后端进程挂掉；2.节点和数据库之间的时区不一致或者通信延迟过高；3.服务器真的宕机。<a href="https://github.com/ssrpanel/ssrpanel/wiki/VPS%E6%8E%A8%E8%8D%90&%E8%B4%AD%E4%B9%B0%E7%BB%8F%E9%AA%8C" target="_blank" style="color:red;">[VPS推荐]</a></p>
                    <p>务必检查各节点服务器的时间是否同步。<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91" target="_blank" style="color:red;">[时间校准]</a></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 节点列表 </span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <button class="btn sbold blue" onclick="addNode()"> 添加节点 </button>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable table-scrollable-borderless">
                            <table class="table table-hover table-light">
                                <thead>
                                <tr>
                                    <th> <span class="node-id"><a href="javascript:showIdTips();">ID</a></span> </th>
                                    <th> 类型 </th>
                                    <th> 名称 </th>
                                    <th> IP </th>
                                    <th> 域名 </th>
                                    <th> 存活 </th>
                                    <th> 状态 </th>
                                    <th> 在线 </th>
                                    <th> <span class="node-flow"><a href="javascript:showFlowTips();">产生流量</a></span> </th>
                                    <th> 流量比例 </th>
                                    <th> 扩展 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($nodeList->isEmpty())
                                        <tr>
                                            <td colspan="11" style="text-align: center;">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($nodeList as $node)
                                            <tr class="odd gradeX">
                                                <td> {{$node->id}} </td>
                                                <td>
                                                    @if($node->is_transit)
                                                        <span class="label {{$node->status ? 'label-info' : 'label-default'}}">{{$node->is_transit ? '中转' : ''}}</span>
                                                    @else
                                                        <span class="label {{$node->status ? 'label-info' : 'label-default'}}">{{$node->type == 2 ? 'V2Ray' : 'Shadowsocks(R)'}}</span>
                                                    @endif
                                                </td>
                                                <td> {{$node->name}} </td>
                                                <td>
                                                    @if($node->is_nat)
                                                        <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">NAT</span>
                                                    @else
                                                        <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->ip}}</span>
                                                    @endif
                                                </td>
                                                <td> <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->server}}</span> </td>
                                                <td> <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->is_transit ? '' : $node->uptime}}</span> </td>
                                                <td> <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->is_transit ? '' : $node->load}}</span> </td>
                                                <td> <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->is_transit ? '' : $node->online_users}}</span> </td>
                                                <td> {{$node->is_transit ? '' : $node->transfer}} </td>
                                                <td> <span class="label {{$node->status ? 'label-danger' : 'label-default'}}">{{$node->traffic_rate}}</span> </td>
                                                <td>
                                                    @if($node->compatible) <span class="label label-info">兼</span> @endif
                                                    @if($node->single) <span class="label label-info">单</span> @endif
                                                    @if(!$node->is_subscribe) <span class="label label-info"><s>订</s></span> @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="javascript:;" aria-expanded="false"> 操作
                                                            <i class="fa fa-angle-down"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="javascript:editNode('{{$node->id}}');"> 编辑 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:delNode('{{$node->id}}');"> 删除 </a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:nodeMonitor('{{$node->id}}');"> 流量概况 </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$nodeList->total()}} 个节点</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $nodeList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        // 添加节点
        function addNode() {
            window.location.href = '{{url('admin/addNode')}}';
        }

        // 编辑节点
        function editNode(id) {
            window.location.href = '{{url('admin/editNode?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 删除节点
        function delNode(id) {
            layer.confirm('确定删除节点？', {icon: 2, title:'警告'}, function(index) {
                $.post("{{url('admin/delNode')}}", {id:id, _token:'{{csrf_token()}}'}, function(ret) {
                    layer.msg(ret.message, {time:1000}, function() {
                        if (ret.status == 'success') {
                            window.location.reload();
                        }
                    });
                });

                layer.close(index);
            });
        }

        // 节点流量监控
        function nodeMonitor(id) {
            window.location.href = '{{url('admin/nodeMonitor?id=')}}' + id + '&page=' + '{{Request::get('page', 1)}}';
        }

        // 显示提示
        function showIdTips() {
            layer.tips('对应SSR(R)后端usermysql.json中的nodeid', '.node-id', {
                tips: [3, '#3595CC'],
                time: 1200
            });
        }

        // 显示提示
        function showFlowTips() {
            layer.tips('如果服务器使用锐速等加速工具，则实际产生的流量会超出以下的值', '.node-flow', {
                tips: [3, '#3595CC'],
                time: 1200
            });
        }

        // 修正table的dropdown
        $('.table-scrollable').on('show.bs.dropdown', function () {
            $('.table-scrollable').css( "overflow", "inherit" );
        });

        $('.table-scrollable').on('hide.bs.dropdown', function () {
            $('.table-scrollable').css( "overflow", "auto" );
        });
    </script>
@endsection