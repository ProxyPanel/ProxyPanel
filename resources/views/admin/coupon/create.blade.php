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
                    <a href="{{route('admin.coupon.index')}}" class="btn btn-danger">{{ trans('common.back') }}</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="{{route('admin.coupon.store')}}" method="post" enctype="multipart/form-data"
                      class="form-horizontal" role="form">@csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="name">{{ trans('model.coupon.name') }}</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control col-md-4" name="name" id="name" value="{{old('name')}}" required/>
                            <span class="text-help"> {{ trans('admin.coupon.name_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="sn">{{ trans('model.coupon.sn') }}</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control col-md-4" name="sn" id="sn" value="{{old('sn')}}"/>
                            <span class="text-help"> {{ trans('admin.coupon.sn_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="logo">{{ trans('model.coupon.logo') }}</label>
                        <div class="col-md-6">
                            <input type="file" id="logo" name="logo" data-plugin="dropify" data-default-file="{{asset('/assets/images/default.png')}}"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">{{ trans('model.common.type') }}</label>
                        <div class="col-md-10 align-items-center">
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="voucher" value="1" checked/>
                                <label for="voucher">{{ trans('admin.coupon.type.voucher') }}</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="discount" value="2"/>
                                <label for="discount">{{ trans('admin.coupon.type.discount') }}</label>
                            </div>
                            <div class="radio-custom radio-primary radio-inline">
                                <input type="radio" name="type" id="charge" value="3"/>
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
                                    <span class="input-group-text">{{array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]}}</span>
                                </div>
                                <input type="number" class="form-control col-md-3" min="1" name="value" id="value" value="{{old('value')}}" required/>
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
                                    <input type="number" class="form-control col-md-2" min="1" max="255" name="priority" id="priority" value="{{old('priority')}}"/>
                                </div>
                                <span class="text-help"> {{ trans('admin.coupon.priority_hint') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="usable_times">{{ trans('model.coupon.usable_times') }}</label>
                            <div class="col-md-4 input-group">
                                <input type="number" class="form-control" min="1" name="usable_times" id="usable_times" value="{{old('usable_times', 1)}}"/>
                                <span class="input-group-text">{{ trans('admin.times') }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="minimum">{{ trans('model.coupon.minimum') }}</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{array_column(config('common.currency'), 'symbol', 'code')[sysConfig('standard_currency')]}}</span>
                                    </div>
                                    <input type="number" class="form-control col-md-3" name="minimum" id="minimum" value="{{old('minimum')}}" step="0.01"/>
                                </div>
                                <span class="text-help"> {!! trans('admin.coupon.minimum_hint', ['num' => 'N']).' '.trans('admin.zero_unlimited_hint') !!} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="used">{{ trans('model.coupon.used') }}</label>
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="number" class="form-control col-md-3" name="used" id="used" value="{{old('used')}}" step="1"/>
                                    <span class="input-group-text">{{ trans('admin.times') }}</span>
                                </div>
                                <span class="text-help"> {!! trans('admin.coupon.used_hint', ['num' => 'N']).' '.trans('admin.zero_unlimited_hint') !!}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="levels" class="col-md-2 col-form-label">{{ trans('model.coupon.levels') }}</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="levels" name="levels[]" multiple>
                                    @foreach($levels as $key => $level)
                                        <option value="{{$key}}">{{$level}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.levels_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="groups" class="col-md-2 col-form-label">{{ trans('model.coupon.groups') }}</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="groups" name="groups[]" multiple>
                                    @foreach($userGroups as $key => $group)
                                        <option value="{{$key}}">{{$group}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.groups_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_whitelist">{{ trans('model.coupon.users_whitelist') }}</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-6" data-plugin="tokenfield" id="users_whitelist" name="users_whitelist" value="{{old('users_whitelist')}}" placeholder="{{ trans('admin.coupon.users_placeholder') }}"/>
                                <span class="text-help"> {{ trans('admin.coupon.user_whitelist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_blacklist">{{ trans('model.coupon.users_blacklist') }}</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-6" data-plugin="tokenfield" id="users_blacklist" name="users_blacklist" value="{{old('users_blacklist')}}" placeholder="{{ trans('admin.coupon.users_placeholder') }}"/>
                                <span class="text-help"> {{ trans('admin.coupon.users_blacklist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_whitelist">{{ trans('model.coupon.services_whitelist') }}</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-4" data-plugin="tokenfield" id="services_whitelist" name="services_whitelist" value="{{old('services_whitelist')}}" placeholder="{{ trans('admin.coupon.services_placeholder') }}"/>
                                <span class="text-help"> {{ trans('admin.coupon.services_whitelist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_blacklist">{{ trans('model.coupon.services_blacklist') }}</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control col-md-4" data-plugin="tokenfield" id="services_blacklist" name="services_blacklist" value="{{old('services_blacklist')}}" placeholder="{{ trans('admin.coupon.services_placeholder') }}"/>
                                <span class="text-help"> {{ trans('admin.coupon.services_blacklist_hint') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newbie" class="col-md-2 col-form-label">{{ trans('model.coupon.newbie') }}</label>
                            <div class="col-md-10">
                                <ul class="list-unstyled">
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="coupon" name="coupon" {{ old('coupon') ? 'checked' : '' }}/>
                                            <label for="coupon">{{ trans('admin.coupon.newbie.first_discount') }}</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="order" name="order" {{ old('order') ? 'checked' : '' }}/>
                                            <label for="order">{{ trans('admin.coupon.newbie.first_order') }}</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item pb-0 pl-0">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="days">{{ trans('admin.coupon.newbie.created_days') }}</label>
                                            </div>
                                            <input type="number" class="form-control col-md-3" name="days" id="days" value="{{old('days')}}"/>
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
                            <input type="number" class="form-control" name="num" id="num" value="{{old('num')}}" required/>
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
                            <input type="text" class="form-control" name="start_time" id="start_time" value="{{old('start_time') ?? date("Y-m-d")}}" required/>
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ trans('common.to') }}</span>
                            </div>
                            <label for="end_time"></label>
                            <input type="text" class="form-control" name="end_time" id="end_time" value="{{old('end_time') ?? date("Y-m-d",strtotime("+1 month"))}}" required/>
                        </div>
                    </div>
                    <div class="form-actions col-12 text-right">
                        <button type="submit" class="btn btn-success">{{ trans('common.submit') }}</button>
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
        @if(old())
        $(document).ready(function() {
          $("input[name='type'][value='{{old('type')}}']").click();
          $('#levels').selectpicker('val', @json(old('levels')));
          $('#groups').selectpicker('val', @json(old('groups')));
        });
        @endif

        $('.input-daterange>input').datepicker({
          format: 'yyyy-mm-dd',
        });

        $('input[name=\'type\']').change(function() {
          if ($(this).val() === '2') {
            $('.discount').show();
            $('.usage').show();
            $('#amount').hide();
            $('#value').attr('max', 99);
          } else if ($(this).val() === '3') {
            $('.discount').hide();
            $('.usage').hide();
            $('#amount').show();
            $('#value').removeAttr('max');
          } else {
            $('.discount').hide();
            $('.usage').show();
            $('#amount').show();
            $('#value').removeAttr('max');
          }
        });
    </script>
@endsection
