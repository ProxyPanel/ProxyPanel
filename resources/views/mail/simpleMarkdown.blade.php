@component('mail::message')
    # {{$title}}

    {!! $content !!}

    @component('mail::button', ['url' => $url])
        {{trans('notification.view_web')}}
    @endcomponent
@endcomponent

