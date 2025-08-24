@props(['class' => '', 'name', 'style' => 'btn-outline btn-primary', 'title' => '', 'options' => [], 'multiple' => false])

<div class="form-group {{ $class }}">
    <select class="form-control show-tick" id="{{ $name }}" name="{{ $multiple ? $name . '[]' : $name }}" data-plugin="selectpicker"
            data-style="{{ $style }}" title="{{ $title }}" @if ($multiple) multiple @endif>
        @forelse($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @empty
            {{ $slot }}
        @endforelse
    </select>
</div>
