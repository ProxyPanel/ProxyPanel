@props(['type' => 'text', 'code', 'value', 'holder' => null, 'url' => null])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{ trans('admin.system.'.$code) }}</label>
        <div class="col-md-6">
            <div class="input-group">
                <input type="{{$type}}" class="form-control" id="{{$code}}" value="{{$value}}" placeholder="{{$holder}}"/>
                <span class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="update('{{$code}}')">{{trans('common.update')}}</button>
                </span>
            </div>
            @if(trans('admin.system.hint.'.$code) !== 'admin.system.hint.'.$code)
                <span class="text-help">
                     @if(isset($url))
                        {!! trans('admin.system.hint.'.$code, ['url' => $url]) !!}
                    @else
                        {!! trans('admin.system.hint.'.$code) !!}
                    @endif
                </span>
            @endif
        </div>
    </div>
</div>
