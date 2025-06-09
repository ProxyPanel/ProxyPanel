@props(['type', 'key', 'value', 'units', 'unit'])
<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{ "{$type}_$key" }}">{{ trans("admin.system.tasks.$type.$key") }}</label>
        <div class="col-md-6 input-group p-0">
            <input class="form-control" name="{{ "$type:$key:value" }}" type="number" value="{{ $value }}" min="1">
            @if (isset($units))
                <select class="form-control" name="{{ "$type:$key:unit" }}" data-plugin=" selectpicker" data-style="btn-outline btn-primary">
                    @foreach ($units as $u)
                        <option value="{{ $u }}" {{ $unit === $u ? 'selected' : '' }}>
                            {{ ucfirst(trans('validation.attributes.' . preg_replace('/s$/', '', $u))) }}
                        </option>
                    @endforeach
                </select>
            @elseif($unit)
                <div class="input-group-append">
                    <span class="input-group-text">{{ ucfirst(trans("validation.attributes.$unit")) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
