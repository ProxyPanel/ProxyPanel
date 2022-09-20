@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">@isset($cert) 编辑 @else 添加 @endisset域名证书</h2>
                <div class="panel-actions">
                    <a href="{{route('admin.node.cert.index')}}" class="btn btn-danger">返 回</a>
                </div>
            </div>
            @if (Session::has('successMsg'))
                <x-alert type="success" :message="Session::get('successMsg')"/>
            @endif
            @if($errors->any())
                <x-alert type="danger" :message="$errors->all()"/>
            @endif
            <div class="panel-body">
                <form action="@isset($cert) {{route('admin.node.cert.update', $cert)}} @else {{route('admin.node.cert.store')}} @endisset"
                      method="POST" enctype="multipart/form-data" class="form-horizontal">
                    @isset($cert)@method('PUT')@endisset
                    @csrf
                    <div class="form-group row">
                        <label for="domain" class="col-md-3 col-form-label">域名</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="domain" id="domain" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="key" class="col-md-3 col-form-label">Key</label>
                        <div class="col-md-9">
                            <textarea type="text" rows="10" class="form-control" name="key" id="key"
                                      placeholder="域名证书的KEY值，允许为空，VNET-V2Ray后端支持自动签证书"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="pem" class="col-md-3 col-form-label">Pem</label>
                        <div class="col-md-9">
                            <textarea type="text" rows="10" class="form-control" name="pem" id="pem"
                                      placeholder="域名证书的PEM值，允许为空，VNET-V2Ray后端支持自动签证书"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">提 交</button>
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
