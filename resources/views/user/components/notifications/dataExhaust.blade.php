<a class="list-group-item dropdown-item" href="javascript:void(0)" role="menuitem">
    <div class="media">
        <div class="pr-10">
            <i class="icon wb-pluse bg-red-600 white icon-circle" aria-hidden="true"></i>
        </div>
        <div class="media-body">
            <h6 class="media-heading text-break">
                {{trans('notification.traffic_remain', ['percent' => $notification->data['percent']])}}
            </h6>
            <time class="media-meta" datetime="{{$notification->created_at}}">{{$notification->created_at->diffForHumans()}}</time>
        </div>
    </div>
</a>