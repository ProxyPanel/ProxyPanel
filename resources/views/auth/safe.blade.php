@extends('auth.layouts')
@section('title', sysConfig('website_name').' - '.trans('error.safe_enter'))
@section('content')
<form role="form" action="/login?securityCode=">
    <div class="form-group">
        <div class="form-group form-material floating" data-plugin="formMaterial">
            <input class="form-control" id="securityCode" name="securityCode"
                placeholder="" type="text">
            <label class="floating-label" for="securityCode">{{trans('error.safe_code')}}</label>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-lg btn-block mt-40 bg-indigo-500 text-white">{{trans('common.confirm')}}</button>
    </div>
</form>
@endsection
