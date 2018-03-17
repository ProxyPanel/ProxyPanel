@extends('user.layouts')

@section('css')
    <link href="/assets/pages/css/profile.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top: 0px; min-height: 354px;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('successMsg'))
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('successMsg')}}
                    </div>
                @endif
                @if (Session::has('errorMsg'))
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                        <strong>错误：</strong> {{Session::get('errorMsg')}}
                    </div>
                @endif
                <!-- BEGIN PROFILE CONTENT -->
                <div class="profile-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <div class="portlet-title tabbable-line">
                                    <div class="caption caption-md">
                                        <i class="icon-globe theme-font hide"></i>
                                        <span class="caption-subject font-blue-madison bold uppercase">个人资料</span>
                                    </div>
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab_1" data-toggle="tab">登录密码</a>
                                        </li>
                                        <li>
                                            <a href="#tab_2" data-toggle="tab">联系方式</a>
                                        </li>
                                        <li>
                                            <a href="#tab_3" data-toggle="tab">SSR(R)设置</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="portlet-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <form action="{{url('user/profile')}}" method="post" enctype="multipart/form-data" class="form-bordered">
                                                <div class="form-group">
                                                    <label class="control-label"> 旧密码 </label>
                                                    <input type="password" class="form-control" name="old_password" id="old_password" autofocus required />
                                                    <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"> 新密码 </label>
                                                    <input type="password" class="form-control" name="new_password" id="new_password" required />
                                                </div>
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class=" col-md-4">
                                                            <button type="submit" class="btn green"> 提 交 </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_2">
                                            <form action="{{url('user/profile')}}" method="post" enctype="multipart/form-data" class="form-bordered">
                                                <div class="form-group">
                                                    <label class="control-label"> 微信 </label>
                                                    <input type="text" class="form-control" name="wechat" value="{{$info->wechat}}" id="wechat" required />
                                                    <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"> QQ </label>
                                                    <input type="text" class="form-control" name="qq" value="{{$info->qq}}" id="qq" required />
                                                </div>
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <button type="submit" class="btn green"> 提 交 </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="tab_3">
                                            <form action="{{url('user/profile')}}" method="post" enctype="multipart/form-data" class="form-bordered">
                                                <div class="form-group">
                                                    <label class="control-label"> 连接密码 </label>
                                                    <input type="text" class="form-control" name="passwd" value="{{$info->passwd}}" id="passwd" required />
                                                    <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"> 加密方式 </label>
                                                    <select class="form-control" name="method" id="method">
                                                        @foreach ($method_list as $method)
                                                            <option value="{{$method->name}}" @if($method->name == $info->method) selected @endif>{{$method->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"> 协议 </label>
                                                    <select class="form-control" name="protocol" id="protocol">
                                                        @foreach ($protocol_list as $protocol)
                                                            <option value="{{$protocol->name}}" @if($protocol->name == $info->protocol) selected @endif>{{$protocol->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label"> 混淆 </label>
                                                    <select class="form-control" name="obfs" id="obfs">
                                                        @foreach ($obfs_list as $obfs)
                                                            <option value="{{$obfs->name}}" @if($obfs->name == $info->obfs) selected @endif>{{$obfs->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <button type="submit" class="btn green"> 提 交 </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END PROFILE CONTENT -->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script type="text/javascript">
        //
    </script>
@endsection