<div {{ $attributes->merge(['class' => 'alert  alert-dismissible alert-'.$type]) }} role="alert">
    <button class="close" data-dismiss="alert" aria-label="{{ trans('common.close') }}">
        <span aria-hidden="true">&times;</span><span class="sr-only">{{trans('common.close')}}</span>
    </button>
    @if(is_array($message))
        @if(count($message) > 1)
            <ul>
                @foreach($message as $data)
                    <li>{!! $data !!}</li>
                @endforeach
            </ul>
        @else
            {!! $message[0]!!}
        @endif
    @else
        {!! $message !!}
    @endif
</div>