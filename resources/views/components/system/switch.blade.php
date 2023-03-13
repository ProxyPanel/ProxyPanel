@props(['code', 'check', 'url' => null])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{ trans('admin.system.'.$code) }}</label>
        <div class="col-md-9">
            <input type="checkbox" id="{{$code}}" data-plugin="switchery" @if($check) checked @endif onchange="updateFromOther('switch','{{$code}}')">
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
