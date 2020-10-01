<div class="chat @if(($ticket->admin && !$user->is_admin) ||(!$ticket->admin && $user->is_admin)) chat-left @endif">
    <div class="chat-avatar">
        <p class="avatar" data-toggle="tooltip" href="#" data-placement="right" title="" data-original-title="{{($ticket->admin ?? $ticket->user)->email}}">
            <x-avatar :user="$ticket->admin ?? $ticket->user"/>
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
