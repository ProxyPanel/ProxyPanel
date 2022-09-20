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
                <h1 class="panel-title">卡券信息</h1>
                <div class="panel-actions">
                    <a href="{{route('admin.coupon.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="name">卡券名称</label>
                    <div class="col-md-10">
                        <input class="form-control text-fit" id="name" value="{{$coupon->name}}" disabled/>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="sn">使用券码</label>
                    <div class="col-md-10">
                        <input type="text" class="form-control text-fit" id="sn" value="{{$coupon->sn}}" disabled/>
                    </div>
                </div>
                @if($coupon->logo)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label">卡券图片</span>
                        <div class="col-md-10">
                            <img src="{{asset($coupon->logo)}}" class="h-100" alt="优惠码logo"/>
                        </div>
                    </div>
                @endif
                <div class="form-group row">
                    <span class="col-md-2 col-form-label">类型</span>
                    <div class="col-md-10 align-items-center">
                        <div class="radio-custom radio-primary radio-inline">
                            <input type="radio" id="voucher" checked/>
                            <label for="voucher">
                                {{  ['未知卡券','抵用券','折扣券','充值券'][$coupon->type] }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="value">优惠额度</label>
                    <div class="col-md-10">
                        <p class="form-control text-fit">
                            @switch ($coupon->type)
                                @case(1)
                                    抵用 <code>{{$coupon->value}}</code> 元
                                    @break
                                @case(2)
                                    减 <code>{{$coupon->value}}</code> %
                                    @break
                                @case(3)
                                    充值 <code>{{$coupon->value}}</code> 元
                                    @break
                                @default
                                    未知卡券
                            @endswitch
                        </p>
                    </div>
                </div>
                @isset($coupon->priority)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label"> 权 重 </span>
                        <div class="col-md-10">
                            <span class="form-control text-fit"> {{$coupon->priority}} </span>
                        </div>
                    </div>
                @endisset
                @isset($coupon->usable_times)
                    <div class="form-group row">
                        <span class="col-md-2 col-form-label">剩余使用次数</span>
                        <div class="col-md-10">
                            <span class="form-control text-fit"><code>{{$coupon->usable_times}}</code> 次</span>
                        </div>
                    </div>
                @endisset
                @if(!empty($coupon->limit))
                    <hr>
                    @isset($coupon->limit['minimum'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="minimum">满减条件</label>
                            <div class="col-md-10">
                                <p class="form-control text-fit">当支付金额超过<strong> {{$coupon->limit['minimum']}}元</strong> 时，才能使用本优惠劵</p>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['used'])
                        <div class="form-group row">
                            <span class="col-md-2 col-form-label">个人限用</span>
                            <div class="col-md-10">
                                <p class="form-control text-fit">符合条件的用户可以使用本券 <strong>{{$coupon->limit['used']}}次</strong></p>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['levels'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="levels">等级限定</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="levels" multiple disabled>
                                    @foreach($levels as $key => $level)
                                        <option value="{{$key}}">{{$level}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> 以上用户等级，方可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['groups'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="groups">分组限定</label>
                            <div class="col-md-10">
                                <select data-plugin="selectpicker" data-style="btn-outline btn-primary" class="col-md-5 form-control show-tick" id="groups" multiple disabled>
                                    @foreach($userGroups as $key => $group)
                                        <option value="{{$key}}">{{$group}}</option>
                                    @endforeach
                                </select>
                                <span class="text-help"> 以上用户分组，方可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['white'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_whitelist">专属用户</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="users_whitelist" value="{{ implode(',', $coupon->limit['users']['white']) }}"
                                       disabled/>
                                <span class="text-help"> 以上用户均可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['black'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="users_blacklist">禁用用户</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="users_blacklist" value="{{ implode(',', $coupon->limit['users']['black']) }}"
                                       disabled/>
                                <span class="text-help"> 以上用户均不可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['services']['white'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_whitelist">许可商品</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="services_whitelist" value="{{ implode(',', $coupon->limit['services']['white']) }}"
                                       disabled/>
                                <span class="text-help"> 以上商品方可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['services']['black'])
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label" for="services_blacklist">禁用商品</label>
                            <div class="col-md-6">
                                <input class="form-control" data-plugin="tokenfield" id="services_blacklist" value="{{ implode(',', $coupon->limit['services']['black']) }}"
                                       disabled/>
                                <span class="text-help"> 以上商品不可使用本券</span>
                            </div>
                        </div>
                    @endisset
                    @isset($coupon->limit['users']['newbie'])
                        <div class="form-group row">
                            <label for="newbie" class="col-md-2 col-form-label">新人专属</label>
                            <div class="col-md-10">
                                <ul class="list-unstyled">
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="coupon" {{ isset($coupon->limit['users']['newbie']['coupon']) ? 'checked' : '' }} disabled/>
                                            <label for="coupon">首次用任意券</label>
                                        </div>
                                    </li>
                                    <li class="list-group-item p-0">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" id="order" {{ isset($coupon->limit['users']['newbie']['order']) ? 'checked' : '' }} disabled/>
                                            <label for="order">首单</label>
                                        </div>
                                    </li>
                                    @isset($coupon->limit['users']['newbie']['days'])
                                        <li class="list-group-item p-0">
                                            <span class="form-control text-fit">且 创号 <code>{{$coupon->limit['users']['newbie']['days']}}</code> 天</span>
                                        </li>
                                    @endisset
                                </ul>
                            </div>
                        </div>
                    @endisset
                    <hr>
                @endif
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">有效期</label>
                    <div class="col-md-6 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                        </div>
                        <span class="form-control"> {{$coupon->start_time}} </span>
                        <div class="input-group-prepend">
                            <span class="input-group-text">至</span>
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
