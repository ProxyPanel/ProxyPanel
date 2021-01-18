<a class="list-group-item dropdown-item" href="javascript:void(0)" role="menuitem">
    <div class="media">
        <div class="pr-10">
            <i class="icon wb-order bg-primary-600 white icon-circle" aria-hidden="true"></i>
        </div>
        <div class="media-body">
            <h6 class="media-heading text-break">
                {{trans('notification.account_expired_blade', ['days' => $notification->data['days']])}}
            </h6>
            <time class="media-meta" datetime="{{$notification->created_at}}">{{$notification->created_at->diffForHumans()}}</time>
        </div>
    </div>
</a>