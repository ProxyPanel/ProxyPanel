@props([
    'name',
    'label' => null,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-4 col-xl-6 col-sm-7',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    <label class="{{ $label_grid }} col-form-label" for="{{ $name }}">{{ $label }}</label>
    <div class="{{ $input_grid }}">
        {{ $slot }}
    </div>
</div>
