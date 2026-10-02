@props([
    'type' => null,
    'bordered' => false,
    'icon' => null,
    'title' => null,
    'subtitle' => null,
    'title_level' => 2,
    'title_class' => null,
    'body_class' => 'mt-lg-15',
    'footer_class' => null,
    'footer_in_body' => false,
    'actions' => null,
    'alert' => null,
    'footer' => null,
])

@php
    // 标题层级只允许数字，避免把外部输入拼进标签名
    $level = (int) $title_level > 0 ? (int) $title_level : 2;
@endphp

<div class="panel{{ $type ? ' panel-'.$type : '' }}{{ $bordered ? ' panel-bordered' : '' }}">
    <div class="panel-heading">
        <h{{ $level }} class="panel-title{{ $title_class ? ' '.$title_class : '' }}">
            @if ($icon)
                <i class="icon {{ $icon }}" aria-hidden="true"></i>
            @endif
            @isset($heading)
                {!! $heading !!}
            @else
                {{ $title }}
            @endisset
            @if ($subtitle)
                <small>{{ $subtitle }}</small>
            @endif
        </h{{ $level }}>

        @if ($actions)
            <div class="panel-actions">
                {{ $actions }}
            </div>
        @endif
    </div>

    @if ($alert)
        {!! $alert !!}
    @endif

    @if ($footer && $footer_in_body)
        <div class="panel-body{{ $body_class ? ' '.$body_class : '' }}">
            {{ $slot }}
            <div class="panel-footer{{ $footer_class ? ' '.$footer_class : '' }}">
                {{ $footer }}
            </div>
        </div>
    @else
        <div class="panel-body{{ $body_class ? ' '.$body_class : '' }}">
            {{ $slot }}
        </div>

        @if ($footer)
            <div class="panel-footer{{ $footer_class ? ' '.$footer_class : '' }}">
                {{ $footer }}
            </div>
        @endif
    @endif
</div>
