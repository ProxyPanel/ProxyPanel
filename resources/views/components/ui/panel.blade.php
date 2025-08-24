@props(['type' => null, 'icon' => null, 'title', 'subtitle' => null, 'actions' => null, 'alert' => null, 'footer' => null])

<div class="panel {{ $type ? "panel-$type" : '' }}">
    <div class="panel-heading">
        <h2 class="panel-title">
            @if ($icon)
                <i class="icon {{ $icon }}" aria-hidden="true"></i>
            @endif
            {{ $title }}
            @if ($subtitle)
                <small>{{ $subtitle }}</small>
            @endif
        </h2>

        @if ($actions)
            <div class="panel-actions">
                {{ $actions }}
            </div>
        @endif
    </div>

    @if ($alert)
        {!! $alert !!}
    @endif

    <div class="panel-body mt-lg-15">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="panel-footer">
            {{ $footer }}
        </div>
    @endif
</div>
