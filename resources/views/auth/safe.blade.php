@extends('auth.layouts')
@section('title', sysConfig('website_name') . ' - ' . trans('errors.safe_enter'))
@section('content')
    <form role="form" action="/login?securityCode=">
        <div class="form-group">
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" id="securityCode" name="securityCode" type="text" placeholder="">
                <label class="floating-label" for="securityCode">{{ trans('errors.safe_code') }}</label>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-lg btn-block mt-40 bg-indigo-500 text-white" type="submit">{{ trans('common.confirm') }}</button>
        </div>
    </form>
@endsection
