@extends('auth.layouts')
@section('title', trans('auth.password.reset.attribute'))
@section('content')
    <form action="{{url(Request::getRequestUri())}}" method="post" class="register-form">
        @csrf
        @if(Session::has('successMsg'))
            <x-alert type="success" :message="Session::pull('successMsg')"/>
        @endif
        @if($errors->any())
            <x-alert type="danger" :message="$errors->all()"/>
        @else
            <div class="form-title">
                {{trans('auth.password.reset.attribute')}}
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" type="password" autocomplete="off" name="password" required/>
                <label class="floating-label" for="password">{{trans('auth.password.new')}}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input class="form-control" type="password" autocomplete="off" name="password_confirmation" required/>
                <label class="floating-label" for="password_confirmation">{{trans('validation.attributes.password_confirmation')}}</label>
            </div>
        @endif
        <a href="{{route('login')}}"
           class="btn btn-danger btn-lg {{$verify->status=== 0? 'float-left': 'btn-block'}}">{{trans('common.back')}}</a>
        @if ($verify->status === 0)
            <button type="submit" class="btn btn-primary btn-lg float-right">{{trans('common.submit')}}</button>
        @endif
    </form>
@endsection
