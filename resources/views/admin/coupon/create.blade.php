@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">{{ trans('admin.action.add_item', ['attribute' => trans('model.coupon.attribute')]) }}</h1>
                <div class="panel-actions">
                    <a class="btn btn-danger" href="{{ route('admin.coupon.index') }}">{{ trans('common.back') }}</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')" />
            @endif
            @if ($errors->any())
                <x-alert type="danger" :message="$errors->all()" />
            @endif
            <div class="panel-body">
                <form class="form-horizontal" role="form" action="{{ route('admin.coupon.store') }}" method="post" enctype="multipart/form-data">@csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="name">{{ trans('model.coupon.name') }}</label>
                        <div class="col-md-10">
                            <input class="form-control col-md-4" id="name" name="name" type="text" value="{{ old('name') }}" required />
                            <span class="text-help"> {{ trans('admin.coupon.name_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sn">{{ trans('model.coupon.sn') }}</label>
                        <div class="col-md-10">
                            <input class="form-control col-md-4" id="sn" name="sn" type="text" value="{{ old('sn') }}" />
                            <span class="text-help"> {{ trans('admin.coupon.sn_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="logo">{{ trans('model.coupon.logo') }}</label>
                        <div class="col-md-6">
                            <input id="logo" name="logo" data-plugin="dropify" data-default-file="{{ asset('/assets/images/default.png') }}"
                                   type="file" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">{{ trans('model.common.type') }}</label>
                        <div class="col-md-10 align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input id="voucher" name="type" type="radio" value="1" checked />
                                <label for="voucher">{{ trans('admin.coupon.type.voucher') }}</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input id="discount" name="type" type="radio" value="2" />
                                <label for="discount">{{ trans('admin.coupon.type.discount') }}</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input id="charge" name="type" type="radio" value="3" />
                                <label for="charge">{{ trans('admin.coupon.type.charge') }}</label>
                            </div>
                            <span class="text-help"> {{ trans('admin.coupon.type_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="value">{{ trans('model.coupon.value') }}</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <div class="input-group-prepend" id="amount">
                                    <span
                                          class="input-group-text">{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}</span>
                                </div>
                                <input class="form-control col-md-3" id="value" name="value" type="number" value="{{ old('value') }}" min="1"
                                       required />
                                <span class="input-group-text discount" style="display: none;">%</span>
                            </div>
                            <span class="text-help discount" style="display: none;"> {{ trans('admin.coupon.value_hint') }}</span>
                        </div>
                    </div>
                    <div class="usage">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="priority"> {{ trans('model.coupon.priority') }} </label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input class="form-control col-md-2" id="priority" name="priority" type="number" value="{{ old('priority') }}"
                                           min="1" max="255" />
                                </div>
                                <span class="text-help"> {{ trans('admin.coupon.priority_hint') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="usable_times">{{ trans('model.coupon.usable_times') }}</label>
                            <div class="col-md-4 input-group">
                                <input class="form-control" id="usable_times" name="usable_times" type="number" value="{{ old('usable_times', 1) }}"
                                       min="1" />
                                <span class="input-group-text">{{ trans('admin.times') }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="minimum">{{ trans('model.coupon.minimum') }}</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span
                                              class="input-group-text">{{ array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')] }}</span>
                                    </div>
                                    <input class="form-control col-md-3" id="minimum" name="minimum" type="number" value="{{ old('minimum') }}"
                                           step="0.01" />
                                </div>
                                <span class="text-help"> {!! trans('admin.coupon.minimum_hint', ['num' => 'N']) . ' ' . trans('admin.zero_unlimited_hint') !!} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="used">{{ trans('model.coupon.used') }}</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input class="form-control col-md-3" id="used" name="used" type="number" value="{{ old('used') }}"
                                           step="1" />
                                    <span class="input-group-text">{{ trans('admin.times') }}</span>
                                </div>
                                <span class="text-help"> {!! trans('admin.coupon.used_hint', ['num' => 'N']) . ' ' . trans('admin.zero_unlimited_hint') !!}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="levels">{{ trans('model.coupon.levels') }}</label>
                            <div class="col-md-10">
                                <select class="col-md-5 form-control show-tick" id="levels" name="levels[]" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" multiple>
                                    @foreach ($levels as $key => $level)
                                        <option value="{{ $key }}">{{ $level }}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.levels_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="groups">{{ trans('model.coupon.groups') }}</label>
                            <div class="col-md-10">
                                <select class="col-md-5 form-control show-tick" id="groups" name="groups[]" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" multiple>
                                    @foreach ($userGroups as $key => $group)
                                        <option value="{{ $key }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.groups_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_whitelist">{{ trans('model.coupon.users_whitelist') }}</label>
                            <div class="col-md-10">
                                <input class="form-control col-md-6" id="users_whitelist" name="users_whitelist" data-plugin="tokenfield" type="text"
                                       value="{{ old('users_whitelist') }}" placeholder="{{ trans('admin.coupon.users_placeholder') }}" />
                                <span class="text-help"> {{ trans('admin.coupon.user_whitelist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_blacklist">{{ trans('model.coupon.users_blacklist') }}</label>
                            <div class="col-md-10">
                                <input class="form-control col-md-6" id="users_blacklist" name="users_blacklist" data-plugin="tokenfield" type="text"
                                       value="{{ old('users_blacklist') }}" placeholder="{{ trans('admin.coupon.users_placeholder') }}" />
                                <span class="text-help"> {{ trans('admin.coupon.users_blacklist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_whitelist">{{ trans('model.coupon.services_whitelist') }}</label>
                            <div class="col-md-10">
                                <input class="form-control col-md-4" id="services_whitelist" name="services_whitelist" data-plugin="tokenfield" type="text"
                                       value="{{ old('services_whitelist') }}" placeholder="{{ trans('admin.coupon.services_placeholder') }}" />
                                <span class="text-help"> {{ trans('admin.coupon.services_whitelist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_blacklist">{{ trans('model.coupon.services_blacklist') }}</label>
                            <div class="col-md-10">
                                <input class="form-control col-md-4" id="services_blacklist" name="services_blacklist" data-plugin="tokenfield" type="text"
                                       value="{{ old('services_blacklist') }}" placeholder="{{ trans('admin.coupon.services_placeholder') }}" />
                                <span class="text-help"> {{ trans('admin.coupon.services_blacklist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="newbie">{{ trans('model.coupon.newbie') }}</label>
                            <div class="col-md-10">
                                <ul class="list-unstyled">
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input id="coupon" name="coupon" type="checkbox" {{ old('coupon') ? 'checked' : '' }} />
                                            <label for="coupon">{{ trans('admin.coupon.newbie.first_discount') }}</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input id="order" name="order" type="checkbox" {{ old('order') ? 'checked' : '' }} />
                                            <label for="order">{{ trans('admin.coupon.newbie.first_order') }}</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item pb-0 pl-0">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="days">{{ trans('admin.coupon.newbie.created_days') }}</label>
                                            </div>
                                            <input class="form-control col-md-3" id="days" name="days" type="number" value="{{ old('days') }}" />
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ trans_choice('common.days.attribute', 0) }}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <span class="text-help"> {!! trans('admin.coupon.limit_hint') !!} </span>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="num">{{ trans('model.coupon.num') }}</label>
                        <div class="col-md-4">
                            <input class="form-control" id="num" name="num" type="number" value="{{ old('num') }}" required />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">{{ trans('common.available_date') }}</label>
                        <div class="col-md-7 input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <label for="start_time"></label>
                            <input class="form-control" id="start_time" name="start_time" type="text" value="{{ old('start_time') ?? date('Y-m-d') }}"
                                   required />
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ trans('common.to') }}</span>
                            </div>
                            <label for="end_time"></label>
                            <input class="form-control" id="end_time" name="end_time" type="text"
                                   value="{{ old('end_time') ?? date('Y-m-d', strtotime('+1 month')) }}" required />
                        </div>
                    </div>
                    <div class="form-actions col-12 text-right">
                        <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
    <script src="/assets/global/js/Plugin/dropify.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
    <script>
        @if (old())
            $(document).ready(function() {
                $("input[name='type'][value='{{ old('type') }}']").click();
                $('#levels').selectpicker('val', @json(old('levels')));
                $('#groups').selectpicker('val', @json(old('groups')));
            });
        @endif

        $('.input-daterange>input').datepicker({
            format: 'yyyy-mm-dd',
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
