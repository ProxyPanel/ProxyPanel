@props(['skip_current' => false, 'plain' => false])

{{-- 语言切换项：后台/用户区/认证页/订阅页共用，换主题时只需改这一处 --}}
@foreach (config('common.language') as $key => $value)
    @if (! $skip_current || $key !== app()->getLocale())
        <a class="dropdown-item" href="{{ route('lang', ['locale' => $key]) }}" @if (! $plain)role="menuitem"@endif>
            @if ($plain)
                <i class="fi fi-{{ $value[1] }} mr-2" aria-hidden="true"></i>
                {{ $value[0] }}
            @else
                <i class="fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                <span style="padding: inherit;">{{ $value[0] }}</span>
            @endif
        </a>
    @endif
@endforeach
