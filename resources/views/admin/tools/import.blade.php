@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/dropify/dropify.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <x-ui.panel :title="trans('admin.menu.tools.import')">
            <x-slot:alert>
                @if (Session::has('successMsg'))
                    <x-alert :message="Session::pull('successMsg')" />
                @endif
                @if ($errors->any())
                    <x-alert type="danger" :message="$errors->all()" />
                @endif
            </x-slot:alert>

            <form class="upload-form" action="{{ route('admin.tools.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input id="inputUpload" name="uploadFile" data-plugin="dropify" data-default-file="" type="file" required />
                <button class="btn btn-success float-right mt-10" type="submit"> {{ trans('common.import') }}</button>
            </form>
        </x-ui.panel>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/dropify/dropify.min.js"></script>
@endsection
