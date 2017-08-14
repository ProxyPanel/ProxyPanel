@extends('user.layouts')

@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user')}}">用户中心</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <div class="row">
            <div class="col-md-6">
                <!-- BEGIN PORTLET -->
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption caption-md">
                            <i class="icon-globe theme-font hide"></i>
                            <span class="caption-subject font-blue-madison bold uppercase">系统公告</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <!--BEGIN TABS-->
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="scroller" style="height: 170px;" data-always-visible="1" data-rail-visible1="0" data-handle-color="#D7DCE2">
                                    <ul class="feeds">
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-success">
                                                            <i class="fa fa-bell-o"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> 节点全部切换为SSR，请注意更改客户端配置信息 </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-default">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> 促销：200G流量包，原价50元现在只要35元，有效期180天 </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> 新节点：新上架新加坡节点，白银以上会员可见  </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-default">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> 新节点：新上架白俄罗斯节点，白银以上会员可见 </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> 新节点：新上架新西兰节点，白银以上会员可见  </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--END TABS-->
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- BEGIN PORTLET -->
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption caption-md">
                            <i class="icon-globe theme-font hide"></i>
                            <span class="caption-subject font-blue-madison bold uppercase"> 当前状态 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-7">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        等级：普通会员
                                    </li>
                                    <li class="list-group-item">
                                        端口：10222
                                        <span class="badge badge-warning"><a href="#">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        加密方式：aes-192-ctr
                                        <span class="badge badge-warning"><a href="#">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        连接密码：@123
                                        <span class="badge badge-warning"><a href="#">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        协议：orgin
                                        <span class="badge badge-warning"><a href="#">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        混淆：plain
                                        <span class="badge badge-warning"><a href="#">修改</a></span>
                                    </li>
                                    <li class="list-group-item"> 最后使用：2017-2-2 12:12:12
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-5" style="text-align: center;">
                                <h3> 流量 </h3>
                                <input class="knob" value="35" title="可用流量：1000G">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-knob/js/jquery.knob.js" type="text/javascript"></script>

    <script>
        $(function() {
            $(".knob").knob({
                'readOnly':true,
                'angleoffset':0,
                'width':150,
                'height':150,
            });
        });
    </script>
@endsection
