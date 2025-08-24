@props(['color' => '', 'url', 'id' => null, 'icon', 'text', 'attribute' => null])
<a class="dropdown-item {{ $color }}" href="{{ $url }}" role="menuitem" {!! $attribute !!}>
    @if ($icon)
        <i class="icon {{ $icon }}" aria-hidden="true" @if ($id) id="{{ $id }}" @endif></i>
    @endif
    {{ $text }}
</a>
