@props(['code', 'title', 'value'])

<div class="form-group col-lg-6">
    <div class="row">
        <label class="col-form-label col-md-3" for="{{$code}}">{{$title}}</label>
        <div class="col-md-8">
            <input type="file" id="{{$code}}" data-plugin="dropify" data-default-file="{{asset($value ?? '/assets/images/default.png')}}"/>
            <button type="submit" class="btn btn-success float-right mt-10"> 提 交</button>
        </div>
    </div>
</div>
