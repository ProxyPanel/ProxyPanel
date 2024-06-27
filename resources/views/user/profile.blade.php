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
                            <img src="{{Auth::getUser()->avatar}}" alt="{{trans('common.avatar')}}" />
                        </a>
                        <h4 class="user-name">{{Auth::getUser()->nickname}}</h4>
                        <p class="user-job"><i class="fa-brands fa-weixin fa-lg mr-10" aria-hidden="true"></i> {{trans('common.payment.wechat')}}：
                            @if(Auth::getUser()->wechat) {{Auth::getUser()->wechat}} @else {{trans('common.none')}} @endif</p>
                        <p class="user-location"><i class="fa-brands fa-qq fa-lg mr-10" aria-hidden="true"></i> QQ：
                            @if(Auth::getUser()->qq) {{Auth::getUser()->qq}} @else {{trans('common.none')}} @endif</p>
                    </div>
                    @if(sysConfig('oauth_path'))
                        <div class="line">
                            <span> {{trans('user.oauth.bind_title')}} </span>
                        </div>
                        <div class="user-socials list-group-gap list-group-full row m-0">
                            @foreach (json_decode(sysConfig('oauth_path')) as $item)
                                <a class="list-group-item justify-content-center @if(in_array($item, $auth)) col-10 @else col-12 @endif"
                                   @if($item !== 'telegram') href="{{route('oauth.route', ['type' => $item, 'action' => 'binding'])}}" @endif>
                                    <i class="fa-brands {{config('common.oauth.icon')[$item]}} fa-lg mr-10" aria-hidden="true"></i> {{config('common.oauth.labels')[$item]}} :
                                    @if(in_array($item, $auth))
                                        <span class="red-600">{{trans('user.oauth.rebind')}}</span>
                                    @else
                                        <span class="grey-500">{{trans('user.oauth.not_bind')}}</span>
                                    @endif
                                    @if($item === 'telegram')
                                        {!! Socialite::driver('telegram')->getButton() !!}
                                    @endif
                                </a>
                                @if(in_array($item, $auth))
                                    <a class="col-2 btn btn-block btn-danger my-auto" href="{{route('oauth.unsubscribe', ['type' => $item])}}">{{trans('user.oauth.unbind')}}</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="panel">
                    @if (Session::has('successMsg'))
                        <x-alert type="success" :message="Session::get('successMsg')"/>
                    @endif
                    @if($errors->any())
                        <x-alert type="danger" :message="$errors->all()"/>
                    @endif
                    <div class="panel-body nav-tabs-animate nav-tabs-horizontal" data-plugin="tabs">
                        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="active nav-link" data-toggle="tab" href="#tab_1" aria-controls="tab_1" role="tab">{{trans('validation.attributes.password')}}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#tab_2" aria-controls="tab_2" role="tab">{{trans('user.contact')}}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#tab_3" aria-controls="tab_3" role="tab">{{trans('user.node.setting')}}</a>
                            </li>
                        </ul>
                        <div class="tab-content py-10">
                            <div class="tab-pane active animation-slide-left" id="tab_1" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal" autocomplete="off">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="password" class="col-md-5 col-form-label">{{trans('auth.password.original')}}</label>
                                        <input type="password" class="form-control col-md-6 round" name="password" id="password" autofocus required/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="new_password" class="col-md-5  col-form-label">{{trans('auth.password.new')}}</label>
                                        <input type="password" class="form-control col-md-6 round" name="new_password" id="new_password" required/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info">{{trans('common.submit')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="tab_2" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="nickname" class="col-md-5 col-form-label">{{trans('validation.attributes.nickname')}}</label>
                                        <input type="text" class="form-control col-md-6 round" name="nickname" id="nickname" value="{{Auth::getUser()->nickname}}"/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="wechat" class="col-md-5 col-form-label">{{trans('common.payment.wechat')}}</label>
                                        <input type="text" class="form-control col-md-6 round" name="wechat" id="wechat" value="{{Auth::getUser()->wechat}}"/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="qq" class="col-md-5 col-form-label">QQ</label>
                                        <input type="number" class="form-control col-md-6 round" name="qq" id="qq" value="{{Auth::getUser()->qq}}"/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info">{{trans('common.submit')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="tab_3" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="passwd" class="col-md-5 col-form-label"> {{trans('user.account.connect_password')}} </label>
                                        <input type="text" class="form-control col-md-5 round" name="passwd" id="passwd" value="{{Auth::getUser()->passwd}}" required/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info"> {{trans('common.submit')}} </button>
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
