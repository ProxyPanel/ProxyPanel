@props(['code', 'title', 'value', 'row' => 10, 'help'])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{$title}}</label>
        <div class="col-md-8">
            <div class="input-group">
                <textarea class="form-control" rows={{$row}} id="{{$code}}">{{$value}}</textarea>
                <span class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="update('{{$code}}')">{{trans('common.update')}}</button>
                </span>
            </div>
            @isset($help)
                <span class="text-help"> {!! $help !!} </span>
            @endisset
        </div>
    </div>
</div>
