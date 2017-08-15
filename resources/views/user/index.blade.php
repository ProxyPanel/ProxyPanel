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
                                        @if (!$articleList->isEmpty())
                                            @foreach($articleList as $article)
                                                <li>
                                                    <div class="col1">
                                                        <div class="cont">
                                                            <div class="cont-col1">
                                                                <div class="label label-sm label-success">
                                                                    <i class="fa fa-bell-o"></i>
                                                                </div>
                                                            </div>
                                                            <div class="cont-col2">
                                                                <div class="desc"> <a href="{{url('user/article?id=') . $article->id}}" target="_blank">{{$article->title}}</a> </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @endif
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
                                        端口：{{$info['port']}}
                                    </li>
                                    <li class="list-group-item">
                                        加密方式：{{$info['method']}}
                                        <span class="badge badge-warning"><a href="{{url('user/profile#tab_2')}}">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        连接密码：{{$info['passwd']}}
                                        <span class="badge badge-warning"><a href="{{url('user/profile#tab_2')}}">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        协议：{{$info['protocol']}}
                                        <span class="badge badge-warning"><a href="{{url('user/profile#tab_2')}}">修改</a></span>
                                    </li>
                                    <li class="list-group-item">
                                        混淆：{{$info['obfs']}}
                                        <span class="badge badge-warning"><a href="{{url('user/profile#tab_2')}}">修改</a></span>
                                    </li>
                                    <li class="list-group-item"> 最后使用：{{empty($info['t']) ? '未使用' : date('Y-m-d H:i:s', $info['t'])}}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-5" style="text-align: center;">
                                <h3> 流量 </h3>
                                <input class="knob" value="35" title="可用流量：{{$info['transfer_enable']}}">
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
