@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">规则列表</h2>
                @can('admin.rule.store')
                    <div class="panel-actions">
                        <button data-toggle="modal" data-target="#add" class="btn btn-outline-primary">
                            <i class="icon wb-plus" aria-hidden="true"></i>添加规则
                        </button>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <select class="form-control" name="type" data-plugin="selectpicker" data-style="btn-outline btn-primary" title="类型" onchange="this.form.submit()">
                            <option value="1">正则表达式</option>
                            <option value="2">域名</option>
                            <option value="3">IP</option>
                            <option value="4">协议</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4">
                        <a href="{{route('admin.rule.index')}}" class="btn btn-danger">{{trans('common.reset')}}</a>
                    </div>
                </form>
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> 类型</th>
                        <th> 描述</th>
                        <th> 值</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rules as $rule)
                        <tr>
                            <td> {{$rule->id}} </td>
                            <td> {!! $rule->type_label !!} </td>
                            <td>
                                <input type="text" class="form-control" name="name" id="name_{{$rule->id}}" value="{{$rule->name}}"/>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="pattern" id="pattern_{{$rule->id}}" value="{{$rule->pattern}}"/>
                            </td>
                            <td>
                                @canany(['admin.rule.update', 'admin.rule.destroy'])
                                    <div class="btn-group">
                                        @can('admin.rule.update')
                                            <button class="btn btn-sm btn-outline-primary" onclick="editRule('{{$rule->id}}')">
                                                <i class="icon wb-edit"></i></button>
                                        @endcan
                                        @can('admin.rule.destroy')
                                            <button class="btn btn-sm btn-outline-danger" onclick="delRule('{{route('admin.rule.destroy',$rule)}}','{{$rule->name}}')">
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
                        共 <code>{{$rules->total()}}</code> 条审计规则
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$rules->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('admin.rule.store')
        <div class="modal fade" id="add" aria-hidden="true" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-simple modal-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">添加规则</h4>
                    </div>
                    <form action="#" method="post" class="modal-body">
                        <div class="alert alert-danger" style="display: none;" id="msg"></div>
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="add_type">类型</label>
                                    <div class="col-xl-4 col-sm-8">
                                        <select class="form-control" name="add_type" id="add_type" data-plugin="selectpicker" data-style="btn-outline btn-primary">
                                            <option value="1">正则表达式</option>
                                            <option value="2">域名</option>
                                            <option value="3">IP</option>
                                            <option value="4">协议</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="name">描述</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <input type="text" class="form-control" name="name" id="name" required/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row">
                                    <label class="col-md-2 col-sm-3 col-form-label" for="pattern">值</label>
                                    <div class="col-xl-6 col-sm-8">
                                        <input type="text" class="form-control" name="pattern" id="pattern" required/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button data-dismiss="modal" class="btn btn-danger mr-auto">关 闭</button>
                        <button type="button" class="btn btn-primary" onclick="addRule()">添 加</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        $(document).ready(function() {
            $('select').selectpicker('val', {{Request::query('type')}});
        });

        @can('admin.rule.store')
        // 添加规则
        function addRule() {
            $.ajax({
                method: 'POST',
                url: "{{route('admin.rule.store')}}",
                data: {
                    _token: '{{csrf_token()}}',
                    type: $('#add_type').val(),
                    name: $('#name').val(),
                    pattern: $('#pattern').val(),
                },
                dataType: 'json',
                success: function(ret) {
                    $('#add').modal('hide');
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                    } else {
                        swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
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
                        swal.fire({title: '提示', html: str, icon: 'error', confirmButtonText: '{{trans('common.confirm')}}'});
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
                url: '{{route('admin.rule.update','')}}/' + id,
                data: {
                    _token: '{{csrf_token()}}',
                    name: $('#name_' + id).val(),
                    pattern: $('#pattern_' + id).val(),
                },
                dataType: 'json',
                success: function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                    } else {
                        swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                },
                error: function(data) {
                    let str = '';
                    const errors = data.responseJSON;
                    if ($.isEmptyObject(errors) === false) {
                        $.each(errors.errors, function(index, value) {
                            str += '<li>' + value + '</li>';
                        });
                        swal.fire({title: '提示', html: str, icon: 'error', confirmButtonText: '{{trans('common.confirm')}}'});
                    }
                },
            });
        }
        @endcan

        @can('admin.rule.destroy')
        // 删除规则
        function delRule(url, name) {
            swal.fire({
                title: '{{trans('common.warning')}}',
                text: '确定删除规则 【' + name + '】 ？',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: '{{trans('common.close')}}',
                confirmButtonText: '{{trans('common.confirm')}}',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        data: {_token: '{{csrf_token()}}'},
                        dataType: 'json',
                        success: function(ret) {
                            if (ret.status === 'success') {
                                swal.fire({title: ret.message, icon: 'success', timer: 1000, showConfirmButton: false}).then(() => window.location.reload());
                            } else {
                                swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                            }
                        },
                    });
                }
            });
        }
        @endcan
    </script>
@endsection
