@props(['code', 'hcode', 'value', 'holder' => '', 'min' => 0, 'max' => false, 'hmin' => false, 'hmax' => false, 'hvalue', 'unit'])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{ $code }}">{{ trans("model.config.$code") }}</label>
        <div class="col-md-7">
            @isset($hcode)
                <div class="input-group">
                    <label for="{{ $code }}"></label>
                    <input class="form-control" id="{{ $code }}" type="number" value="{{ $value }}"
                           onchange="updateFromInput('{{ $code }}', {{ $min }},{{ $max }})" />
                    <div class="input-group-prepend">
                        <span class="input-group-text"> ~ </span>
                    </div>
                    <label for="{{ $hcode }}"></label>
                    <input class="form-control" id="{{ $hcode }}" type="number" value="{{ $hvalue }}"
                           onchange="updateFromInput('{{ $hcode }}',{{ $hmin }},{{ $hmax }})" />
                    @isset($unit)
                        <div class="input-group-prepend">
                            <span class="input-group-text"> {{ $unit }} </span>
                        </div>
                    @endisset
                </div>
            @else
                <div class="input-group">
                    <input class="form-control" id="{{ $code }}" type="number" value="{{ $value }}" />
                    <div class="input-group-append">
                        @isset($unit)
                            <span class="input-group-text">{{ $unit }}</span>
                        @endisset
                        <button class="btn btn-primary" type="button"
                                onclick="updateFromInput('{{ $code }}', {{ $min }}, {{ $max }})">{{ trans('common.update') }}</button>
                    </div>
                </div>
            @endisset
            @if (trans("admin.system.hint.$code") !== "admin.system.hint.$code")
                <span class="text-help"> {!! trans("admin.system.hint.$code") !!} </span>
            @endif
        </div>
    </div>
</div>
