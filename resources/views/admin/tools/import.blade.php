@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">数据导入</h2>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="{{route('admin.tools.import')}}" method="POST" enctype="multipart/form-data" class="upload-form">
                    @csrf
                    <input type="file" id="inputUpload" name="uploadFile" data-plugin="dropify" data-default-file="" required/>
                    <button type="submit" class="btn btn-success float-right mt-10"> 导入</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
@endsection
