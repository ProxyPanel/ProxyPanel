@props([
    'style' => 'horizontal',
    'route' => null,
    'method' => 'POST',
    'model' => null,
    'handler' => null,
    'enctype' => false,
])

@php
    $formAttributes = ['class' => 'form-' . e($style)];
    if ($route) {
        $formAttributes['action'] = e($route);
    }
    $formAttributes['method'] = $method === 'GET' ? 'GET' : 'POST';
    if ($handler) {
        $formAttributes['onsubmit'] = 'return ' . e($handler);
    }
    if ($enctype) {
        $formAttributes['enctype'] = 'multipart/form-data';
    }
@endphp

<form {!! implode(
    ' ',
    array_map(
        function ($key, $value) {
            return $key . '="' . $value . '"';
        },
        array_keys($formAttributes),
        $formAttributes,
    ),
) !!}>
    @if (!in_array($method, ['GET', 'POST']))
        @method($method)
    @endif
    @csrf
    {!! $slot !!}
</form>
