@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet">
    <style>
        .text-fit {
            width: fit-content;
            width: -moz-fit-content;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">{{ trans('admin.coupon.info_title') }}</h1>
                <div class="panel-actions">
                    <a href="{{route('admin.coupon.index')}}" class="btn btn-danger">{{ trans('common.back') }}</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="name">{{ trans('model.coupon.name') }}</label>
                    <div class="col-md-10">
                        <input class="form-control text-fit" id="name" value="{{$coupon->name}}" disabled/>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="sn">{{ trans('model.coupon.sn') }}</label>
                    <div class="col-md-10">
                        <input type="text" class="form-control text-fit" id="sn" value="{{$coupon->sn}}" disabled/>
                    </div>
                </div>
                @if($coupon->logo)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label">{{ trans('model.coupon.logo') }}</span>
                        <div class="col-md-10">
                            <img src="{{asset($coupon->logo)}}" class="h-100" alt="{{ trans('model.coupon.logo') }}"/>
                        </div>
                    </div>
                @endif
                <div class="form-group row">
                    <span class="col-md-2 col-form-label">{{ trans('model.common.type') }}</span>
                    <div class="col-md-10 align-items-center">
                        <div class="radio-custom radio-primary radio-inline">
                            <input type="radio" id="voucher" checked/>
                            <label for="voucher">
                                {{  [trans('common.status.unknown'), trans('admin.coupon.type.voucher') , trans('admin.coupon.type.discount'), trans('admin.coupon.type.charge')][$coupon->type] }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="value">{{ trans('model.coupon.value') }}</label>
                    <div class="col-md-10">
                        <p class="form-control text-fit">
                            {{ trans_choice('admin.coupon.value', $coupon->type, ['num' => $coupon->type === 2 ? $coupon->value : \App\Utils\Helpers::getPriceTag($coupon->value)]) }}
                        </p>
                    </div>
                </div>
                @isset($coupon->priority)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label"> {{ trans('model.coupon.priority') }} </span>
                        <div class="col-md-10">
                            <span class="form-control text-fit"> {{$coupon->priority}} </span>
                        </div>
                    </div>
                @endisset
                @isset($coupon->usable_times)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label">{{ trans('model.coupon.usable_times') }}</span>
                        <div class="col-md-10">
                            <span class="form-control text-fit"><code>{{$coupon->usable_times}}</code> {{ trans('admin.times') }}</span>
                        </div>
                    </div>
                @endisset
                @if(!empty($coupon->limit))
                    <hr>
                    @isset($coupon->limit['minimum'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="minimum">{{ trans('model.coupon.minimum') }}</label>
                            <div class="col-md-10">
                                <p class="form-control text-fit">{!! trans('admin.coupon.minimum_hint', ['num' => \App\Utils\Helpers::getPriceTag($coupon->limit['minimum'])]) !!}</p>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['used'])
                        <div class="form-group row">
                            <span class="col-md-2 col-form-label">{{ trans('model.coupon.used') }}</span>
                            <div class="col-md-10">
                                <p class="form-control text-fit">{!! trans('admin.coupon.used_hint', ['num' => $coupon->limit['used']]) !!}</p>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['levels'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="levels">{{ trans('model.coupon.levels') }}</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                        class="col-md-5 form-control show-tick" id="levels" multiple disabled>
                                    @foreach($levels as $key => $level)
                                        <option value="{{$key}}">{{$level}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.levels_hint') }}</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['groups'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="groups">{{ trans('model.coupon.groups') }}</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                        class="col-md-5 form-control show-tick" id="groups" multiple disabled>
                                    @foreach($userGroups as $key => $group)
                                        <option value="{{$key}}">{{$group}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> {{ trans('admin.coupon.groups_hint') }}</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['white'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="users_whitelist">{{ trans('model.coupon.users_whitelist') }}</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="users_whitelist"
                                       value="{{ implode(',', $coupon->limit['users']['white']) }}"
                                       disabled/>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['black'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="users_blacklist">{{ trans('model.coupon.users_blacklist') }}</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="users_blacklist"
                                       value="{{ implode(',', $coupon->limit['users']['black']) }}"
                                       disabled/>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['services']['white'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="services_whitelist">{{ trans('model.coupon.services_whitelist') }}</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="services_whitelist"
                                       value="{{ implode(',', $coupon->limit['services']['white']) }}"
                                       disabled/>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['services']['black'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label"
                                   for="services_blacklist">{{ trans('model.coupon.services_blacklist') }}</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="services_blacklist"
                                       value="{{ implode(',', $coupon->limit['services']['black']) }}"
                                       disabled/>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['newbie'])
                        <div class="form-group row">
                            <label for="newbie"
                                   class="col-md-2 col-form-label">{{ trans('model.coupon.newbie') }}</label>
                            <div class="col-md-10">
                                <ul class="list-unstyled">
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="coupon"
                                                   {{ isset($coupon->limit['users']['newbie']['coupon']) ? 'checked' : '' }} disabled/>
                                            <label for="coupon">{{ trans('admin.coupon.newbie.first_discount') }}</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="order"
                                                   {{ isset($coupon->limit['users']['newbie']['order']) ? 'checked' : '' }} disabled/>
                                            <label for="order">{{ trans('admin.coupon.newbie.first_order') }}</label>
                                        </div>
                                    </li>
                                    @isset($coupon->limit['users']['newbie']['days'])
                                        <li class="list-group-item p-0">
                                            <span class="form-control text-fit">{!! trans('admin.coupon.created_days_hint', ['days' => $coupon->limit['users']['newbie']['days']])
                                            !!}</span>
                                        </li>
                                    @endisset
                                </ul>
                            </div>
                        </div>
                    @endisset
                    <hr>
                @endif
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ trans('common.available_date') }}</label>
                    <div class="col-md-6 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                        </div>
                        <span class="form-control"> {{$coupon->start_time}} </span>
                        <div class="input-group-prepend">
                            <span class="input-group-text">{{ trans('common.to') }}</span>
                        </div>
                        <span class="form-control"> {{$coupon->end_time}} </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-tokenfield.js"></script>
    <script>
      $(document).ready(function() {
          @isset($coupon->limit['users']['levels'])
          $('#levels').selectpicker('val', @json($coupon->limit['users']['levels']));
          @endisset

          @isset($coupon->limit['users']['groups'])
          $('#groups').selectpicker('val', @json($coupon->limit['users']['groups']));
          @endisset
      });
    </script>
@endsection
