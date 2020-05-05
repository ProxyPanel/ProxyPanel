@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/ascolorpicker/asColorPicker.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/dropify/dropify.min.css" type="text/css" rel="stylesheet">
    <style type="text/css">
        .text-help {
            padding-left: 1.0715rem;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title"><i class="icon wb-shopping-cart" aria-hidden="true"></i>编辑商品</h1>
                <div class="panel-actions">
                    <a href="/shop/goodsList" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    {{Session::get('successMsg')}}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <strong>错误：</strong> {{$errors->first()}}
                </div>
            @endif
            <div class="panel-body">
                <form action="/shop/editGoods/{{$goods->id}}" method="post" enctype="multipart/form-data"
                        class="form-horizontal" role="form">
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="type">类型</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="1" @if($goods->type == 1) checked
                                        @endif disabled/>
                                <label for="type">流量包</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" value="2" @if($goods->type == 2) checked
                                        @endif disabled/>
                                <label for="type">套餐</label>
                            </div>
                        </div>
                        <span class="offset-md-2 text-help"> 套餐与账号有效期有关，流量包只扣可用流量，不影响有效期 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="name">名称</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="name" id="name" value="{{$goods->name}}"
                                    required/>
                            {{csrf_field()}}
                            <input name="id" value="{{$goods->id}}" hidden/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="price">售价</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="price" id="price" value="{{$goods->price}}"
                                    step="0.01" required/>
                            <span class="input-group-text">元</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="status">状态</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="status" value="1" @if($goods->status == 1) checked @endif/>
                                <label for="status">上架</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="status" value="0" @if($goods->status == 0) checked @endif/>
                                <label for="status">下架</label>
                            </div>
                        </div>
                    </div>
                    @if ($goods->type == 2)
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="renew">流量重置价格</label>
                            <div class="col-md-4 input-group">
                                <input type="number" class="form-control" name="renew" id="renew"
                                        value="{{$goods->renew}}" step="0.01"/>
                                <span class="input-group-text">元</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="period">重置周期</label>
                            <div class="col-md-4 input-group">
                                <input type="number" class="form-control" name="period" id="period"
                                        value="{{$goods->period}}" required/>
                                <span class="input-group-text">天</span>
                            </div>
                            <span class="text-help"> 套餐流量会每N天重置 </span>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="traffic">流量额度</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="traffic" id="traffic"
                                    value="{{$goods->traffic}}" disabled/>
                            <span class="input-group-text">MB</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="limit_num">限购数量</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="limit_num" id="limit_num"
                                    value="{{$goods->limit_num}}" required/>
                            <span class="input-group-text">次</span>
                        </div>
                        <span class="text-help"> 每个用户可以购买该商品次数，为 0 时代表不限购 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="labels">标签</label>
                        <div class="col-md-6">
                            <select class="form-control show-tick" name="labels[]" id="labels"
                                    data-plugin="selectpicker" data-style="btn-outline btn-primary" multiple>
                                @foreach($label_list as $label)
                                    <option value="{{$label->id}}"
                                            @if(in_array($label->id, $goods->labels)) selected @endif>{{$label->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="text-help"> 自动给购买此商品的用户打上相应的标签 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="days">有效期</label>
                        <div class="col-md-4 input-group">
                            <input type="number" class="form-control" name="days" id="days" value="{{$goods->days}}"
                                    disabled/>
                            <span class="input-group-text">天</span>
                        </div>
                        <span class="text-help"> 到期后会自动从总流量扣减对应的流量 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sort">排序</label>
                        <div class="col-md-4">
                            <input type="number" class="form-control" name="sort" id="sort" value="{{$goods->sort}}"/>
                        </div>
                        <span class="text-help"> 排序值越大排越前 </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="color">颜色</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="color" id="color" data-plugin="asColorPicker"
                                    data-mode="simple" value="{{$goods->color}}"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="is_hot">热销</label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="is_hot" value="1" @if($goods->is_hot == 1) checked @endif/>
                                <label for="is_hot">是</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="is_hot" value="0" @if($goods->is_hot == 0) checked @endif/>
                                <label for="is_hot">否</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="logo">商品图片</label>
                        <div class="col-md-6">
                            <input type="file" id="logo" name="logo" data-plugin="dropify"
                                    data-default-file={{$goods->logo?:'/assets/images/default.png'}} />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="desc">描述</label>
                        <div class="col-md-8">
                            <textarea class="form-control" rows="2" name="desc" id="desc"
                                    placeholder="商品的简单描述">{{$goods->desc}}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="info">自定义列表</label>
                        <div class="col-md-8">
                            <textarea class="form-control" rows="6" name="info" id="info"
                                    placeholder="商品的自定义列表添加">{{$goods->info}}</textarea>
                        </div>
                        <span class="offset-md-2 text-help"> 每行内容请以<code>&lt;li&gt;</code> 开头 <code>&lt;/li&gt;</code> 结尾</span>
                    </div>
                    <div class="form-actions col-12 text-right">
                        <button type="submit" class="btn btn-success"><i class="icon wb-check"></i> 提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/ascolor/jquery-asColor.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/asgradient/jquery-asGradient.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/ascolorpicker/jquery-asColorPicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/ascolorpicker.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/dropify.js" type="text/javascript"></script>
@endsection
