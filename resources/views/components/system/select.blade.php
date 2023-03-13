@props(['code', 'list', 'multiple' => 0])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{ trans('admin.system.'.$code) }}</label>
        <div class="col-md-9">
            <select id="{{$code}}" data-plugin="selectpicker" data-style="btn-outline btn-primary" onchange="updateFromOther('select','{{$code}}')" @if ($multiple) multiple @endif>
                @foreach ($list as $key => $value)
                    <option value="{{$value}}">{{$key}}</option>
                @endforeach
            </select>
            @if(trans('admin.system.hint.'.$code) !== 'admin.system.hint.'.$code)
                <span class="text-help"> {!! trans('admin.system.hint.'.$code) !!} </span>
            @endisset
        </div>
    </div>
</div>
