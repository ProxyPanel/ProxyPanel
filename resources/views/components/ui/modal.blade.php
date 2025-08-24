@props([
    'id',
    'title' => null,
    'size' => 'simple', // simple, lg, sm, etc.
    'position' => 'center', // center, sidebar, etc.
    'labelledby' => null,
    'backdrop' => true,
    'keyboard' => true,
    'form' => false,
    'focus' => true,
])

<div class="modal fade" id="{{ $id }}" role="dialog" aria-hidden="true" aria-labelledby="{{ $labelledby ?? $id }}" tabindex="-1"
     @if (!$backdrop) data-backdrop="static" @endif @if (!$keyboard) data-keyboard="false" @endif
     @if ($focus) data-focus-on="input:first" @endif>
    <div class="modal-dialog modal-{{ $size }} modal-{{ $position }}">
        <div class="modal-content">
            @if ($title || isset($header))
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                        <span aria-hidden="true">×</span>
                    </button>
                    @if (isset($header))
                        {{ $header }}
                    @elseif ($title)
                        <h4 class="modal-title">
                            {{ $title }}
                        </h4>
                    @endif
                </div>
            @endif

            @if ($form)
                {{ $slot }}
            @else
                <div class="modal-body">
                    {{ $slot }}
                </div>
            @endif

            @if (isset($footer))
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @elseif(isset($actions))
                <div class="modal-footer">
                    <button class="btn btn-default mr-auto" data-dismiss="modal">{{ trans('common.close') }}</button>
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>
