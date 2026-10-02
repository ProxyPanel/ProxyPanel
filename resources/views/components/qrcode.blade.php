@props([
    'text',
    'id' => 'qrcode',
    'background' => null,
    'auto_color' => false,
])

@php
    // easy.qrcode 的选项：只带实际用到的键，避免拼出尾逗号
    $options = array_filter([
        'text' => $text,
        'backgroundImage' => $background,
        'autoColor' => $auto_color ?: null,
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'w-p100 h-p100']) }} id="{{ $id }}"></div>

@push('javascript')
    <script src="/assets/custom/easy.qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById({{ json_encode($id) }}), {{ json_encode($options, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)}});
    </script>
@endpush
