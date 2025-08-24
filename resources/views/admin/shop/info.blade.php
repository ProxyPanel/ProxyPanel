@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/custom/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/ascolorpicker/asColorPicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <x-ui.panel type="bordered" :title="trans(isset($good) ? 'admin.action.edit_item' : 'admin.action.add_item', ['attribute' => trans('model.goods.attribute')])" icon="wb-shopping-cart">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.goods.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>
            <x-admin.form.container :route="isset($good) ? route('admin.goods.update', $good) : route('admin.goods.store')" :method="isset($good) ? 'PUT' : 'POST'" enctype="true">
                <div class="form-row">
                    <div class="col-lg-6">
                        <x-admin.form.radio-group name="type" :label="trans('model.common.type')" :options="[1 => trans('admin.goods.type.package'), 2 => trans('admin.goods.type.plan')]" :help="trans('admin.goods.info.type_hint')" />
                        <x-admin.form.input name="name" :label="trans('model.goods.name')" required />
                        <x-admin.form.input-group name="price" type="number" :label="trans('model.goods.price')" :prepend="array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]" required attribute="min=0 step=0.1" />
                        <x-admin.form.select name="category_id" :label="trans('model.goods.category')" :options="$categories" />
                        <x-admin.form.select name="level" :label="trans('model.common.level')" :options="$levels" />
                        <div class="package-renew">
                            <x-admin.form.input-group name="renew" type="number" :label="trans('model.goods.renew')" :prepend="array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]" attribute="min=0 step=0.1" />
                            <x-admin.form.input-group name="speed_limit" type="number" :label="trans('model.goods.user_limit')" append="Mbps" :help="trans('admin.zero_unlimited_hint')" attribute="min=0" />
                            <x-admin.form.input-group name="period" type="number" :label="trans('model.goods.period')" :append="trans_choice('common.days.attribute', 1)" :help="trans('admin.goods.info.period_hint')"
                                                      attribute="min=0 step=1" />
                            <x-admin.form.input-group name="days" type="number" :label="trans('model.goods.available_date')" :append="trans_choice('common.days.attribute', 1)" :help="trans('admin.goods.info.available_date_hint')"
                                                      attribute="min=0 step=1" />
                        </div>
                        <x-admin.form.skeleton name="traffic" :label="trans('model.goods.traffic')">
                            <div class="input-group">
                                <input class="form-control" id="traffic" name="traffic" type="number" min="0" />
                                <select class="form-control" id="traffic_unit" name="traffic_unit" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                    <option value="1">MB</option>
                                    <option value="1024">GB</option>
                                    <option value="1048576">TB</option>
                                    <option value="1073741824">PB</option>
                                </select>
                            </div>
                        </x-admin.form.skeleton>
                        <x-admin.form.input name="invite_num" type="number" :label="trans('model.goods.invite_num')" attribute="min=0 step=1" />
                        <x-admin.form.input-group name="limit_num" type="number" :label="trans('model.goods.limit_num')" :append="trans('admin.times')" :help="trans('admin.goods.info.limit_num_hint')" required
                                                  attribute="min=0 step=1" />
                    </div>

                    <div class="col-lg-6">
                        <x-admin.form.skeleton name="is_hot" :label="trans('model.goods.hot')">
                            <input id="is_hot" name="is_hot" data-toggle="switch" data-on-color="primary" data-off-color="default"
                                   data-on-text="{{ trans('admin.yes') }}" data-off-text="{{ trans('admin.no') }}" data-size="small" type="checkbox">
                        </x-admin.form.skeleton>
                        <x-admin.form.skeleton name="status" :label="trans('common.status.attribute')">
                            <input id="status" name="status" data-toggle="switch" data-on-color="primary" data-off-color="default"
                                   data-on-text="{{ trans('admin.goods.status.yes') }}" data-off-text="{{ trans('admin.goods.status.no') }}" data-size="small"
                                   type="checkbox">
                        </x-admin.form.skeleton>
                        <x-admin.form.input name="sort" type="number" :label="trans('model.common.sort')" attribute="min=0 max=255 step=1" :help="trans('admin.sort_asc')" />
                        <x-admin.form.input name="color" :label="trans('model.goods.color')" attribute="data-plugin=asColorPicker data-mode=simple" />
                        <x-admin.form.input name="logo" type="file" :label="trans('model.goods.logo')"
                                            attribute="data-plugin=dropify data-default-file={{ asset($good->logo ?? '/assets/images/default.png') }}" />
                        <x-admin.form.textarea name="description" :label="trans('model.common.description')" rows="3" :placeholder="trans('admin.goods.info.desc_placeholder')" />
                        <x-admin.form.textarea name="info" :label="trans('model.goods.info')" rows="6" :placeholder="trans('admin.goods.info.list_placeholder')" :help="trans('admin.goods.info.list_hint')" />
                        <div class="form-actions col-12 text-right">
                            <button class="btn btn-success" type="submit">
                                <i class="icon wb-check"></i>{{ trans('common.submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
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
        // 初始化开关控件
        $("[data-toggle='switch']").bootstrapSwitch();

        // 商品类型切换处理
        $("input[name='type']").change(function() {
            $(".package-renew").toggle(parseInt($(this).val()) !== 1);
        });

        // 流量单位转换函数
        function getTrafficAndUnit(traffic) {
            for (let unit of [1073741824, 1048576, 1024]) {
                if (traffic >= unit) {
                    return {
                        "traffic": traffic / unit,
                        "traffic_unit": unit
                    };
                }
            }

            return {
                "traffic": traffic,
                "traffic_unit": 1
            };
        }

        $(document).ready(function() {
            // 默认商品数据
            let productData = {
                "type": 2,
                "speed_limit": 0,
                "period": 30,
                "traffic": 102400,
                "invite_num": 0,
                "limit_num": 0,
                "days": 0,
                "sort": 0,
                "color": "#a57afa",
                "status": 1
            };

            // 根据条件更新商品数据
            @isset($good)
                productData = @json($good);
            @endisset

            @if (old())
                productData = @json(old());
            @endif

            // 填充表单数据
            autoPopulateForm({
                ...productData,
                ...getTrafficAndUnit(productData.traffic || 0)
            });

            // 编辑模式下的特殊处理
            @isset($good)
                $("input[name='type']").attr("disabled", true);
                $("#traffic").attr("disabled", true);
                $("#traffic_unit").attr("disabled", true).selectpicker("refresh");
                if (productData.type === 2) $("#days").attr("disabled", true);
            @endisset
        });
    </script>
@endsection
