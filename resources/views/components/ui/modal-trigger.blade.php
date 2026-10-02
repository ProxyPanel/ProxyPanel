@props(['target'])

{{-- 模态框触发器：data-toggle/data-target 是 Bootstrap4 协议，BS5 改名 data-bs-*，集中在此一处 --}}
<button {{ $attributes }} data-toggle="modal" data-target="#{{ $target }}">
    {{ $slot }}
</button>
