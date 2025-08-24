@props(['theme' => config('theme.sidebar'), 'items'])

<div class="site-menubar {{ $theme }}">
    <div class="site-menubar-body">
        <ul class="site-menu" data-plugin="menu">
            @foreach ($items as $item)
                @if (!is_array($item))
                    <hr />
                    @continue
                @endif
                <x-ui.site.menu-item :icon="$item['icon'] ?? null" :route="$item['route'] ?? null" :text="$item['text'] ?? null" :active="$item['active'] ?? null" :badge="$item['badge'] ?? null" :can="$item['can'] ?? null"
                                     :show="$item['show'] ?? true" :children="$item['children'] ?? []" />
            @endforeach
        </ul>
    </div>
</div>
