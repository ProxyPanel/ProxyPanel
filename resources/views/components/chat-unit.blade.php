@php
    $isChatLeft =
        (isset($ticket->admin_id) && $ticket->admin_id !== $user->id) ||
        (isset($ticket->user_id) && !isset($ticket->admin_id) && $ticket->user_id !== $user->id);
    $chatUser = $ticket->admin ?? $ticket->user;
@endphp

<div class="chat {{ $isChatLeft ? 'chat-left' : '' }}">
    <div class="chat-avatar">
        <p class="avatar" data-toggle="tooltip" data-placement="right" data-original-title="{{ $chatUser->username }}" title="">
            <img data-uid="{{ $chatUser->id }}" data-qq="{{ $chatUser->qq }}" data-username="{{ $chatUser->username }}" src=""
                 alt="{{ trans('common.avatar') }}" loading="lazy" />
        </p>
    </div>
    <div class="chat-body">
        <div class="chat-content">
            <p>
                {!! $ticket->content !!}
            </p>
            <time class="chat-time" datetime="{{ $ticket->created_at }}">
                {{ ($ticket->ticket && $ticket->ticket->status === 2) || $ticket->status === 2 ? $ticket->created_at : $ticket->created_at->diffForHumans() }}
            </time>
        </div>
    </div>
</div>
