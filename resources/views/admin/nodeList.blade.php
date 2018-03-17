@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="note note-info">
                    <p>如果负载显示宕机，可能是节点服务器宕机或者节点上的SSR(R)服务挂掉了，请重启节点服务器或者SSR(R)服务。</p>
                    <p>宕机是因为节点服务器IDC母鸡超售过载导致，SSR(R)挂掉是因为节点服务器配置太渣。<a href="https://github.com/ssrpanel/ssrpanel/wiki/VPS%E6%8E%A8%E8%8D%90&%E8%B4%AD%E4%B9%B0%E7%BB%8F%E9%AA%8C" target="_blank" style="color:red;">[VPS推荐]</a></p>
                    <p>如果还是无法解决，请检查各节点服务器的时间是否同步。<a href="https://github.com/ssrpanel/SSRPanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91" target="_blank" style="color:red;">[时间校准]</a></p>
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
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th> <span class="node-id"><a href="javascript:showIdTips();">ID</a></span> </th>
                                    <th> 节点名称 </th>
                                    <th> 域名 </th>
                                    <th> IP </th>
                                    <th> 负载 </th>
                                    <th> 在线 </th>
                                    <th> 产生流量 </th>
                                    <th> 流量比例 </th>
                                    <th> 扩展 </th>
                                    <th> 操作 </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($nodeList->isEmpty())
                                        <tr>
                                            <td colspan="10">暂无数据</td>
                                        </tr>
                                    @else
                                        @foreach($nodeList as $node)
                                            <tr class="odd gradeX">
                                                <td> {{$node->id}} </td>
                                                <td>
                                                    @if(!$node->status)
                                                        <span class="label label-warning" title="维护中">{{$node->name}}</span>
                                                    @else
                                                        {{$node->name}}
                                                    @endif
                                                </td>
                                                <td> <span class="label label-danger">{{$node->server}}</span> </td>
                                                <td> <span class="label label-danger">{{$node->ip}}</span> </td>
                                                <td> <span class="label label-danger">{{$node->load}}</span> </td>
                                                <td> <span class="label label-danger">{{$node->online_users}}</span> </td>
                                                <td> {{$node->transfer}}G </td>
                                                <td> <span class="label label-danger">{{$node->traffic_rate}}</span> </td>
                                                <td>
                                                    @if($node->compatible) <span class="label label-info">兼</span> @endif
                                                    @if($node->single) <span class="label label-danger">单</span> @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm blue btn-outline" onclick="editNode('{{$node->id}}')">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm purple btn-outline" onclick="nodeMonitor('{{$node->id}}')">
                                                        <i class="fa fa-area-chart"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm red btn-outline" onclick="delNode('{{$node->id}}')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
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
    <script src="/js/layer/layer.js" type="text/javascript"></script>

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
    </script>
@endsection