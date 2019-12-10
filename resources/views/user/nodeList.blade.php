@extends('user.layouts')
@section('css')
	<script src="//at.alicdn.com/t/font_682457_e6aq10jsbq0yhkt9.js" type="text/javascript"></script>
	<link href="/assets/global/fonts/font-awesome/font-awesome.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/global/vendor/webui-popover/webui-popover.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<!-- BEGIN CONTENT BODY -->
	<div class="page-content container-fluid">
		<div class="row">
			@if(!$nodeList->isEmpty())
				@foreach($nodeList as $node)
					<div class="col-xxl-3 col-xl-4 col-sm-6">
						<div class="card card-inverse card-shadow bg-white">
							<div class="card-block p-30 row">
								<div class="col-4">
									<svg class="w-p100 text-center" aria-hidden="true">
										<use xlink:href="@if($node->country_code)#icon-{{$node->country_code}}@else #icon-un @endif"></use>
									</svg>
								</div>
								<div class="col-8 text-break text-right">
									<p class="font-size-20 blue-600">
										@if(!$node->online_status)
											<i class="red-600 icon wb-warning" data-content="线路不稳定/维护中" data-trigger="hover" data-toggle="popover" data-placement="top"></i>
										@endif
										@if($node->traffic_rate > 1)
											<i class="green-600 icon wb-info-circle" data-content="{{$node->traffic_rate}} 倍流量消耗" data-trigger="hover" data-toggle="popover" data-placement="top"></i>
										@endif
										<a data-toggle="modal" href="#txt_{{$node->id}}">{{$node->name}}</a>
										<span class="badge badge-pill font-size-10 up m-0 @if($node->labels->label_id == 1) badge-success @elseif($node->labels->label_id == 7) badge-danger @else badge-info @endif">{{$node->labels->labelInfo->name}}</span>
									</p>
									<blockquote>
										{{$node->desc}}
									</blockquote>
									<p class="font-size-14">
										<button class="btn btn-sm btn-outline-info" data-toggle="modal" href="#link_{{$node->id}}"><i class="icon fa-code"></i></button>
										<button class="btn btn-sm btn-outline-info" data-toggle="modal" href="#qrcode_{{$node->id}}"><i class="icon fa-qrcode"></i></button>
									</p>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			@endif
		</div>
	</div>

	@foreach ($nodeList as $node)
		<div class="modal fade draggable-modal" id="txt_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">{{trans('home.setting_info')}}</h4>
					</div>
					<div class="modal-body">
						<textarea class="form-control" rows="12" readonly="readonly">{{$node->txt}}</textarea>
					</div>
				</div>
			</div>
		</div>
		<!-- 配置链接 -->
		<div class="modal fade draggable-modal" id="link_{{$node->id}}" tabindex="-1" role="basic" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">{{$node->name}}</h4>
					</div>
					<div class="modal-body">
						@if($node->type == 1)
							@if($node->ss_scheme)
								<textarea class="form-control" rows="3" readonly="readonly">{{$node->ss_scheme}}</textarea>
								<a href="{{$node->ss_scheme}}" class="btn btn-danger btn-block mt-10">打开SS</a>
							@else
								<textarea class="form-control" rows="5" readonly="readonly">{{$node->ssr_scheme}}</textarea>
								<a href="{{$node->ssr_scheme}}" class="btn btn-danger btn-block mt-10">打开SSR</a>
							@endif
						@else
							@if($node->v2_scheme)
								<textarea class="form-control" rows="3" readonly="readonly">{{$node->v2_scheme}}</textarea>
								<a href="{{$node->v2_scheme}}" class="btn btn-danger btn-block mt-10">打开V2ray</a>
							@endif
						@endif
					</div>
				</div>
			</div>
		</div>
		<!-- 配置二维码 -->
		<div class="modal fade" id="qrcode_{{$node->id}}" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog @if($node->type == 2 || !$node->compatible) modal-sm @endif">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">{{trans('home.scan_qrcode')}}</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							@if ($node->type == 1)
								@if ($node->compatible)
									<div class="col-md-6">
										<div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
									</div>
									<div class="col-md-6">
										<div id="qrcode_ss_img_{{$node->id}}" style="text-align: center;"></div>
									</div>
								@else
									<div class="col-md-12">
										<div id="qrcode_ssr_img_{{$node->id}}" style="text-align: center;"></div>
									</div>
								@endif
							@else
								<div class="col-md-12">
									<div id="qrcode_v2_img_{{$node->id}}" style="text-align: center;"></div>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	@endforeach
@endsection @section('script')
	<script src="/assets/custom/Plugin/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/webui-popover.js" type="text/javascript"></script>

	<script type="text/javascript">
        const UIModals = function () {
            const n = function () {
				@foreach($nodeList as $node)
                $("#txt_{{$node->id}}").draggable({
                    handle: ".modal-header"
                });
                $("#qrcode_{{$node->id}}").draggable({
                    handle: ".modal-header"
                });
				@endforeach
            };

            return {
                init: function () {
                    n()
                }
            }
        }();

        jQuery(document).ready(function () {
            UIModals.init()
        });

        // 循环输出节点scheme用于生成二维码
		@foreach ($nodeList as $node)
		@if($node->type == 1)
        $('#qrcode_ssr_img_{{$node->id}}').qrcode("{{$node->ssr_scheme}}");
		@if($node->ss_scheme)
        $('#qrcode_ss_img_{{$node->id}}').qrcode("{{$node->ss_scheme}}");
		@endif
		@else
        $('#qrcode_v2_img_{{$node->id}}').qrcode("{{$node->v2_scheme}}");
		@endif
		@endforeach

	</script>
@endsection
