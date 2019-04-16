@extends('admin.layouts')
@section('css')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('successMsg'))
                    <div class="alert alert-success">
                        <button class="close" data-close="alert"></button>
                        {{Session::get('successMsg')}}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <span> {{$errors->first()}} </span>
                    </div>
                @endif
                <!-- BEGIN PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark sbold uppercase">生成卡券</span>
                        </div>
                        <div class="actions"></div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="{{url('coupon/addCoupon')}}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label col-md-3">卡券名称</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="name" value="{{Request::old('name')}}" id="name" autocomplete="off" required>
                                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">LOGO</label>
                                    <div class="col-md-4">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                <img src="/assets/images/noimage.png" alt="" />
                                            </div>
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
                                    <label class="control-label col-md-3">类型</label>
                                    <div class="col-md-4">
                                        <div class="mt-radio-inline">
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="1" checked> 抵用券
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="3"> 充值券
                                                <span></span>
                                            </label>
                                            <label class="mt-radio">
                                                <input type="radio" name="type" value="2"> 折扣券
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">用途</label>
                                    <div class="col-md-4">
                                        <div class="mt-radio-inline">
                                            <label class="mt-radio">
                                                <input type="radio" name="usage" value="1" id="usage1" checked> 仅限一次性使用
                                                <span></span>
                                            </label>
                                            <label class="mt-radio hide">
                                                <input type="radio" name="usage" value="2" id="usage2"> 可重复使用
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">数量</label>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="num" value="{{Request::old('num')}}" id="num" autocomplete="off" required>
                                            <span class="input-group-addon">张</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">金额</label>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="amount" value="{{Request::old('amount')}}" id="amount" autocomplete="off" required>
                                            <span class="input-group-addon">元</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group hide">
                                    <label class="control-label col-md-3">折扣</label>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="discount" value="{{Request::old('discount')}}" id="discount" autocomplete="off" placeholder="">
                                            <span class="input-group-addon">折</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">有效期</label>
                                    <div class="col-md-4">
                                        <div class="input-group input-large input-daterange">
                                            <input type="text" class="form-control" name="available_start" value="{{Request::old('available_start')}}" id="available_start" autocomplete="off" required>
                                            <span class="input-group-addon"> 至 </span>
                                            <input type="text" class="form-control" name="available_end" value="{{Request::old('available_end')}}" id="available_end" autocomplete="off" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-4">
                                        <button type="submit" class="btn green">提交</button>
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
    <script src="/assets/global/plugins/laydate/laydate.js" type="text/javascript"></script>

    <script type="text/javascript">
        // 有效期-开始
        laydate.render({
            elem: '#available_start'
        });

        // 有效期-结束
        laydate.render({
            elem: '#available_end'
        });

        // 根据类型显示
        $("input[name='type']").change(function(){
            var type = $(this).val();
            if (type == '1' || type == '3') {
                $("#amount").parent("div").parent("div").parent("div").removeClass("hide");
                $("#discount").parent("div").parent("div").parent("div").addClass("hide");
                $("#amount").prop('required', 'required');
                $("#discount").removeAttr('required');
                $("#discount").val('');
                $("#usage2").parent("label").addClass("hide");
                $("#usage1").prop('checked', 'checked');
                $("#usage2").prop('checked', false);
            } else {
                $("#amount").parent("div").parent("div").parent("div").addClass("hide");
                $("#discount").parent("div").parent("div").parent("div").removeClass("hide");
                $("#discount").prop('required', 'required');
                $("#amount").removeAttr('required');
                $("#amount").val('');
                $("#usage2").parent("label").removeClass("hide");
            }
        });
    </script>
@endsection