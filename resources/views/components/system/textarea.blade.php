@props(['code', 'value', 'row' => 10])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{ trans('admin.system.'.$code) }}</label>
        <div class="col-md-8">
            <div class="input-group">
                <textarea class="form-control" rows={{$row}} id="{{$code}}">{{$value}}</textarea>
                <span class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="update('{{$code}}')">{{trans('common.update')}}</button>
                </span>
            </div>
            @if(trans('admin.system.hint.'.$code) !== 'admin.system.hint.'.$code)
                <span class="text-help"> {!! trans('admin.system.hint.'.$code) !!} </span>
            @endisset
        </div>
    </div>
</div>
