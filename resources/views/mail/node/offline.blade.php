@component('mail::message')
{!! $content !!}

@component('mail::button', ['url' => route('admin.node.index')])
{{trans('notification.view_web')}}
@endcomponent
@endcomponent

