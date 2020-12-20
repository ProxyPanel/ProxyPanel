@extends('user.layouts')
@section('content')
    <div class="page-content container">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header white bg-cyan-600 p-30 clearfix">
                        <span class="avatar avatar-100 float-left mr-20">
                            <x-avatar :user="Auth::getUser()"/>
                        </span>
                        <div class="float-left">
                            <div class="font-size-20 mb-15">{{Auth::getUser()->username}}</div>
                            <p class="mb-5 text-nowrap"><i class="icon bd-webchat mr-10" aria-hidden="true"></i>
                                <span class="text-break">微信:
                                    @if(Auth::getUser()->wechat) {{Auth::getUser()->wechat}} @else未添加 @endif
                                </span>
                            </p>
                            <p class="mb-5 text-nowrap"><i class="icon bd-qq mr-10" aria-hidden="true"></i>
                                <span class="text-break">QQ:
                                    @if(Auth::getUser()->qq) {{Auth::getUser()->qq}} @else 未添加 @endif
                                </span>
                            </p>
                        </div>
                    </div>
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
                                <a class="active nav-link" data-toggle="tab" href="#tab_1" aria-controls="tab_1" role="tab">{{trans('home.password')}}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#tab_2" aria-controls="tab_2" role="tab">{{trans('home.contact')}}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-toggle="tab" href="#tab_3" aria-controls="tab_3" role="tab">{{trans('home.ssr_setting')}}</a>
                            </li>
                        </ul>
                        <div class="tab-content py-10">
                            <div class="tab-pane active animation-slide-left" id="tab_1" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal" autocomplete="off">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="old_password" class="col-md-2 col-form-label">{{trans('home.current_password')}}</label>
                                        <input type="password" class="form-control col-md-5 round" name="old_password" id="old_password" autofocus required/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="new_password" class="col-md-2  col-form-label">{{trans('home.new_password')}}</label>
                                        <input type="password" class="form-control col-md-5 round" name="new_password" id="new_password" required/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info">{{trans('home.submit')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="tab_2" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="username" class="col-md-2 col-form-label">{{trans('auth.username')}}</label>
                                        <input type="text" class="form-control col-md-5 round" name="username" id="username" value="{{Auth::getUser()->username}}"/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="wechat" class="col-md-2 col-form-label">{{trans('home.wechat')}}</label>
                                        <input type="text" class="form-control col-md-5 round" name="wechat" id="wechat" value="{{Auth::getUser()->wechat}}"/>
                                    </div>
                                    <div class="form-group row">
                                        <label for="qq" class="col-md-2 col-form-label">QQ</label>
                                        <input type="number" class="form-control col-md-5 round" name="qq" id="qq" value="{{Auth::getUser()->qq}}"/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info">{{trans('home.submit')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane animation-slide-left" id="tab_3" role="tabpanel">
                                <form action="{{route('profile')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="passwd" class="col-md-2 col-form-label"> {{trans('home.connection_password')}} </label>
                                        <input type="text" class="form-control col-md-5 round" name="passwd" id="passwd" value="{{Auth::getUser()->passwd}}" required/>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-info"> {{trans('home.submit')}} </button>
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
