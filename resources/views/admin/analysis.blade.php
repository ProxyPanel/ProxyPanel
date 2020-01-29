@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">日志分析
					<small>仅适用于单机单节点</small>
				</h2>
			</div>
			@if (Session::has('analysisErrorMsg'))
				<div class="alert alert-danger" role="alert">
					<button class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span></button>
					{{Session::get('analysisErrorMsg')}}
				</div>
			@endif
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th>近期请求地址</th>
					</tr>
					</thead>
					<tbody>
					@if(empty($urlList))
						<tr>
							<td colspan="1">访问记录不足15000条，无法分析数据</td>
						</tr>
					@else
						@foreach($urlList as $url)
							<tr>
								<td> {{$url}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>

	<script type="text/javascript">
        const TableDatatablesScroller = function () {
            const e = function () {
                const e = $("#analysis");
                e.dataTable({
                    language: {
                        aria: {
                            sortAscending: ": activate to sort column ascending",
                            sortDescending: ": activate to sort column descending"
                        },
                        emptyTable: "暂无数据",
                        info: "第 _START_ 到 _END_ 条，共计 _TOTAL_ 条",
                        infoEmpty: "未找到",
                        infoFiltered: "(filtered1 from _MAX_ total entries)",
                        lengthMenu: "_MENU_ entries",
                        search: "搜索:",
                        zeroRecords: "未找到"
                    },
                    buttons: [
                        {extend: "print", className: "btn btn-outline-dark"},
                        {extend: "pdf", className: "btn btn-outline-success"},
                        {extend: "csv", className: "btn btn-outline-primary"}
                    ],
                    scrollY: 300,
                    deferRender: !0,
                    scroller: !0,
                    stateSave: !0,
                    order: [[0, "asc"]],
                    lengthMenu: [[10, 15, 20, -1], [10, 15, 20, "All"]],
                    pageLength: 20,
                    dom: "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>"
                })
            };
            return {
                init: function () {
                    jQuery().dataTable && (e())
                }
            }
        }();
        jQuery(document).ready(function () {
            TableDatatablesScroller.init()
        });

        $('#is_rand_port').on({
            'switchChange.bootstrapSwitch': function (event, state) {
                $.post("/admin/enableRandPort", {_token: '{{csrf_token()}}', value: state ? 1 : 0}, function (ret) {
                    console.log(ret);
                });
            }
        });
	</script>
@endsection