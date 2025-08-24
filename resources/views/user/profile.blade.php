@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <style>
        .line {
            height: 1px;
            border-top: 1px solid #ddd;
            text-align: center;
            padding-bottom: 15px;
        }

        .line span {
            position: relative;
            top: -8px;
            background: #fff;
            padding: 0 20px;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container">
        <div class="row">
            <div class="col-lg-5">
                <div class="user-info card card-shadow text-center">
                    <div class="user-base card-block">
                        <a class="avatar img-bordered avatar-100" href="javascript:void(0)">
                            <img data-uid="{{ auth()->user()->id }}" data-qq="{{ auth()->user()->qq }}" data-username="{{ auth()->user()->username }}" src=""
                                 alt="{{ trans('common.avatar') }}" loading="lazy" />
                        </a>
                        <h4 class="user-name">{{ auth()->user()->nickname }}</h4>
                        <p class="user-job">
                            <i class="fa-brands fa-weixin fa-lg mr-10" aria-hidden="true"></i>
                            {{ trans('model.user.wechat') }}: {{ auth()->user()->wechat ?? trans('common.none') }}
                        </p>
                        <p class="user-location"><i class="fa-brands fa-qq fa-lg mr-10" aria-hidden="true"></i> QQ:
                            @if (auth()->user()->qq)
                                {{ auth()->user()->qq }}
                            @else
                                {{ trans('common.none') }}
                            @endif
                        </p>
                    </div>
                    @if (sysConfig('oauth_path'))
                        <div class="line">
                            <span> {{ trans('user.oauth.bind_title') }} </span>
                        </div>
                        <div class="user-socials list-group-gap list-group-full row m-0">
                            @foreach (json_decode(sysConfig('oauth_path'), false) as $provider)
                                <a class="list-group-item offset-lg-1 col-lg-8 col-10 d-flex justify-content-around align-items-center"
                                   @if ($provider !== 'telegram') href="{{ route('oauth.route', ['provider' => $provider, 'operation' => 'bind']) }}" @endif>
                                    <span>
                                        <i class="fa-brands {{ config('common.oauth.icon')[$provider] }} fa-lg mr-2" aria-hidden="true"></i>
                                        {{ config('common.oauth.labels')[$provider] }}:
                                    </span>
                                    <span>
                                        @if (in_array($provider, $auth, true))
                                            <span class="text-danger">{{ trans('user.oauth.rebind') }}</span>
                                        @else
                                            <span class="text-muted">{{ trans('user.oauth.not_bind') }}</span>
                                        @endif
                                        @if ($provider === 'telegram')
                                            <script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="{{ config('services.telegram.bot') }}" data-size="medium"
                                                    data-userpic="false" data-auth-url="{{ route('oauth.bind', ['provider' => $provider]) }}" data-request-access="write"></script>
                                        @endif
                                    </span>
                                </a>
                                @if (in_array($provider, $auth, true))
                                    <a class="col-2 btn btn-danger btn-block my-auto"
                                       href="{{ route('oauth.unbind', ['provider' => $provider]) }}">{{ trans('user.oauth.unbind') }}</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="panel">
                    @if (Session::has('successMsg'))
                        <x-alert :message="Session::pull('successMsg')" />
                    @endif
                    @if ($errors->any())
                        <x-alert type="danger" :message="$errors->all()" />
                    @endif
                    <div class="panel-body nav-tabs-animate nav-tabs-horizontal" data-plugin="tabs">
                        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="active nav-link" data-toggle="tab" href="#account" role="tab"
                                   aria-controls="account">{{ ucfirst(trans('validation.attributes.password')) }}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#contact" role="tab" aria-controls="contact">{{ trans('user.contact') }}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#proxy" role="tab" aria-controls="proxy">{{ trans('user.node.setting') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content py-10">
                            <div class="tab-pane active animation-slide-left" id="account" role="tabpanel">
                                <form class="form-horizontal" action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data"
                                      autocomplete="off">
                                    @csrf
                                    <div class="form-group row">
                                        <label class="col-md-5 col-form-label" for="password">{{ trans('auth.password.original') }}</label>
                                        <input class="form-control col-md-6 round" id="password" name="password" type="password" autofocus required />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-5  col-form-label" for="new_password">{{ trans('auth.password.new') }}</label>
                                        <input class="form-control col-md-6 round" id="new_password" name="new_password" type="password" required />
                                    </div>
                                    <div class="form-actions">
                                        <button class="btn btn-info float-right" type="submit">{{ trans('common.submit') }}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="contact" role="tabpanel">
                                <form class="form-horizontal" action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group row">
                                        <label class="col-md-5 col-form-label" for="nickname">{{ trans('model.user.nickname') }}</label>
                                        <input class="form-control col-md-6 round" id="nickname" name="nickname" type="text"
                                               value="{{ auth()->user()->nickname }}" />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-5 col-form-label" for="wechat">{{ trans('model.user.wechat') }}</label>
                                        <input class="form-control col-md-6 round" id="wechat" name="wechat" type="text"
                                               value="{{ auth()->user()->wechat }}" />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-5 col-form-label" for="qq">{{ trans('model.user.qq') }}</label>
                                        <input class="form-control col-md-6 round" id="qq" name="qq" type="number"
                                               value="{{ auth()->user()->qq }}" />
                                    </div>
                                    <div class="form-actions">
                                        <button class="btn btn-info float-right" type="submit">{{ trans('common.submit') }}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="proxy" role="tabpanel">
                                <form class="form-horizontal" action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group row">
                                        <label class="col-md-5 col-form-label" for="passwd"> {{ trans('user.account.connect_password') }} </label>
                                        <input class="form-control col-md-5 round" id="passwd" name="passwd" type="text"
                                               value="{{ auth()->user()->passwd }}" required />
                                    </div>
                                    <div class="form-actions">
                                        <button class="btn btn-info float-right" type="submit"> {{ trans('common.submit') }} </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/custom/jump-tab.js"></script>
@endsection
