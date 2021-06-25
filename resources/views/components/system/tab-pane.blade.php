@props(['active' => false, 'id', 'slot'])

<div class="tab-pane {{$active ? 'active' : ''}}" id="{{$id}}" role="tabpanel">
    <div class="form-row">
        {{$slot}}
    </div>
</div>
