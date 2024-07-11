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
                    {{ isset($good) ? trans('admin.action.edit_item', ['attribute' => trans('model.goods.attribute')]) : trans('admin.action.add_item', ['attribute' => trans('model.goods.attribute')]) }}
                </h1>
                <div class="panel-actions">
                    <a class="btn btn-danger" href="{{ route('admin.goods.index') }}">{{ trans('common.back') }}</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')" />
            @endif
            @if ($errors->any())
                <x-alert type="danger" :message="$errors->all()" />
            @endif
            <div class="panel-body">
                <form class="form-horizontal" role="form"
                      action=@isset($good){{ route('admin.goods.update', $good) }} @else {{ route('admin.goods.store') }} @endisset
                      method="post" enctype="multipart/form-data">@csrf
                    @isset($good)
                        @method('PUT')
                    @endisset
                    <div class="form-row">
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label">{{ trans('model.common.type') }}</label>
                                <div class="col-md-10 align-items-center">
                                    <div class="radio-custom radio-primary radio-inline">
                                        <input id="data_package" name="type" type="radio" value="1" />
                                        <label for="data_package">{{ trans('admin.goods.type.package') }}</label>
                                    </div>
                                    <div class="radio-custom radio-primary radio-inline">
                                        <input id="data_plan" name="type" type="radio" value="2" checked />
                                        <label for="data_plan">{{ trans('admin.goods.type.plan') }}</label>
                                    </div>
                                    <span class="text-help"> {{ trans('admin.goods.info.type_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="name">{{ trans('model.goods.name') }}</label>
                                <div class="col-md-4">
                                    <input class="form-control" id="name" name="name" type="text" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="price">{{ trans('model.goods.price') }}</label>
                                <div class="col-md-4 input-group">
                                    <div class="input-group-prepend">
                                        <span
                                              class="input-group-text">{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}</span>
                                    </div>
                                    <input class="form-control" id="price" name="price" type="number" step="0.01" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="level">{{ trans('model.goods.category') }}</label>
                                <div class="col-md-4">
                                    <select class="form-control" id="category_id" name="category_id" data-plugin="selectpicker"
                                            data-style="btn-outline btn-primary">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="level">{{ trans('model.common.level') }}</label>
                                <div class="col-md-4">
                                    <select class="form-control" id="level" name="level" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->level }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="renew">{{ trans('model.goods.renew') }}</label>
                                <div class="col-md-4 input-group">
                                    <div class="input-group-prepend">
                                        <span
                                              class="input-group-text">{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}</span>
                                    </div>
                                    <input class="form-control" id="renew" name="renew" type="number" value="0" step="0.01" />
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="speed_limit">{{ trans('model.goods.user_limit') }}</label>
                                <div class="col-md-4 input-group">
                                    <input class="form-control" id="speed_limit" name="speed_limit" type="number" value="0" style="width: 30%" />
                                    <span class="input-group-text"> Mbps</span>
                                    <span class="text-help"> {{ trans('admin.zero_unlimited_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="period">{{ trans('model.goods.period') }}</label>
                                <div class="col-md-4 input-group">
                                    <input class="form-control" id="period" name="period" type="number" value="30" />
                                    <span class="input-group-text"> {{ trans_choice('common.days.attribute', 1) }}</span>
                                    <span class="text-help"> {{ trans('admin.goods.info.period_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="traffic">{{ trans('model.goods.traffic') }}</label>
                                <div class="col-md-4 input-group">
                                    <input class="form-control" id="traffic" name="traffic" type="number" value="100" />
                                    <select class="form-control" id="traffic_unit" name="traffic_unit" data-plugin="selectpicker"
                                            data-style="btn-outline btn-primary">
                                        <option value="1" selected>MB</option>
                                        <option value="1024">GB</option>
                                        <option value="1048576">TB</option>
                                        <option value="1073741824">PB</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="invite_num">{{ trans('model.goods.invite_num') }}</label>
                                <div class="col-md-4">
                                    <input class="form-control" id="invite_num" name="invite_num" type="number" value="0" required />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="limit_num">{{ trans('model.goods.limit_num') }}</label>
                                <div class="col-md-4 input-group">
                                    <input class="form-control" id="limit_num" name="limit_num" type="number" value="0" required />
                                    <span class="input-group-text">{{ trans('admin.times') }}</span>
                                    <span class="text-help"> {{ trans('admin.goods.info.limit_num_hint') }} </span>
                                </div>
                            </div>
                            <div class="form-group row package-renew">
                                <label class="col-md-2 col-form-label" for="days">{{ trans('model.goods.available_date') }}</label>
                                <div class="col-md-4 input-group">
                                    <input class="form-control" id="days" name="days" type="number" value="30" />
                                    <span class="input-group-text">{{ trans_choice('common.days.attribute', 1) }}</span>
                                    <span class="text-help"> {{ trans('admin.goods.info.available_date_hint') }} </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="is_hot">{{ trans('model.goods.hot') }}</label>
                                <div class="col-md-10">
                                    <input id="is_hot" name="is_hot" data-toggle="switch" data-on-color="primary" data-off-color="default"
                                           data-on-text="{{ trans('admin.yes') }}" data-off-text="{{ trans('admin.no') }}" data-size="small" type="checkbox">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="status">{{ trans('common.status.attribute') }}</label>
                                <div class="col-md-10">
                                    <input id="status" name="status" data-toggle="switch" data-on-color="primary" data-off-color="default"
                                           data-on-text="{{ trans('admin.goods.status.yes') }}" data-off-text="{{ trans('admin.goods.status.no') }}"
                                           data-size="small" type="checkbox">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="sort">{{ trans('model.common.sort') }}</label>
                                <div class="col-md-4">
                                    <input class="form-control" id="sort" name="sort" type="number" value="0" min="0" max="255" />
                                    <span class="text-help"> {{ trans('admin.sort_asc') }} </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="color">{{ trans('model.goods.color') }}</label>
                                <div class="col-md-4">
                                    <input class="form-control" id="color" name="color" data-plugin="asColorPicker" data-mode="simple" type="text"
                                           value="#A57AFA" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="logo">{{ trans('model.goods.logo') }}</label>
                                <div class="col-md-6">
                                    <input id="logo" name="logo" data-plugin="dropify"
                                           data-default-file="{{ asset($good->logo ?? '/assets/images/default.png') }}" type="file" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="description">{{ trans('model.common.description') }}</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="{{ trans('admin.goods.info.desc_placeholder') }}"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label" for="info">{{ trans('model.goods.info') }}</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" id="info" name="info" rows="6" placeholder="{{ trans('admin.goods.info.list_placeholder') }}"></textarea>
                                    <span class="text-help"> {!! trans('admin.goods.info.list_hint') !!}</span>
                                </div>
                            </div>
                            <div class="form-actions col-12 text-right">
                                <button class="btn btn-success" type="submit">
                                    <i class="icon wb-check"></i>{{ trans('common.submit') }}</button>
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
        @isset($good)
            $(document).ready(function() {
                const type = $('input[name=\'type\']');
                $('#id').val('{{ $good->id }}');
                $("input[name='type'][value='{{ $good->type }}']").click();
                type.attr('disabled', true);
                $('#name').val('{{ $good->name }}');
                $('#price').val('{{ $good->price }}');
                $('#level').selectpicker('val', '{{ $good->level }}');
                @if ($good->type == 2)
                    $('#renew').val('{{ $good->renew }}');
                    $('#speed_limit').val('{{ $good->speed_limit }}');
                    $('#period').val('{{ $good->period }}');
                    $('#days').val('{{ $good->days }}').attr('disabled', true);
                @endif
                $('#invite_num').val('{{ $good->invite_num }}');
                $('#limit_num').val('{{ $good->limit_num }}');
                @if ($good->is_hot)
                    $('#is_hot').click();
                @endif
                @if ($good->status)
                    $('#status').click();
                @endif
                $('#sort').val('{{ $good->sort }}');
                $('#color').asColorPicker('val', '{{ $good->color }}');
                $('#description').val(@json($good->description));
                $('#info').val(@json($good->info));
                $('#category_id').selectpicker('val', '{{ $good->category_id }}');
                const trafficUnit = $('#traffic_unit');
                const traffic = $('#traffic');
                @if ($good->traffic >= 1073741824)
                    traffic.val('{{ $good->traffic / 1073741824 }}');
                    trafficUnit.selectpicker('val', '1073741824');
                @elseif ($good->traffic >= 1048576)
                    traffic.val('{{ $good->traffic / 1048576 }}');
                    trafficUnit.selectpicker('val', '1048576');
                @elseif ($good->traffic >= 1024)
                    traffic.val('{{ $good->traffic / 1024 }}');
                    trafficUnit.selectpicker('val', '1024');
                @else
                    traffic.val('{{ $good->traffic }}');
                @endif
                traffic.attr('disabled', true);
                trafficUnit.attr('disabled', true).selectpicker('refresh');
            });
        @elseif (old('type'))
            $(document).ready(function() {
                const type = $('input[name=\'type\']');
                $('#id').val('{{ old('id') }}');
                $("input[name='type'][value='{{ old('type') }}']").click();
                $('#name').val('{{ old('name') }}');
                $('#price').val('{{ old('price') }}');
                $('#level').selectpicker('val', '{{ old('level') }}');
                @if (old('type') == 2)
                    $('#renew').val('{{ old('renew', 0) }}');
                    $('#speed_limit').val('{{ old('speed_limit', 0) }}');
                    $('#period').val('{{ old('period', 0) }}');
                    $('#days').val('{{ old('days', 0) }}');
                @endif
                $('#traffic').val('{{ old('traffic') }}');
                $('#traffic_unit').selectpicker('val', '{{ old('traffic_unit') }}');
                $('#invite_num').val('{{ old('invite_num') }}');
                $('#limit_num').val('{{ old('limit_num') }}');
                @if (old('is_hot'))
                    $('#is_hot').click();
                @endif
                @if (old('status'))
                    $('#status').click();
                @endif
                $('#sort').val('{{ old('sort') }}');
                $('#color').asColorPicker('val', '{{ old('color') }}');
                $('#description').val('{{ old('description') }}');
                $('#info').val('{{ old('info') }}');
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
