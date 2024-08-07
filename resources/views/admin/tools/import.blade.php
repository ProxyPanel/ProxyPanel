@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.menu.tools.import') }}</h2>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')" />
            @endif
            @if ($errors->any())
                <x-alert type="danger" :message="$errors->all()" />
            @endif
            <div class="panel-body">
                <form class="upload-form" action="{{ route('admin.tools.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input id="inputUpload" name="uploadFile" data-plugin="dropify" data-default-file="" type="file" required />
                    <button class="btn btn-success float-right mt-10" type="submit"> {{ trans('common.import') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
@endsection
