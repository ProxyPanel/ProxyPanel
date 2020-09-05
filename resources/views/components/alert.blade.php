<div {{ $attributes->merge(['class' => 'alert  alert-dismissible alert-'.$type]) }} role="alert">
	<button class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span>
	</button>
	{!! $message !!}
</div>