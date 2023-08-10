@props(['type' => 'text', 'code', 'value', 'holder' => null, 'test'])
<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{ trans('admin.system.'.$code) }}</label>
        <div class="col-md-6">
            <div class="input-group">
                <input type="{{$type}}" class="form-control" id="{{$code}}" value="{{$value}}" placeholder="{!! $holder !!}"/>
                <span class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="update('{{$code}}')">{{trans('common.update')}}</button>
                </span>
            </div>
            @if(trans('admin.system.hint.'.$code) !== 'admin.system.hint.'.$code)
                <span class="text-help"> {!! trans('admin.system.hint.'.$code) !!} @can('admin.test.notify')
                        <a href="javascript:sendTestNotification('{{$test}}');">[{{ trans('admin.system.notification.send_test') }}]</a>
                    @endcan</span>
            @endisset
        </div>
    </div>
</div>
