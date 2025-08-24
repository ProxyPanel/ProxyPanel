@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'attribute' => 'autocomplete=off',
    'help' => null,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-4 col-xl-7 col-sm-8',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    <label class="{{ $label_grid }} col-form-label" for="{{ $id ?? $name }}">{{ $label }}</label>
    <div class="{{ $input_grid }}">
        <input class="form-control" id="{{ $id ?? $name }}" name="{{ $name }}" type="{{ $type }}" placeholder="{{ $placeholder }}"
               @if ($required) required @endif {{ $attribute }} />
        @if ($help)
            <span class="text-help">{!! $help !!}</span>
        @endif
    </div>
</div>
