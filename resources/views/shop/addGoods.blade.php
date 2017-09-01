@extends('admin.layouts')

@section('css')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css" />
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('shop/goodsList')}}">商品管理</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('shop/addGoods')}}">添加商品</a>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('successMsg'))
                    <div class="alert alert-success">
                        <button class="close" data-close="alert"></button>
                        {{Session::get('successMsg')}}
                    </div>
                @endif
                @if (Session::has('errorMsg'))
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <strong>错误：</strong> {{Session::get('errorMsg')}}
                    </div>
                @endif
                <!-- BEGIN PORTLET-->
                <div class="portlet light form-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green sbold uppercase">添加商品</span>
                        </div>
                        <div class="actions"></div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="{{url('shop/addGoods')}}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-3">商品名称</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="name" value="" id="name" placeholder="" required>
                                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">LOGO</label>
                                    <div class="col-md-9">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                <img src="/assets/images/noimage.png" alt="" /> </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                            <div>
                                                <span class="btn default btn-file">
                                                    <span class="fileinput-new"> 选择 </span>
                                                    <span class="fileinput-exists"> 更换 </span>
                                                    <input type="file" name="logo" id="logo">
                                                </span>
                                                <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> 移除 </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">内含流量</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="traffic" value="10" id="traffic" placeholder="" required="">
                                            <span class="input-group-addon">GiB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">售价</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="price" value="" id="price" placeholder="" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">所需积分</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="score" value="" id="score" placeholder="" required>
                                        <span class="help-block">换购该商品需要的积分值</span>
                                    </div>
                                </div>
                                <div class="form-group last">
                                    <label class="control-label col-md-3">状态</label>
                                    <div class="col-md-6">
                                        <div class="mt-radio-inline">
                                            <label class="mt-radio">
                                                <input type="radio" name="status" value="0" checked> 上架
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="status" value="1"> 下架
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green"> <i class="fa fa-check"></i> 提 交</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
                <!-- END PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        //
    </script>
@endsection