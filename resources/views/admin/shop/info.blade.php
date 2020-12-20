@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/custom/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/ascolorpicker/asColorPicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title">
                    <i class="icon wb-shopping-cart" aria-hidden="true"></i>
                    @isset($goods) 编辑商品 @else 添加商品 @endisset
                </h1>
                <div class="panel-actions">
                    <a href="{{route('admin.goods.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action=@isset($goods){{route('admin.goods.update', $goods->id)}} @else {{route('admin.goods.store')}} @endisset method="post"
                      enctype="multipart/form-data" class="form-horizontal" role="form">@csrf
                    @isset($goods) @method('PUT') @endisset
                    <div class="form-row">
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label">类型</label>
                                <div class="col-md-10 align-items-center">
                                    <div class="radio-custom radio-primary radio-inline">
                                        <input type="radio" name="type" id="data_package" value="1"/>
                                        <label for="data_package">流量包</label>
                                    </div>
                                    <div class="radio-custom radio-primary radio-inline">
                                        <input type="radio" name="type" id="data_plan" value="2" checked/>
                                        <label for="data_plan">套餐</label>
                                    </div>
                                    <span class="text-help"> 套餐与账号有效期有关，流量包只扣可用流量，不影响有效期 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="name">名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="name" id="name" required/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="price">售价</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="price" id="price" step="0.01" required/>
                                    <span class="input-group-text">元</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="level" class="col-md-2 col-form-label">等级</label>
                                <div class="col-md-4">
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="level" id="level">
                                        @foreach ($levelList as $level)
                                            <option value="{{$level->level}}">{{$level->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="renew">流量重置价格</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="renew" id="renew" step="0.01" value="0"/>
                                    <span class="input-group-text">元</span>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="period">重置周期</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="period" id="period" value="30"/>
                                    <span class="input-group-text">天</span>
                                    <span class="text-help"> 套餐流量会每N天重置 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="traffic">流量额度</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="traffic" id="traffic" value="100"/>
                                    <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="form-control" name="traffic_unit" id="traffic_unit">
                                        <option value="" selected>MB</option>
                                        <option value="1024">GB</option>
                                        <option value="1048576">TB</option>
                                        <option value="1073741824">PB</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="invite_num">赠送邀请码数量</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="invite_num" id="invite_num" value="0" required/>
                                    <span class="input-group-text">枚</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="limit_num">限购数量</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="limit_num" id="limit_num" value="0" required/>
                                    <span class="input-group-text">次</span>
                                    <span class="text-help"> 每个用户可以购买该商品次数，为 0 时代表不限购 </span>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="days">有效期</label>
                                <div class="col-md-4 input-group">
                                    <input type="number" class="form-control" name="days" id="days" value="30"/>
                                    <span class="input-group-text">天</span>
                                    <span class="text-help"> 到期后会自动从总流量扣减对应的流量 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="is_hot">热销</label>
                                <div class="col-md-10">
                                    <input type="checkbox" data-toggle="switch" name="is_hot" id="is_hot" data-on-color="primary" data-off-color="default"
                                           data-on-text="是" data-off-text="否" data-size="small">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="status">状态</label>
                                <div class="col-md-10">
                                    <input type="checkbox" data-toggle="switch" name="status" id="status" data-on-color="primary" data-off-color="default"
                                           data-on-text="上架" data-off-text="下架" data-size="small">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="sort">排序</label>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="sort" id="sort" value="0"/>
                                    <span class="text-help"> 排序值越大排越前 </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="color">颜色</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="color" id="color" data-plugin="asColorPicker" data-mode="simple" value="#A57AFA"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="logo">商品图片</label>
                                <div class="col-md-6">
                                    <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="{{asset($goods->logo ?? '/assets/images/default.png')}}"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="description">描述</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" rows="2" name="description" id="description" placeholder="商品的简单描述"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="info">自定义列表</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" rows="6" name="info" id="info" placeholder="商品的自定义列表添加"></textarea>
                                    <span class="text-help"> 每行内容请以<code>&lt;li&gt;</code> 开头<code>&lt;/li&gt;</code> 结尾</span>
                                </div>
                            </div>
                            <div class="form-actions col-12 text-right">
                                <button type="submit" class="btn btn-success"><i class="icon wb-check"></i> 提 交</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/ascolor/jquery-asColor.min.js"></script>
    <script src="/assets/global/vendor/asgradient/jquery-asGradient.min.js"></script>
    <script src="/assets/global/vendor/ascolorpicker/jquery-asColorPicker.min.js"></script>
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/custom/bootstrap-switch/bootstrap-switch.min.js"></script>
    <script src="/assets/global/js/Plugin/ascolorpicker.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script>
      $('[data-toggle="switch"]').bootstrapSwitch();
      @isset($goods)
      $(document).ready(function() {
        const type = $('input[name=\'type\']');
        $('#id').val('{{$goods->id}}');
        $("input[name='type'][value='{{$goods->type}}']").click();
        type.attr('disabled', true);
        $('#name').val('{{$goods->name}}');
        $('#price').val('{{$goods->price}}');
        $('#level').selectpicker('val', '{{$goods->level}}');
          @if ($goods->type == 2)
          $('#renew').val('{{$goods->renew}}');
        $('#period').val('{{$goods->period}}');
        $('#days').val('{{$goods->days}}').attr('disabled', true);
          @endif
          $('#invite_num').val('{{$goods->invite_num}}');
        $('#limit_num').val('{{$goods->limit_num}}');
          @if ($goods->is_hot)
          $('#is_hot').click();
          @endif
          @if ($goods->status)
          $('#status').click();
          @endif
          $('#sort').val('{{$goods->sort}}');
        $('#color').asColorPicker('val', '{{$goods->color}}');
        $('#description').val('{{$goods->description}}');
        $('#info').val('{!! $goods->info !!}');
        const trafficUnit = $('#traffic_unit');
        const traffic = $('#traffic');
          @if($goods->traffic >= 1073741824)
          traffic.val('{{$goods->traffic/1073741824}}');
        trafficUnit.selectpicker('val', '1073741824');
          @elseif($goods->traffic >= 1048576)
          traffic.val('{{$goods->traffic/1048576}}');
        trafficUnit.selectpicker('val', '1048576');
          @elseif($goods->traffic >= 1024)
          traffic.val('{{$goods->traffic/1024}}');
        trafficUnit.selectpicker('val', '1024');
          @else
          traffic.val('{{$goods->traffic}}');
          @endif
          traffic.attr('disabled', true);
        trafficUnit.attr('disabled', true).selectpicker('refresh');
      });
      @elseif(old('type'))
      $(document).ready(function() {
        const type = $('input[name=\'type\']');
        $('#id').val('{{old('id')}}');
        $("input[name='type'][value='{{old('type')}}']").click();
        $('#name').val('{{old('name')}}');
        $('#price').val('{{old('price')}}');
        $('#level').selectpicker('val', '{{old('level')}}');
          @if (old('type') == 2)
          $('#renew').val('{{old('renew',0)}}');
        $('#period').val('{{old('period',0)}}');
        $('#days').val('{{old('days',0)}}');
          @endif
          $('#traffic').val('{{old('traffic')}}');
        $('#traffic_unit').selectpicker('val', '{{old('traffic_unit')}}');
        $('#invite_num').val('{{old('invite_num')}}');
        $('#limit_num').val('{{old('limit_num')}}');
          @if (old('is_hot'))
          $('#is_hot').click();
          @endif
          @if (old('status'))
          $('#status').click();
          @endif
          $('#sort').val('{{old('sort')}}');
        $('#color').asColorPicker('val', '{{old('color')}}');
        $('#description').val('{{old('description')}}');
        $('#info').val('{{old('info')}}');
      });
      @else
      $('#status').click();

      @endisset

      function itemControl(value) {
        if (value === 1) {
          $('.package-renew').hide();
        } else {
          $('.package-renew').show();
        }
      }

      // 选择商品类型
      $('input[name=\'type\']').change(function() {
        itemControl(parseInt($(this).val()));
      });
    </script>
@endsection
