@extends('auth.layouts')
@section('title',\App\Components\Helpers::systemConfig()['website_name'])
@section('content')
	<h4 class="caption-subject font-dark bold">{{trans('home.free_invite_codes_title')}}</h4>
	<div class="table-responsive">
		<table class="table table-hover text-center">
			@if(\App\Components\Helpers::systemConfig()['is_invite_register'])
				@if(\App\Components\Helpers::systemConfig()['is_free_code'])
					<thead class="thead-default">
					<tr>
						<th> {{trans('home.invite_code_table_name')}} </th>
						<th> {{trans('home.invite_code_table_date')}} </th>
					</tr>
					</thead>
					<tbody>
					@if($inviteList->isEmpty())
						<tr>
							<td colspan="2">{{trans('home.invite_code_table_none_codes')}}</td>
						</tr>
					@else
						@foreach($inviteList as $invite)
							<tr>
								<td><a href="/register?code={{$invite->code}}" target="_blank">{{$invite->code}}</a></td>
								<td> {{$invite->dateline}} </td>
							</tr>
						@endforeach
					@endif
					</tbody>
				@else
					<tbody>
					<tr>
						<td colspan="2">{{trans('home.invite_code_table_none_codes')}}</td>
					</tr>
					</tbody>
				@endif
			@else
				<tbody>
				<tr>
					<td colspan="2">{{trans('home.no_need_invite_codes')}}</td>
				</tr>
				</tbody>
			@endif
		</table>
	</div>
	@if(\App\Components\Helpers::systemConfig()['is_invite_register'] && \App\Components\Helpers::systemConfig()['is_free_code'])
		<div class="mt-20">
			<button class="btn btn-danger btn-lg float-left" onclick="login()">{{trans('auth.back')}}</button>
			<nav class="Page navigation float-right">
				{{$inviteList->links()}}
			</nav>
		</div>
	@endif
@endsection
