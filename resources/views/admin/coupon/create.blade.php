@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <x-ui.panel class="panel" :title="trans('admin.action.add_item', ['attribute' => trans('model.coupon.attribute')])">
            <x-slot:actions>
                <a class="btn btn-danger" href="{{ route('admin.coupon.index') }}">{{ trans('common.back') }}</a>
            </x-slot:actions>
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>
            <x-admin.form.container :route="route('admin.coupon.store')" enctype="true">
                <x-admin.form.input name="name" :label="trans('model.coupon.name')" :help="trans('admin.coupon.name_hint')" required />
                <x-admin.form.input name="sn" :label="trans('model.coupon.sn')" :help="trans('admin.coupon.sn_hint')" />
                <x-admin.form.input name="logo" type="file" :label="trans('model.coupon.logo')"
                                    attribute="data-plugin=dropify data-default-file={{ asset('/assets/images/default.png') }}" />
                <x-admin.form.radio-group name="type" :label="trans('model.common.type')" :options="[
                    1 => trans('admin.coupon.type.voucher'),
                    2 => trans('admin.coupon.type.discount'),
                    3 => trans('admin.coupon.type.charge'),
                ]" :help="trans('admin.coupon.type_hint')" />
                <x-admin.form.skeleton name="value" :label="trans('model.coupon.value')">
                    <div class="input-group">
                        <div class="input-group-prepend" id="amount">
                            <span class="input-group-text">{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}</span>
                        </div>
                        <input class="form-control col-md-3" id="value" name="value" type="number" min="1" required />
                        <span class="input-group-text discount" style="display: none;">%</span>
                    </div>
                    <span class="text-help discount" style="display: none;"> {{ trans('admin.coupon.value_hint') }}</span>
                </x-admin.form.skeleton>
                <div class="usage">
                    <x-admin.form.input name="priority" type="number" :label="trans('model.coupon.priority')" min="1" max="255" :help="trans('admin.coupon.priority_hint')" />
                    <x-admin.form.input-group name="usable_times" type="number" :label="trans('model.coupon.usable_times')" min="1" :append="trans('admin.times')" />
                    <hr />
                    <x-admin.form.input-group name="minimum" type="number" :label="trans('model.coupon.minimum')" step="0.01" :help="trans('admin.coupon.minimum_hint', ['num' => 'N']) . ' ' . trans('admin.zero_unlimited_hint')" :prepend="array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]" />
                    <x-admin.form.input-group name="used" type="number" :label="trans('model.coupon.used')" step="1" :help="trans('admin.coupon.used_hint', ['num' => 'N']) . ' ' . trans('admin.zero_unlimited_hint')" :append="trans('admin.times')" />
                    <x-admin.form.select name="levels" :label="trans('model.coupon.levels')" :options="$levels" :help="trans('admin.coupon.levels_hint')" multiple />
                    <x-admin.form.select name="groups" :label="trans('model.coupon.groups')" :options="$userGroups" :help="trans('admin.coupon.groups_hint')" multiple />
                    <x-admin.form.input name="users_whitelist" :label="trans('model.coupon.users_whitelist')" attribute="data-plugin=tokenfield" :placeholder="trans('admin.coupon.users_placeholder')" :help="trans('admin.coupon.user_whitelist_hint')" />
                    <x-admin.form.input name="users_blacklist" :label="trans('model.coupon.users_blacklist')" attribute="data-plugin=tokenfield" :placeholder="trans('admin.coupon.users_placeholder')" :help="trans('admin.coupon.users_blacklist_hint')" />
                    <x-admin.form.input name="services_whitelist" :label="trans('model.coupon.services_whitelist')" attribute="data-plugin=tokenfield" :placeholder="trans('admin.coupon.services_placeholder')" :help="trans('admin.coupon.services_whitelist_hint')" />
                    <x-admin.form.input name="services_blacklist" :label="trans('model.coupon.services_blacklist')" attribute="data-plugin=tokenfield" :placeholder="trans('admin.coupon.services_placeholder')" :help="trans('admin.coupon.services_blacklist_hint')" />
                    <x-admin.form.skeleton name="newbie" :label="trans('model.coupon.newbie')">
                        <ul class="list-unstyled">
                            <li class="list-group-item p-0">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="coupon" name="coupon" type="checkbox" />
                                    <label for="coupon">{{ trans('admin.coupon.newbie.first_discount') }}</label>
                                </div>
                            </li>
                            <li class="list-group-item p-0">
                                <div class="checkbox-custom checkbox-primary">
                                    <input id="order" name="order" type="checkbox" />
                                    <label for="order">{{ trans('admin.coupon.newbie.first_order') }}</label>
                                </div>
                            </li>
                            <li class="list-group-item pb-0 pl-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <label class="input-group-text" for="days">{{ trans('admin.coupon.newbie.created_days') }}</label>
                                    </div>
                                    <input class="form-control col-md-3" id="days" name="days" type="number" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ trans_choice('common.days.attribute', 0) }}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <span class="text-help">{!! trans('admin.coupon.limit_hint') !!}</span>
                    </x-admin.form.skeleton>
                    <hr />
                </div>
                <x-admin.form.input name="num" type="number" :label="trans('model.coupon.num')" required />
                <x-admin.form.date-range start_name="start_time" end_name="end_time" :label="trans('common.available_date')" required />
                <div class="form-actions col-12 text-right">
                    <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                </div>
            </x-admin.form.container>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
    <script>
        let couponData = {
            type: 1,
            usable_times: 1,
            start_time: "{{ date('Y-m-d') }}",
            end_time: "{{ date('Y-m-d', strtotime('+1 month')) }}"
        }

        @if (old())
            couponData = @json(old());
        @endif

        $(document).ready(function() {
            autoPopulateForm(couponData); // 填充表单数据
        });

        $('input[name="type"]').change(function() {
            const type = $(this).val();
            const isType2 = type === '2';
            const isType3 = type === '3';

            $('.discount').toggle(isType2);
            $('.usage').toggle(!isType3);
            $('#amount').toggle(!isType2);
            $('#value').attr('max', isType2 ? 99 : null);
        });
    </script>
@endsection
