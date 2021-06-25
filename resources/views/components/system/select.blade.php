@props(['code', 'title', 'list', 'help', 'multiple' => 0])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{$title}}</label>
        <div class="col-md-9">
            <select id="{{$code}}" data-plugin="selectpicker" data-style="btn-outline btn-primary" onchange="updateFromOther('select','{{$code}}')" @if ($multiple) multiple @endif>
                @foreach ($list as $item => $value)
                    <option value="{{$value}}">{{$item}}</option>
                @endforeach
            </select>
            @isset($help)
                <span class="text-help"> {!! $help !!} </span>
            @endisset
        </div>
    </div>
</div>
