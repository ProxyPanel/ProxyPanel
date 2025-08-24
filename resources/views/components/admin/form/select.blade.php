@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [],
    'multiple' => false,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'useSelectpicker' => true,
    'attribute' => null,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-4 col-xl-5 col-md-6 col-sm-8',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    <label class="{{ $label_grid }} col-form-label" for="{{ $id ?? $name }}">{{ $label }}</label>
    <div class="{{ $input_grid }}">
        <select class="form-control show-tick" id="{{ $id ?? $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}"
                @if ($multiple) multiple @endif @if ($required) required @endif
                @if ($useSelectpicker) data-plugin="selectpicker" data-style="btn-outline btn-primary" @endif {{ $attribute }}>
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $value => $text)
                <option value="{{ $value }}">
                    {{ $text }}
                </option>
            @endforeach
            {{ $slot }}
        </select>
        @if ($help)
            <span class="text-help">{{ $help }}</span>
        @endif
    </div>
</div>
