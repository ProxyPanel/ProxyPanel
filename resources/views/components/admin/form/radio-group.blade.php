@props([
    'name',
    'label' => null,
    'options' => [],
    'attribute' => null,
    'help' => [],
    'inline' => true,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-auto',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    <label class="{{ $label_grid }} col-form-label">{{ $label }}</label>
    <div class="{{ $input_grid }}">
        <ul class="list-unstyled{{ $inline ? ' list-inline' : '' }}">
            @foreach ($options as $value => $text)
                <li class="{{ $inline ? 'list-inline-item' : '' }}">
                    <div class="radio-custom radio-primary">
                        <input id="{{ $name }}_{{ $value }}" name="{{ $name }}" type="radio" value="{{ $value }}"
                               {{ $attribute }} />
                        <label for="{{ $name }}_{{ $value }}">{{ $text }}</label>
                    </div>
                </li>
            @endforeach
        </ul>
        @if ($help)
            <span class="text-help">{{ $help }}</span>
        @endif
    </div>
</div>
