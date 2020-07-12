<div class="chat @if(($ticket->admin && !Auth::getUser()->is_admin) ||(!$ticket->admin && Auth::getUser()->is_admin)) chat-left @endif">
	<div class="chat-avatar">
		<p class="avatar" data-toggle="tooltip" href="#" data-placement="right" title="" data-original-title="{{($ticket->admin?:$ticket->user)->email}}">
			@component('components.avatar',['user' => $ticket->admin?:$ticket->user])@endcomponent
		</p>
	</div>
	<div class="chat-body">
		<div class="chat-content">
			<p>
				{!! $ticket->content !!}
			</p>
			<time class="chat-time">{{$ticket->created_at}}</time>
		</div>
	</div>
</div>