@props(['color' => 'primary', 'icon' => 'wb-wrench', 'title' => ''])

<button class="btn btn-{{ $color }} dropdown-toggle" data-boundary="viewport" data-toggle="dropdown" type="button" aria-expanded="false">
    @if ($icon)
        <i class="icon {{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $title }}
</button>
<div class="dropdown-menu" role="menu">
    {{ $slot }}
</div>
