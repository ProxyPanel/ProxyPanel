<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)" title="Notifications"
       aria-expanded="false" data-animation="scale-up" role="button">
        <i class="icon wb-bell" aria-hidden="true"></i>
        @if ($unreadCount = auth()->user()->unreadNotifications->count())
            <span class="badge badge-pill badge-danger up">{{$unreadCount}}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-media" role="menu">
        <div class="dropdown-menu-header">
            <h5>{{trans('notification.attribute')}}</h5>
            @if ($unreadCount)
                <span class="badge badge-round badge-danger">{{trans_choice('notification.new', $unreadCount, ['num' => $unreadCount])}}</span>
            @endif
        </div>
        @if ($unreadCount)
            <div class="list-group">
                <div data-role="container">
                    <div data-role="content">
                        @foreach(tap(auth()->user()->unreadNotifications)->markAsRead() as $notification)
                            @include('user.components.notifications.'.Str::camel(class_basename($notification->type)))
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="list-group bg-grey-100">
                <div class="dropdown-item" role="menuitem">
                    <div class="media">
                        <div class="pr-10">
                            <i class="icon wb-inbox bg-grey-600 white icon-circle" aria-hidden="true"></i>
                        </div>
                        <div class="media-body">
                            <h6 class="media-heading">{{trans('notification.empty')}}</h6>

                            <time class="media-meta" datetime="{{now()}}">{{now()}}</time>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{--        <div class="dropdown-menu-footer">--}}
        {{--            <a class="dropdown-menu-footer-btn" href="javascript:void(0)" role="button">--}}
        {{--                <i class="icon wb-settings" aria-hidden="true"></i>--}}
        {{--            </a>--}}
        {{--            <a class="dropdown-item" href="javascript:void(0)" role="menuitem">--}}
        {{--                All notifications--}}
        {{--            </a>--}}
        {{--        </div>--}}
    </div>
</li>
