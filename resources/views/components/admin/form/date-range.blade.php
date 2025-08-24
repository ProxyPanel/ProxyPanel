@props([
    'start_name' => 'start',
    'end_name' => 'end',
    'start_placeholder' => '',
    'end_placeholder' => '',
    'label' => null,
    'required' => false,
    'label_grid' => 'col-xxl-2 col-lg-3 col-sm-4',
    'input_grid' => 'col-xxl-4 col-xl-6 col-sm-8',
    'container' => 'form-group row',
])

<div class="{{ $container }}">
    @if ($label)
        <label class="{{ $label_grid }} col-form-label">{{ $label }}</label>
    @endif
    <div class="{{ $input_grid }}">
        <div class="input-daterange">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="icon wb-calendar" aria-hidden="true"></i>
                    </span>
                </div>
                <input class="form-control" name="{{ $start_name }}" data-plugin="datepicker" type="text" placeholder="{{ $start_placeholder }}"
                       @if ($required) required @endif />
            </div>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">{{ trans('common.to') }}</span>
                </div>
                <input class="form-control" name="{{ $end_name }}" data-plugin="datepicker" type="text" placeholder="{{ $end_placeholder }}"
                       @if ($required) required @endif />
            </div>
        </div>
    </div>
</div>
