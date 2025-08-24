@props(['icon', 'route', 'text', 'active' => null, 'badge' => null, 'can' => null, 'children' => [], 'show' => true])

@if ($show && (empty($can) || auth()->user()->canAny($can)))
    <li class="site-menu-item @if ($children) has-sub @endif {{ request()->routeIs($active ?? $route) ? 'active open' : '' }}">
        <a href="{{ $route ? route($route) : 'javascript:void(0)' }}">
            <i class="site-menu-icon {{ $icon }}" aria-hidden="true"></i>
            <span class="site-menu-title">{{ $text }}</span>
            @if ($badge)
                <div class="site-menu-badge">
                    <span class="badge badge-pill badge-success">{{ $badge }}</span>
                </div>
            @endif
        </a>
        @if ($children)
            <ul class="site-menu-sub">
                @foreach ($children as $child)
                    <x-ui.site.menu-item :icon="$child['icon'] ?? null" :route="$child['route'] ?? null" :text="$child['text'] ?? null" :active="$child['active'] ?? null" :badge="$child['badge'] ?? null" :can="$child['can'] ?? null"
                                         :show="$child['show'] ?? true" :children="$child['children'] ?? []" />
                @endforeach
            </ul>
        @endif
    </li>
@endif
