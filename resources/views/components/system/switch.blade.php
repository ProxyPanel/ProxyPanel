@props(['code', 'title', 'help', 'check'])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-md-3 col-form-label" for="{{$code}}">{{$title}}</label>
        <div class="col-md-9">
            <input type="checkbox" id="{{$code}}" data-plugin="switchery" @if($check) checked @endif onchange="updateFromOther('switch','{{$code}}')">
            @isset($help)
                <span class="text-help"> {!! $help !!} </span>
            @endisset
        </div>
    </div>
</div>
