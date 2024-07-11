@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($cert) ? trans('admin.action.edit_item', ['attribute' => trans('model.node_cert.attribute')]) : trans('admin.action.add_item', ['attribute' => trans('model.node_cert.attribute')]) }}
                </h2>
                <div class="panel-actions">
                    <a class="btn btn-danger" href="{{ route('admin.node.cert.index') }}">{{ trans('common.back') }}</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::pull('successMsg')" />
            @endif
            @if ($errors->any())
                <x-alert type="danger" :message="$errors->all()" />
            @endif
            <div class="panel-body">
                <form class="form-horizontal"
                      action="@isset($cert) {{ route('admin.node.cert.update', $cert) }} @else {{ route('admin.node.cert.store') }} @endisset"
                      method="POST" enctype="multipart/form-data">
                    @isset($cert)
                        @method('PUT')
                    @endisset
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="domain">{{ trans('model.node_cert.domain') }}</label>
                        <div class="col-md-9">
                            <input class="form-control" id="domain" name="domain" type="text" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="key">{{ trans('model.node_cert.key') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="key" name="key" type="text" rows="10" placeholder="{{ trans('admin.node.cert.key_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="pem">{{ trans('model.node_cert.pem') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="pem" name="pem" type="text" rows="10" placeholder="{{ trans('admin.node.cert.pem_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-success" type="submit">{{ trans('common.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('#domain').val(@json(old('domain')));
            $('#key').val(@json(old('key')));
            $('#pem').val(@json(old('pem')));
            @isset($cert)
                $('#domain').val(@json(old('domain') ?? $cert->domain));
                $('#key').val(@json(old('key') ?? $cert->key));
                $('#pem').val(@json(old('pem') ?? $cert->pem));
            @endisset
        });
    </script>
@endsection
