@props([
    'id' => 'tabs-' . rand(10000, 99999),
    'active' => null,
    'plugin' => 'tabs',
    'orientation' => 'horizontal', // horizontal, vertical
    'dropdown' => true,
    'dropdown_title' => trans('admin.setting.system.menu'),
])

<div class="nav-tabs-{{ $orientation }}" id="{{ $id }}" data-plugin="{{ $plugin }}">
    @if (isset($tabs))
        <ul class="nav nav-tabs" role="tablist">
            {{ $tabs }}

            @if ($dropdown)
                <li class="dropdown nav-item" role="presentation">
                    <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                        {{ $dropdown_title }}
                    </a>
                    <div class="dropdown-menu" role="menu">
                        @if (isset($dropdown_items))
                            {{ $dropdown_items }}
                        @else
                            {{ $tabs }}
                        @endif
                    </div>
                </li>
            @endif
        </ul>
    @endif

    <div class="tab-content {{ $attributes->get('content-class', 'py-35 px-35') }}">
        {{ $slot }}
    </div>
</div>
