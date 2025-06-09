@props(['active' => false, 'id', 'slot', 'feature' => null])

<div class="tab-pane {{ $active ? 'active' : '' }}" id="{{ $id }}" role="tabpanel"
     @if ($feature) data-feature="{{ $feature }}" @endif>
    <div class="form-row">
        {{ $slot }}
    </div>
</div>
