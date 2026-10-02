@props(['badge' => null, 'type' => null, 'size' => null, 'text' => null])

@php
    // 模型只给语义值（type/size/text），徽标的类名映射集中在这里：换主题时只改本文件
    $type = $badge['type'] ?? $type;
    $size = $badge['size'] ?? $size;
    $text = $badge['text'] ?? $text;
@endphp

@if ($type === null)
    {{ $text }}
@else
    <span class="badge{{ $size ? ' badge-'.$size : '' }} badge-{{ $type }}">{{ $text }}</span>
@endif
