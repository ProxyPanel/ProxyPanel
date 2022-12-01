<div class="chat
@if (isset($ticket->admin_id) && $ticket->admin_id !== $user->id)
    chat-left
@elseif(isset($ticket->user_id) && !isset($ticket->admin_id)  && $ticket->user_id !== $user->id)
    chat-left
@endif">
    <div class="chat-avatar">
        <p class="avatar" data-toggle="tooltip" href="#" data-placement="right" title="" data-original-title="{{($ticket->admin ?? $ticket->user)->username}}">
            <img src="{{($ticket->admin ?? $ticket->user)->avatar}}" alt="{{trans('common.avatar')}}"/>
        </p>
    </div>
    <div class="chat-body">
        <div class="chat-content">
            <p>
                {!! $ticket->content !!}
            </p>
            <time class="chat-time" datetime="{{$ticket->created_at}}">
                {{($ticket->ticket && $ticket->ticket->status === 2) || $ticket->status === 2 ? $ticket->created_at : $ticket->created_at->diffForHumans()}}
            </time>
        </div>
    </div>
</div>
