@extends('user.layouts')
@section('content')
	<div class="page-content container">
		<div class="row">
			@if (Session::has('successMsg'))
				<div class="alert alert-success alert-dismissable">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					{{Session::get('successMsg')}}
				</div>
			@endif
			@if($errors->any())
				<div class="alert alert-danger alert-dismissable">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<strong>{{trans('home.error')}}：</strong> {{$errors->first()}}
				</div>
			@endif
			<div class="col-lg-5">
				<div class="card">
					<div class="card-header white bg-cyan-600 p-30 clearfix">
						<a class="avatar avatar-100 float-left mr-20" href="javascript:void(0)">
							<img src="/assets/images/astronaut.svg" alt="头像">
						</a>
						<div class="float-left">
							<div class="font-size-20 mb-15">{{Auth::user()->username}}</div>
							<p class="mb-5 text-nowrap"><i class="icon bd-webchat mr-10" aria-hidden="true"></i>
								<span class="text-break">@if(Auth::user()->wechat) {{Auth::user()->wechat}} @else 未添加 @endif</span>
							</p>
							<p class="mb-5 text-nowrap"><i class="icon bd-qq mr-10" aria-hidden="true"></i>
								<span class="text-break">@if(Auth::user()->qq) {{Auth::user()->qq}} @else 未添加 @endif</span>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-7">
				<div class="panel">
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
								<form action="/profile" method="post" enctype="multipart/form-data" class="form-horizontal" autocomplete="off">
									<div class="form-group row">
										<label for="old_password" class="col-md-2 col-form-label">{{trans('home.current_password')}}</label>
										<input type="password" class="form-control col-md-5 round" name="old_password" id="old_password" autofocus required/>
										{{csrf_field()}}
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
								<form action="/profile" method="post" enctype="multipart/form-data" class="form-horizontal">
									<div class="form-group row">
										<label for="wechat" class="col-md-2 col-form-label">{{trans('home.wechat')}}</label>
										<input type="text" class="form-control col-md-5 round" name="wechat" id="wechat" value="{{Auth::user()->wechat}}" required/>
										{{csrf_field()}}
									</div>
									<div class="form-group row">
										<label for="qq" class="col-md-2 col-form-label">QQ</label>
										<input type="number" class="form-control col-md-5 round" name="qq" id="qq" value="{{Auth::user()->qq}}" required/>
									</div>
									<div class="form-actions">
										<button type="submit" class="btn btn-info">{{trans('home.submit')}}</button>
									</div>
								</form>
							</div>
							<div class="tab-pane animation-slide-left" id="tab_3" role="tabpanel">
								<form action="/profile" method="post" enctype="multipart/form-data" class="form-horizontal">
									<div class="form-group row">
										<label for="passwd" class="col-md-2 col-form-label"> {{trans('home.connection_password')}} </label>
										<input type="text" class="form-control col-md-5 round" name="passwd" id="passwd" value="{{Auth::user()->passwd}}" required/>
										{{csrf_field()}}
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
@section('script')
	<script src="/assets/global/js/Plugin/tabs.js" type="text/javascript"></script>
@endsection
