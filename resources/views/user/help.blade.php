@extends('user.layouts')
@section('css')
	<link href="/assets/global/fonts/font-awesome/font-awesome.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/fonts/material-design/material-design.min.css" type="text/css" rel="stylesheet">
	<style type="text/css">
		ol > li {
			margin-bottom: 8px;
		}
	</style>
@endsection
@section('content')
	<div class="page-header">
		<h1 class="page-title">问题解决库</h1>
	</div>
	<div class="page-content container-fluid">
		<div class="row">
			<div class="col-xxl-2 col-lg-3 col-md-12">
				<!-- Panel -->
				<div class="panel">
					<div class="panel-body">
						<div class="list-group faq-list" role="tablist">
							<a class="list-group-item list-group-item-action active" data-toggle="tab" href="#category-1" aria-controls="category-1" role="tab">使用&下载</a>
							<a class="list-group-item" data-toggle="tab" href="#category-3" aria-controls="category-3" role="tab">账号&服务</a>
							<a class="list-group-item" data-toggle="tab" href="#category-2" aria-controls="category-2" role="tab">面板相关</a>
						</div>
					</div>
				</div>
				<!-- End Panel -->
			</div>
			<div class="col-xxl-10 col-lg-9 col-md-12">
				<!-- Panel -->
				<div class="panel">
					<div class="panel-body">
						<div class="tab-content">
							<div class="tab-pane animation-fade active" id="category-1" role="tabpanel">
								<div class="panel-group panel-group-simple panel-group-continuous" id="accordion1" aria-multiselectable="true" role="tablist">
									<div class="panel">
										<div class="panel-heading" id="question-1" role="tab">
											<a class="panel-title cyan-600" aria-controls="answer-1" aria-expanded="true" data-toggle="collapse" href="#answer-1" data-parent="#accordion1"><i class="icon wb-link" aria-hidden="true"></i>{{trans('home.subscribe_link')}}
											</a>
										</div>
										<div class="panel-collapse collapse show" id="answer-1" aria-labelledby="question-1" role="tabpanel">
											<div class="panel-body">
												@if($subscribe_status)
													<div class="alert alert-warning" role="alert">
														<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
														{{trans('home.subscribe_warning')}}
													</div>
													<div class="input-group">
														<input type="text" class="form-control" value="{{$link}}"/>
														<span class="input-group-btn btn-group" role="group">
                                                            <button class="btn btn-outline-info" onclick="exchangeSubscribe();">
                                                                <i class="icon wb-refresh" aria-hidden="true"></i>
                                                                {{trans('home.exchange_subscribe')}}</button>
                                                            <button class="btn btn-outline-info mt-clipboard" data-clipboard-action="copy" data-clipboard-text="{{$link}}">
                                                                <i class="icon wb-copy" aria-hidden="true"></i>
                                                                {{trans('home.copy_subscribe_address')}}</button>
                                                        </span>
													</div>
												@else
													<div class="alert alert-danger alert-dismissible" role="alert">
														<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
														{{trans('home.subscribe_baned')}}
													</div>
												@endif
											</div>
										</div>
									</div>
									<div class="panel">
										<div class="panel-heading" id="question-2" role="tab">
											<a class="panel-title" aria-controls="answer-2" aria-expanded="true" data-toggle="collapse" href="#answer-2" data-parent="#accordion1"><i class="icon md-help-outline" aria-hidden="true"></i>客户端 下载与使用教程
											</a>
										</div>
										<div class="panel-collapse collapse show" id="answer-2" aria-labelledby="question-2" role="tabpanel">
											<div class="panel-body">
												<div class="nav-tabs-horizontal" data-plugin="tabs">
													<ul class="nav nav-tabs nav-tabs-line mr-25" role="tablist">
														<li class="nav-item" role="presentation">
															<a class="nav-link active" data-toggle="tab" href="#android_client" aria-controls="android_client" role="tab" aria-expanded="true">
																<i class="icon fa-android" aria-hidden="true"></i>Android</a>
														</li>
														<li class="nav-item" role="presentation">
															<a class="nav-link" data-toggle="tab" href="#shadowrocket_client" aria-controls="shadowrocket_client" role="tab">
																<i class="icon fa-apple" aria-hidden="true"></i>iOS-小火箭<span class="badge badge-danger up">推荐</span></a>
														</li>
													</ul>
													<div class="tab-content py-15">
														<div class="tab-pane active" id="android_client" role="tabpanel">
															<ol>
																<li>点击<code>添加订阅地址</code> ->
																	<button class="btn btn-xs btn-animate btn-animate-side btn-info mt-clipboard" data-clipboard-action="copy" data-clipboard-text="{{$link}}"><span><i class="icon wb-copy" aria-hidden="true"></i>点击复制订阅地址</span></button>
																	-> 粘贴进输入框 ->
																	<code>确定</code> -> <code>确定并更新</code></li>
															</ol>
														</div>
														<div class="tab-pane" id="shadowrocket_client" role="tabpanel">
															<ol>
																<li>
																	<a class="btn btn-xs btn-primary" href="{{$Shadowrocket_install}}"><i class="icon fa-hand-o-right" aria-hidden="true"></i> 点此下载客户端
																		<i class="icon fa-hand-o-left" aria-hidden="true"></i></a> -> 弹窗选<code>安装</code> -> 等待下载 -> 启动
																</li>
																<li>返回手机主界面 -> 等待Shadowrocket下载/安装完毕 -> 进入软件</li>
																@if (\App\Components\Helpers::systemConfig()['AppStore_id'] && \App\Components\Helpers::systemConfig()['AppStore_password'])
																	<div class="alert alert-info" role="alert" style="width: fit-content">
																		@if($not_paying_user)

																		@else
																			<i class="wb-unlock" aria-hidden="true"></i> 首次安装软件可能会需要苹果ID激活软件，请输入以下账号信息：
																			<br>
																			账号（Apple ID）：
																			<code id="accountId">{{\App\Components\Helpers::systemConfig()['AppStore_id']}}</code>
																			<button class="btn btn-xs btn-primary mt-clipboard" data-clipboard-target="#accountId" data-clipboard-action="copy"><i class="wb-copy" aria-hidden="true"></i></button>
																			<br> 密码：
																			<code id="accountPasswd">{{\App\Components\Helpers::systemConfig()['AppStore_password']}}</code>
																			<button class="btn btn-xs btn-primary mt-clipboard" data-clipboard-target="#accountPasswd" data-clipboard-action="copy"><i class="wb-copy" aria-hidden="true"></i></button>
																		@endif
																	</div>
																@endif
															</ol>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="panel">
										<div class="panel-heading" id="question-3" role="tab">
											<a class="panel-title" aria-controls="answer-3" aria-expanded="false" data-toggle="collapse" href="#answer-3" data-parent="#accordion1"><i class="icon wb-extension" aria-hidden="true"></i>客户端 相关问题解决
											</a>
										</div>
										<div class="panel-collapse collapse" id="answer-3" aria-labelledby="question-3" role="tabpanel">
											<div class="panel-body">
												<div class="nav-tabs-horizontal" data-plugin="tabs">
													<ul class="nav nav-tabs" role="tablist">
														<li class="nav-item" role="presentation">
															<a class="nav-link active" data-toggle="tab" href="#android" aria-controls="android" role="tab">
																<i class="icon fa-android" aria-hidden="true"></i>Android</a>
														</li>
														<li class="nav-item" role="presentation">
															<a class="nav-link" data-toggle="tab" href="#ios" aria-controls="ios" role="tab">
																<i class="icon fa-apple" aria-hidden="true"></i>iOS</a>
														</li>
														<li class="nav-item" role="presentation">
															<a class="nav-link" data-toggle="tab" href="#Windows" aria-controls="Windows" role="tab">
																<i class="icon fa-windows" aria-hidden="true"></i>Windows</a>
														</li>
														<li class="nav-item" role="presentation">
															<a class="nav-link" data-toggle="tab" href="#MacOS" aria-controls="MacOS" role="tab">
																<i class="icon fa-apple" aria-hidden="true"></i>MacOS</a>
														</li>
													</ul>
													<div class="tab-content pt-20">
														<div class="tab-pane active" id="android" role="tabpanel">
															暂无
														</div>
														<div class="tab-pane" id="ios" role="tabpanel">
															<h4>Q: 软件安装后，闪退？</h4>
															<p>A: 闪退的情况大概分以下三种</p>
															<ol>
																<li>安装完成后，一打开就秒退。
																	<br> 解决办法：登陆网站上提供的对应ID，先在苹果商店搜索安装最新版，运行一次后，在通过网站直接覆盖安装。
																</li>
																<li>打开软件，且弹出ID验证框，输入ID后闪退。
																	<br> 解决办法：同上。
																</li>
																<li>打开软件，运行一段时间后闪退。
																	<br> 这类问题，一般是因为软件的适配性引起的，基本无解。
																</li>
															</ol>
															<h4>Q: 软件安装失败？</h4>
															<p>A: 出现此类问题，绝大多数是你的网络不好，更换个网络即可，最好使用数据流量进行下载。</p>
														</div>
														<div class="tab-pane" id="Windows" role="tabpanel">
															{{--                                                            <h4>Q: PAC 更新失败</h4>--}}
															{{--                                                            <p>A: 这个情况都是PC链接访问网站不稳定造成的！--}}
															{{--                                                                <br>--}}
															{{--                                                                解决方案： 手动下载--}}
															{{--                                                            </p>--}}
															暂无
														</div>
														<div class="tab-pane" id="MacOS" role="tabpanel">
															暂无
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane animation-fade" id="category-2" role="tabpanel">
								<div class="panel-group panel-group-simple panel-group-continuous" id="accordion2" aria-multiselectable="true" role="tablist">
									<!-- Question 1 -->
									<div class="panel">
										<div class="panel-heading" id="question-1" role="tab">
											<a class="panel-title" aria-controls="answer-1" aria-expanded="true" data-toggle="collapse" href="#answer-1" data-parent="#accordion2">
												面板菜单介绍
											</a>
										</div>
										<div class="panel-collapse collapse show" id="answer-1" aria-labelledby="question-1" role="tabpanel">
											<div class="panel-body">
												<button class="site-tour-trigger btn btn-outline-info">点我激活介绍功能</button>
											</div>
										</div>
									</div>
									<!-- End Question 1 -->

									<!-- Question 2 -->
									<div class="panel">
										<div class="panel-heading" id="question-2" role="tab">
											<a class="panel-title" aria-controls="answer-2" aria-expanded="false" data-toggle="collapse" href="#answer-2" data-parent="#accordion2">
												我想续费/购买服务，该怎么操作？
											</a>
										</div>
										<div class="panel-collapse collapse" id="answer-2" aria-labelledby="question-2" role="tabpanel">
											<div class="panel-body">
												<ol>
													<li>在线支付，本支付方式支持支付宝。支付后即开即用。前往<a href="/services">【{{trans('home.services')}}】</a>选择想要购买的套餐，在订单界面选择<code>在线支付</code>即可。
													</li>
													<li>余额支付，本支付方法支持微信，支付宝。支付后需要等待充值到账，再购买服务。
														，充值后等待充值到账，一般会在<code>24小时</code>内到账，到账后可以在<a href="/services">【{{trans('home.services')}}】</a> 页面查看您的账号余额。 在<a href="/services">【{{trans('home.services')}}】</a>选择想要购买的套餐，在订单界面选择<code>余额支付</code>即可。
													</li>
												</ol>
											</div>
										</div>
									</div>
									<!-- End Question 2 -->

									<!-- Question 3 -->
									<div class="panel">
										<div class="panel-heading" id="question-3" role="tab">
											<a class="panel-title" aria-controls="answer-3" aria-expanded="false" data-toggle="collapse" href="#answer-3" data-parent="#accordion2">
												怎么样才能快速的联系上客服？
											</a>
										</div>
										<div class="panel-collapse collapse" id="answer-3" aria-labelledby="question-3" role="tabpanel">
											<div class="panel-body">
												<blockquote class="blockquote custom-blockquote blockquote-warning">请选择其一种方式联系客服，请勿重复发送请求!!!</blockquote>
												<ol>
													<li>在<a href="/tickets">【{{trans('home.tickets')}}】</a>界面，创建新的工单，客服人员在上线后会在第一时刻处理。
													</li>
												</ol>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane animation-fade" id="category-3" role="tabpanel">
								<div class="panel-group panel-group-simple panel-group-continuous" id="accordion" aria-multiselectable="true" role="tablist">
									<!-- Question 5 -->
									<div class="panel">
										<div class="panel-heading" id="question-5" role="tab">
											<a class="panel-title" aria-controls="answer-5" aria-expanded="true" data-toggle="collapse" href="#answer-5" data-parent="#accordion">
												不运行软件，就连不上网，怎么办？
											</a>
										</div>
										<div class="panel-collapse collapse show" id="answer-5" aria-labelledby="question-5" role="tabpanel">
											<div class="panel-body">
												<ol>
													<li>
														电脑有安装任何电脑管家类的软件，都可以使用他们自带的网络修复工具来重置网络。
													</li>
													<li>
														<ol>
															<li>
																键盘操作<code>Win</code> + <code>X</code>，或右击左下角开始菜单键 （Win键看起来像 <i class="icon fa-windows" aria-hidden="true"></i> 这样）
															</li>
															<li>
																按下 <code>A</code>键 或者 手动选择
																<code>命令提示符（管理员）/ Windows PowerShell(管理员)</code>
															</li>
															<li>
																输入<code>Netsh winsock reset</code> 后回车，再输入 <code>netsh advfirewall reset</code> 后回车；
															</li>
														</ol>
													</li>
												</ol>
											</div>
										</div>
									</div>
									<!-- End Question 5 -->

									<!-- Question 6 -->
									<div class="panel">
										<div class="panel-heading" id="question-6" role="tab">
											<a class="panel-title" aria-controls="answer-6" aria-expanded="false" data-toggle="collapse" href="#answer-6" data-parent="#accordion">
												为什么我的账号状态显示是<span class="red-700">禁用</span>?
											</a>
										</div>
										<div class="panel-collapse collapse" id="answer-6" aria-labelledby="question-6" role="tabpanel">
											<div class="panel-body">
												账号在2种情况下会显示禁用；
												<ol>
													<li>
														套餐过期/流量枯竭；此情况您需要重新<a href="/services">【{{trans('home.services')}}】</a>；
													</li>
													<li>
														近期流量使用异常；在<code>1小时</code>内使用流量超过
														<code>{{\App\Components\Helpers::systemConfig()['traffic_ban_value']}}GB</code> ，即会触发本站的流量异常保护；保护时长为<code>{{\App\Components\Helpers::systemConfig()['traffic_ban_time']}}分钟</code>
													</li>
												</ol>
												如您对禁用情况有疑问，可以创建<a href="/tickets">【{{trans('home.tickets')}}】</a>，联系售后人员。
											</div>
										</div>
									</div>
									<!-- End Question 6 -->

									<!-- Question 7 -->
									<div class="panel">
										<div class="panel-heading" id="question-7" role="tab">
											<a class="panel-title" aria-controls="answer-7" aria-expanded="false" data-toggle="collapse" href="#answer-7" data-parent="#accordion">
												为什么我的订阅链接被禁用了？
											</a>
										</div>
										<div class="panel-collapse collapse" id="answer-7" aria-labelledby="question-7" role="tabpanel">
											<div class="panel-body">
												订阅地址对于账号来说非常重要。所以本站对此设置了严格的限制措施，以防止用户无意间泄露给他人后，无法挽回。
												<p>限制为：
													<code>24小时</code>内，订阅地址只允许请求<code>{{\App\Components\Helpers::systemConfig()['subscribe_ban_times']}}次</code>
												</p>
												<p>解封，请在过一段时间并确定无误后，创建<a href="/tickets">【{{trans('home.tickets')}}】</a>，联系售后人员
												</p>
												<p>小知识：如果您无意间的截图忘记将订阅地址打码了，您可以
													<button class="btn btn-sm btn-outline-info" onclick="exchangeSubscribe();">
														<i class="icon wb-refresh" aria-hidden="true"></i>
														点这里
													</button>
													更换链接
												</p>
											</div>
										</div>
									</div>
									<!-- End Question 7 -->
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Panel -->
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/responsive-tabs.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/tabs.js" type="text/javascript"></script>
	<script type="text/javascript">
        // 更换订阅地址
        function exchangeSubscribe() {
            swal.fire({
                title: '警告',
                text: '更换订阅地址将导致:\n1.旧地址立即失效\n2.连接密码被更改',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('home.ticket_close')}}',
                confirmButtonText: '{{trans('home.ticket_confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.post("/exchangeSubscribe", {_token: '{{csrf_token()}}'}, function (ret) {
                        if (ret.status === 'success') {
                            swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
                                .then(() => window.location.reload())
                        } else {
                            swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
                        }
                    });
                }
            });
        }

        const clipboard = new ClipboardJS('.mt-clipboard');
        clipboard.on('success', function () {
            swal.fire({
                title: '复制成功',
                type: 'success',
                timer: 1300,
                showConfirmButton: false
            });
        });
        clipboard.on('error', function () {
            swal.fire({
                title: '复制失败，请手动复制',
                type: 'error',
                timer: 1500,
                showConfirmButton: false
            });
        });
	</script>
@endsection
