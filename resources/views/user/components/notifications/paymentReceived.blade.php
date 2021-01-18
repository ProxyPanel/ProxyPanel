<a class="list-group-item dropdown-item" href="{{route('invoiceInfo', $notification->data['sn'])}}" role="menuitem">
    <div class="media">
        <div class="pr-10">
            <i class="icon wb-order bg-primary-600 white icon-circle" aria-hidden="true"></i>
        </div>
        <div class="media-body">
            <h6 class="media-heading text-break">
                {{trans('notification.payment_received', ['order' => '#'.$notification->data['sn'], 'amount'=>$notification->data['amount']])}}
            </h6>
            <time class="media-meta" datetime="{{$notification->created_at}}">{{$notification->created_at->diffForHumans()}}</time>
        </div>
    </div>
</a>