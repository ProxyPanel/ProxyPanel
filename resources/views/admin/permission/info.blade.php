@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ isset($permission)
                        ? trans('admin.action.edit_item', ['attribute' => trans('model.permission.attribute')])
                        : trans('admin.action.add_item', ['attribute' => trans('model.permission.attribute')]) }}
                </h2>
                <div class="panel-actions">
                    <a class="btn btn-danger" href="{{ route('admin.permission.index') }}">{{ trans('common.back') }}</a>
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
                      action="@isset($permission){{ route('admin.permission.update', $permission) }}@else{{ route('admin.permission.store') }}@endisset"
                      method="POST" enctype="multipart/form-data">
                    @isset($permission)
                        @method('PUT')
                    @endisset
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="description">{{ trans('model.permission.description') }}</label>
                        <div class="col-md-7 col-sm-8">
                            <input class="form-control" id="description" name="description" type="text" required />
                            <span class="text-help"> {{ trans('admin.permission.description_hint') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-sm-3 col-form-label" for="name">{{ trans('model.permission.name') }}</label>
                        <div class="col-md-7 col-sm-8">
                            <input class="form-control" id="name" name="name" type="text" required />
                            <span class="text-help"> {{ trans('admin.permission.name_hint') }} </span>
                        </div>
                    </div>

                    <div class="form-actions text-right">
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
            @isset($permission)
                $('#description').val(@json(old('description') ?? $permission->description));
                $('#name').val(@json(old('name') ?? $permission->name));
            @else
                $('#description').val(@json(old('description')));
                $('#name').val(@json(old('name')));
            @endisset
        });
    </script>
@endsection
