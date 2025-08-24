@props([
    'class' => 'col-xxl-3 col-lg-5 col-sm-12',
    'start_name' => 'start',
    'end_name' => 'end',
    'start_placeholder' => trans('admin.filter.start_time'),
    'end_placeholder' => trans('admin.filter.end_time'),
])

<div class="form-group {{ $class }}">
    <div class="input-group input-daterange">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
        </div>
        <input class="form-control" name="{{ $start_name }}" data-plugin="datepicker" type="text" placeholder="{{ $start_placeholder }}" autocomplete="off" />
        <div class="input-group-prepend">
            <span class="input-group-text">{{ trans('common.to') }}</span>
        </div>
        <input class="form-control" name="{{ $end_name }}" data-plugin="datepicker" type="text" placeholder="{{ $end_placeholder }}"
               autocomplete="off" />
    </div>
</div>
