@props(['code', 'list', 'multiple' => false])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{ $code }}">{{ trans("model.config.$code") }}</label>
        <div class="col-md-9">
            <select id="{{ $code }}" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                    onchange="updateFromOther('{{ $multiple ? 'multiSelect' : 'select' }}','{{ $code }}')"
                    @if ($multiple) multiple @endif>
                @foreach ($list as $key => $value)
                    <option value="{{ $value }}">{{ $key }}</option>
                @endforeach
            </select>
            @if (trans("admin.system.hint.$code") !== "admin.system.hint.$code")
                <span class="text-help"> {!! trans("admin.system.hint.$code") !!} </span>
            @endif
        </div>
    </div>
</div>
