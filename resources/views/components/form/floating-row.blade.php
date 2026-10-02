@props([
    'name',
    'type' => 'text',
    'label',
    'value' => null,
    'required' => true,
    'autocomplete' => null,
])

{{-- 浮动标签表单行：div 的类名组合与 data-plugin 都是主题侧约定，BS5 下 form-group 已废弃 --}}
<div {{ $attributes->merge(['class' => 'form-group form-material floating']) }} data-plugin="formMaterial">
    <input class="form-control" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @if ($autocomplete !== null)autocomplete="{{ $autocomplete }}" @endif @if ($required)required @endif />
    <label class="floating-label" for="{{ $name }}">{{ $label }}</label>
</div>
