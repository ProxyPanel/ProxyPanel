@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css">
    {{--<link rel="stylesheet" href="/theme/global/vendor/dropify/dropify.min.css">--}}
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">编辑商品</h2>
            </div>
            @if (Session::has('successMsg'))
                <div class="alert alert-success" role="alert">
                    <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                    {{Session::get('successMsg')}}
                </div>
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                        <strong>错误：</strong> {{$errors->first()}}
                    </div>
                @else
                    <div class="alert alert-info" role="alert">
                        <button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
                        <strong>警告：</strong>购买新套餐则会覆盖所有已购但未过期的旧套餐并删除这些旧套餐对应的流量，所以设置商品时请务必注意类型和有效期，流量包则可叠加。</p>
                    </div>
                @endif
                <div class="panel-body">
                    <form action="{{url('shop/editGoods')}}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
                        <div class="form-group row">
                            <label for="type" class="col-form-label col-md-2">类型</label>
                            <ul class="col-md-9 list-unstyled list-inline">
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="type" value="1" @if($goods->type == 1) checked @endif disabled>
                                        <label>流量包</label>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="type" value="2" @if($goods->type == 2) checked @endif disabled>
                                        <label>套餐</label>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="type" value="3" @if($goods->type == 3) checked @endif disabled>
                                        <label>充值</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">商品名称</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name" value="{{$goods->name}}" id="name" placeholder="" required>
                                <input type="hidden" name="id" value="{{$goods->id}}"/>
                                <input type="hidden" name="_token" value="{{csrf_token()}}"/>
                            </div>
                        </div>
                    <!--
					<div class="form-group row">
						<label class="col-form-label col-md-2">商品图片</label>
						<div class="col-md-6">
							<input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file= @if ($goods->logo) {{$goods->logo}} @else /assets/images/noimage.png @endif />
							<button type="submit" class="btn btn-success float-right mt-10">提交</button>
						</div>
					</div>
					-->
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">描述</label>
                            <div class="col-md-8">
                                <textarea class="form-control" rows="1" name="info" id="info" placeholder="商品的简单描述">{{$goods->info}}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">列表</label>
                            <div class="col-md-8">
                                <textarea class="form-control" rows="4" name="desc" id="desc" placeholder="商品的列表添加">{{$goods->desc}}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-2">售价</label>
                            <div class="col-md-2 input-group">
                                <input type="text" class="form-control" name="price" value="{{$goods->price}}" id="price" placeholder="" required>
                                <span class="input-group-text">元</span>
                            </div>
                        </div>
                        @if($goods->type <= 2)
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">内含流量</label>
                                <div class="col-md-3 input-group">
                                    <input type="text" class="form-control" name="traffic" value="{{$goods->traffic}}" id="traffic" placeholder="" disabled>
                                    <span class="input-group-text">MB</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="labels" class="col-md-2 col-form-label">标签</label>
                                <div class="col-md-4">
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control show-tick" id="labels" name="labels[]" multiple>
                                        @foreach($label_list as $label)
                                            <option value="{{$label->id}}" @if(in_array($label->id, $goods->labels)) selected @endif>{{$label->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="text-help offset-md-2"> 自动给购买此商品的用户打上相应的标签 </span>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">有效期</label>
                                <div class="col-md-6 input-group">
                                    <input type="text" class="form-control" name="days" value="{{$goods->days}}" id="days" placeholder="" disabled>
                                    <span class="input-group-text">天</span>
                                </div>
                                <span class="text-help offset-md-2"> 到期后会自动从总流量扣减对应的流量 </span>
                            </div>
                            <div class="form-group row">
                                <label for="sort" class="col-md-2 col-form-label">排序</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="sort" value="{{$goods->sort}}" id="sort" placeholder="">
                                    <span class="text-help offset-md-2"> 值越大排越前 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="color" class="col-md-2 col-form-label">颜色</label>
                                <div class="col-md-4">
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control col-md-4" name="color" id="color">
                                        <option class="bg-red-700 text-white" value="red" @if($goods->color == 'red') selected @endif>红</option>
                                        <option class="bg-pink-700 text-white" value="pink" @if($goods->color == 'pink') selected @endif>粉红</option>
                                        <option class="bg-purple-700 text-white" value="purple" @if($goods->color == 'purple') selected @endif>紫</option>
                                        <option class="bg-indigo-700 text-white" value="indigo" @if($goods->color == 'indigo') selected @endif>靛青</option>
                                        <option class="bg-blue-700 text-white" value="blue" @if($goods->color == 'blue') selected @endif>蓝</option>
                                        <option class="bg-cyan-700 text-white" value="cyan" @if($goods->color == 'cyan') selected @endif>青</option>
                                        <option class="bg-teal-700 text-white" value="teal" @if($goods->color == 'teal') selected @endif >深藍綠</option>
                                        <option class="bg-green-700 text-white" value="green" @if($goods->color == 'green') selected @endif>绿</option>
                                        <option class="bg-light-green-700 text-white" value="light-green" @if($goods->color == 'light-green') selected @endif>浅绿</option>
                                        <option class="bg-yellow-700 text-white" value="yellow" @if($goods->color == 'yellow') selected @endif>黄</option>
                                        <option class="bg-orange-700 text-white" value="orange" @if($goods->color == 'orange') selected @endif>橙</option>
                                        <option class="bg-brown-700 text-white" value="brown" @if($goods->color == 'brown') selected @endif>棕</option>
                                        <option class="bg-grey-700 text-white" value="grey" @if($goods->color == 'grey') selected @endif>灰</option>
                                        <option class="bg-blue-grey-700 text-white" value="blue-grey" @if($goods->color == 'blue-grey') selected @endif>蓝灰</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="is_hot" class="col-md-2 col-form-label">热销</label>
                                <ul class="col-md-10 list-unstyled list-inline">
                                    <li class="list-inline-item">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" name="is_hot" value="1" @if($goods->is_hot == 1) checked @endif>
                                            <label>是</label>
                                        </div>
                                    </li>
                                    <li class="list-inline-item">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" name="is_hot" value="0" @if($goods->is_hot == 0) checked @endif>
                                            <label>否</label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="form-group row">
                                <label for="is_limit" class="col-md-2 col-form-label">限购</label>
                                <ul class="col-md-10 list-unstyled list-inline">
                                    <li class="list-inline-item">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" name="is_limit" value="1" @if($goods->is_limit == 1) checked @endif>
                                            <label>是</label>
                                        </div>
                                    </li>
                                    <li class="list-inline-item">
                                        <div class="radio-custom radio-primary">
                                            <input type="radio" name="is_limit" value="0" @if($goods->is_limit == 0) checked @endif>
                                            <label>否</label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        <div class="form-group row last">
                            <label class="col-form-label col-md-2">状态</label>
                            <ul class="col-md-10 list-unstyled list-inline">
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="status" value="1" {{$goods->status == 1 ? 'checked' : ''}}>
                                        <label>上架</label>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="radio-custom radio-primary">
                                        <input type="radio" name="status" value="0" {{$goods->status == 0 ? 'checked' : ''}}>
                                        <label>下架</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success"><i class="icon wb-check"></i> 提 交</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    {{--<script src="/theme/global/vendor/dropify/dropify.min.js"></script>--}}
    {{--<script src="/theme/global/js/Plugin/dropify.js"></script>--}}
@endsection