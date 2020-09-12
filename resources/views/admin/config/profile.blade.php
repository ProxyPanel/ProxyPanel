@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600"><i class="icon wb-user"></i>{{trans('home.profile')}}</h1>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->first()"/>
            @endif
            <div class="panel-body">
                <form action="/admin/profile" method="post" enctype="multipart/form-data" class="form-bordered">
                    {{csrf_field()}}
                    <div class="form-group row">
                        <label for="old_password" class="col-md-2 col-form-label"> 旧密码 </label>
                        <input type="password" class="form-control col-md-5 round" name="old_password" id="old_password"
                                autofocus required/>
                    </div>
                    <div class="form-group row">
                        <label for="new_password" class="col-md-2 col-form-label"> 新密码 </label>
                        <input type="password" class="form-control col-md-5 round" name="new_password" id="new_password"
                                required/>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success"> 提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
