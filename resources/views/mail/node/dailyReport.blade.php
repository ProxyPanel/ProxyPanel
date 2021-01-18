@component('mail::message')
# {{__('Nodes Daily Report')}}
{!! $content !!}
@component('mail::button', ['url' => route('admin.node.index')])
    {{trans('notification.view_web')}}
@endcomponent
@endcomponent
