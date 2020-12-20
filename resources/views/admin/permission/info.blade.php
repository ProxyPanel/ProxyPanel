@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">@isset($permission)编辑@else添加@endisset权限行为</h2>
                <div class="panel-actions">
                    <a href="{{route('admin.permission.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="@isset($permission){{route('admin.permission.update',$permission)}}@else{{route('admin.permission.store')}}@endisset"
                      method="POST" enctype="multipart/form-data" class="form-horizontal">
                    @isset($permission)@method('PUT')@endisset
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="description">名称</label>
                        <div class="col-md-7 col-sm-8">
                            <input type="text" class="form-control" name="description" id="description"/>
                            <span class="text-help"> 填写名称，例：【A系统】编辑A </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="name">行为</label>
                        <div class="col-md-7 col-sm-8">
                            <input type="text" class="form-control" name="name" id="name"/>
                            <span class="text-help"> 填写路由名称，例：admin.permission.create,update </span>
                        </div>
                    </div>

                    <div class="form-actions text-right">
                        <button type="submit" class="btn btn-success">提 交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        @isset($permission)
        $(document).ready(function() {
          $('#description').val('{{$permission->description}}');
          $('#name').val('{{$permission->name}}');
        });
        @endisset
    </script>
@endsection