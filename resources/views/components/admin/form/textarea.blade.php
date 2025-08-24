@props([
    'name',
    'id' => null,
    'label' => null,
    'rows' => 5,
    'help' => null,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-6 col-sm-8',
    'container' => 'form-group row',
    'placeholder' => null,
])

<div class="{{ $container }}">
    @if ($label)
        <label class="{{ $label_grid }} col-form-label" for="{{ $id ?? $name }}">{{ $label }}</label>
    @endif
    <div class="{{ $input_grid }}">
        <textarea class="form-control" id="{{ $id ?? $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{!! $placeholder !!}"></textarea>
        @if ($help)
            <span class="text-help">{{ $help }}</span>
        @endif
    </div>
</div>
