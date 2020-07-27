@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/multi-select/multi-select.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">@isset($userGroup)编辑@else添加@endisset用戶分组</h2>
				<div class="panel-actions">
					<a href="/group" class="btn btn-danger">返 回</a>
				</div>
			</div>
			@if (Session::has('successMsg'))
				<div class="alert alert-success alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					{{Session::get('successMsg')}}
				</div>
			@endif
			@if($errors->any())
				<div class="alert alert-danger alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<div class="panel-body">
				<form action=@isset($userGroup){{url('/group/edit')}}@else{{url('/group/add')}}@endisset method="POST" enctype="multipart/form-data" class="form-horizontal">
					@isset($userGroup)<input name="id" value="{{$userGroup->id}}" hidden/>@endisset
					@csrf
					<div class="form-group row">
						<label class="col-md-2 col-sm-3 col-form-label" for="name">分组名称</label>
						<div class="col-md-9 col-sm-9">
							<input type="text" class="form-control" name="name" id="name"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-2 col-sm-3 col-form-label" for="nodes">选择节点</label>
						<div class="col-md-9 col-sm-9">
							<div class="btn-group mb-20">
								<button type="button" class="btn btn-primary" id="select-all">全 选</button>
								<button type="button" class="btn btn-danger" id="deselect-all">清 空</button>
							</div>
							<select class="form-control" name="nodes[]" id="nodes" data-plugin="multiSelect" multiple>
								@foreach($nodeList as $node)
									<option value="{{$node->id}}">{{$node->id . ' - ' . $node->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-actions text-right">
						<button type="submit" class="btn btn-success">提 交</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/multi-select/jquery.multi-select.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/multi-select.js"></script>
	<script src="/assets/global/js/jquery.quicksearch.js" type="text/javascript"></script>
	<script type="text/javascript">
		@isset($userGroup)
		$(document).ready(function () {
			$('#name').val('{{$userGroup->name}}');
			$('#nodes').multiSelect('select',{!! json_encode(explode(',', $userGroup->nodes)) !!});
		})
		@endisset
		// 权限列表
		$('#nodes').multiSelect({
			selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='待分配规则，此处可搜索'>",
			selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='已分配规则，此处可搜索'>",
			afterInit: function () {
				const that = this,
					$selectableSearch = that.$selectableUl.prev(),
					$selectionSearch = that.$selectionUl.prev(),
					selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
					selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

				that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectableUl.focus();
							return false;
						}
					});

				that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectionUl.focus();
							return false;
						}
					});
			},
			afterSelect: function () {
				this.qs1.cache();
				this.qs2.cache();
			},
			afterDeselect: function () {
				this.qs1.cache();
				this.qs2.cache();
			}
		});

		// 全选
		$('#select-all').click(function () {
			$('#node').multiSelect('select_all');
			return false;
		});

		// 反选
		$('#deselect-all').click(function () {
			$('#node').multiSelect('deselect_all');
			return false;
		});
	</script>
@endsection
