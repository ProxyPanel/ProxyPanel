@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.rule.title') }}</h2>
                @can('admin.rule.store')
                    <div class="panel-actions">
                        <button class="btn btn-outline-primary" data-toggle="modal" data-target="#add">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" name="type" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('model.rule.attribute') }}">
                            <option value="1">{{ trans('admin.rule.type.reg') }}</option>
                            <option value="2">{{ trans('admin.rule.type.domain') }}</option>
                            <option value="3">{{ trans('admin.rule.type.ip') }}</option>
                            <option value="4">{{ trans('admin.rule.type.protocol') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <a class="btn btn-danger" href="{{ route('admin.rule.index') }}">{{ trans('common.reset') }}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                        <tr>
                            <th> #</th>
                            <th> {{ trans('model.rule.attribute') }}</th>
                            <th> {{ trans('model.rule.name') }}</th>
                            <th> {{ trans('model.rule.pattern') }}</th>
                            <th> {{ trans('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rules as $rule)
                            <tr>
                                <td> {{ $rule->id }} </td>
                                <td> {!! $rule->type_label !!} </td>
                                <td>
                                    <input class="form-control" id="name_{{ $rule->id }}" name="name" type="text" value="{{ $rule->name }}" />
                                </td>
                                <td>
                                    <input class="form-control" id="pattern_{{ $rule->id }}" name="pattern" type="text" value="{{ $rule->pattern }}" />
                                </td>
                                <td>
                                    @canany(['admin.rule.update', 'admin.rule.destroy'])
                                        <div class="btn-group">
                                            @can('admin.rule.update')
                                                <button class="btn btn-sm btn-outline-primary" onclick="editRule('{{ $rule->id }}')">
                                                    <i class="icon wb-edit"></i></button>
                                            @endcan
                                            @can('admin.rule.destroy')
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="delRule('{{ route('admin.rule.destroy', $rule) }}','{{ $rule->name }}')">
                                                    <i class="icon wb-trash"></i></button>
                                            @endcan
                                        </div>
                                    @endcanany
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        {!! trans('admin.rule.counts', ['num' => $rules->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{ $rules->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.rule.store')
        <div class="modal fade" id="add" role="dialog" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog modal-simple modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" type="button" aria-label="{{ trans('common.close') }}">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ trans('admin.action.add_item', ['attribute' => trans('model.rule.attribute')]) }}</h4>
                    </div>
                    <form class="modal-body" action="#" method="post">
                        <div class="alert alert-danger" id="msg" style="display: none;"></div>
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="add_type">{{ trans('model.rule.attribute') }}</label>
                                    <div class="col-xl-4 col-sm-8">
                                        <select class="form-control" id="add_type" name="add_type" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                            <option value="1">{{ trans('admin.rule.type.reg') }}</option>
                                            <option value="2">{{ trans('admin.rule.type.domain') }}</option>
                                            <option value="3">{{ trans('admin.rule.type.ip') }}</option>
                                            <option value="4">{{ trans('admin.rule.type.protocol') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="name">{{ trans('model.rule.name') }}</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <input class="form-control" id="name" name="name" type="text" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="pattern">{{ trans('model.rule.pattern') }}</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <input class="form-control" id="pattern" name="pattern" type="text" required />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button class="btn btn-danger mr-auto" data-dismiss="modal">{{ trans('common.close') }}</button>
                        <button class="btn btn-primary" type="button" onclick="addRule()">{{ trans('common.add') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@push('javascript')
    <script>
        $(document).ready(function() {
            $('select').selectpicker('val', {{ Request::query('type') }});
        });

        @can('admin.rule.store')
            // 添加规则
            function addRule() {
                $.ajax({
                    method: 'POST',
                    url: "{{ route('admin.rule.store') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: $('#add_type').val(),
                        name: $('#name').val(),
                        pattern: $('#pattern').val(),
                    },
                    dataType: 'json',
                    success: function(ret) {
                        $('#add').modal('hide');
                        if (ret.status === 'success') {
                            swal.fire({
                                title: ret.message,
                                icon: 'success',
                                timer: 1000,
                                showConfirmButton: false,
                            }).then(() => window.location.reload());
                        } else {
                            swal.fire({
                                title: ret.message,
                                icon: 'error'
                            }).then(() => window.location.reload());
                        }
                    },
                    error: function(data) {
                        $('#add').modal('hide');
                        let str = '';
                        const errors = data.responseJSON;
                        if ($.isEmptyObject(errors) === false) {
                            $.each(errors.errors, function(index, value) {
                                str += '<li>' + value + '</li>';
                            });
                            swal.fire({
                                title: '{{ trans('admin.hint') }}',
                                html: str,
                                icon: 'error',
                                confirmButtonText: '{{ trans('common.confirm') }}',
                            });
                        }
                    },
                });
            }
        @endcan

        @can('admin.rule.update')
            // 编辑规则
            function editRule(id) {
                $.ajax({
                    method: 'PUT',
                    url: '{{ route('admin.rule.update', '') }}/' + id,
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: $('#name_' + id).val(),
                        pattern: $('#pattern_' + id).val(),
                    },
                    dataType: 'json',
                    success: function(ret) {
                        if (ret.status === 'success') {
                            swal.fire({
                                title: ret.message,
                                icon: 'success',
                                timer: 1000,
                                showConfirmButton: false,
                            }).then(() => window.location.reload());
                        } else {
                            swal.fire({
                                title: ret.message,
                                icon: 'error'
                            }).then(() => window.location.reload());
                        }
                    },
                    error: function(data) {
                        let str = '';
                        const errors = data.responseJSON;
                        if ($.isEmptyObject(errors) === false) {
                            $.each(errors.errors, function(index, value) {
                                str += '<li>' + value + '</li>';
                            });
                            swal.fire({
                                title: '{{ trans('admin.hint') }}',
                                html: str,
                                icon: 'error',
                                confirmButtonText: '{{ trans('common.confirm') }}',
                            });
                        }
                    },
                });
            }
        @endcan

        @can('admin.rule.destroy')
            // 删除规则
            function delRule(url, name) {
                swal.fire({
                    title: '{{ trans('common.warning') }}',
                    text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.rule.attribute')]) }}' + name +
                        '{{ trans('admin.confirm.delete.1') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: '{{ trans('common.close') }}',
                    confirmButtonText: '{{ trans('common.confirm') }}',
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            method: 'DELETE',
                            url: url,
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(ret) {
                                if (ret.status === 'success') {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'success',
                                        timer: 1000,
                                        showConfirmButton: false,
                                    }).then(() => window.location.reload());
                                } else {
                                    swal.fire({
                                        title: ret.message,
                                        icon: 'error'
                                    }).then(() => window.location.reload());
                                }
                            },
                        });
                    }
                });
            }
        @endcan
    </script>
@endpush
