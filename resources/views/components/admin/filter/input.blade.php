@props(['class' => '', 'type' => 'text', 'name', 'value' => null, 'placeholder' => null])

<div class="form-group {{ $class }}">
    <input class="form-control" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
           autocomplete="off" />
</div>
