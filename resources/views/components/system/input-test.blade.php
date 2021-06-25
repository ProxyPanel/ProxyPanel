@props(['type' => 'text','code', 'title', 'value', 'holder' => '','help', 'test'])
<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{$title}}</label>
        <div class="col-md-6">
            <div class="input-group">
                <input type="{{$type}}" class="form-control" id="{{$code}}" value="{{$value}}" placeholder="{{$holder}}"/>
                <span class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="update('{{$code}}')">{{trans('common.update')}}</button>
                </span>
            </div>
            @isset($help)
                <span class="text-help"> {!! $help !!}@can('admin.test.notify')（<a href="javascript:sendTestNotification('{{$test}}');">发送测试消息</a>）@endcan</span>
            @endisset
        </div>
    </div>
</div>
