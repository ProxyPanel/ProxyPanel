@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'attribute' => null,
    'help' => null,
    'prepend' => null,
    'append' => null,
    'prependIcon' => null,
    'appendIcon' => null,
    'button' => null,
    'buttonType' => 'button',
    'buttonClass' => 'btn-outline btn-primary',
    'buttonOnclick' => null,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-4 col-xl-6 col-sm-8',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    @if ($label)
        <label class="{{ $label_grid }} col-form-label" for="{{ $id ?? $name }}">{{ $label }}</label>
    @endif
    <div class="{{ $input_grid }}">
        <div class="input-group">
            @if ($prepend || $prependIcon)
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        @if ($prependIcon)
                            <i class="{{ $prependIcon }}" aria-hidden="true"></i>
                        @else
                            {!! $prepend !!}
                        @endif
                    </span>
                </div>
            @endif

            <input class="form-control" id="{{ $id ?? $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
                   placeholder="{{ $placeholder }}" @if ($required) required @endif {{ $attribute }} />

            @if ($append || $appendIcon || $button)
                <div class="input-group-append">
                    @if ($button)
                        <button class="btn {{ $buttonClass }}" type="{{ $buttonType }}"
                                @if ($buttonOnclick) onclick="{{ $buttonOnclick }}" @endif {{ $buttonAttributes ?? '' }}>
                            {!! $button !!}
                        </button>
                    @else
                        <span class="input-group-text">
                            @if ($appendIcon)
                                <i class="{{ $appendIcon }}" aria-hidden="true"></i>
                            @else
                                {!! $append !!}
                            @endif
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if ($help)
            <span class="text-help">{{ $help }}</span>
        @endif
    </div>
</div>
