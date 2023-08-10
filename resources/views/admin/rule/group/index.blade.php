@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.rule.group.title') }}</h2>
                @can('admin.rule.group.create')
                    <div class="panel-actions">
                        <a href="{{route('admin.rule.group.create')}}" class="btn btn-outline-primary">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                        </a>
                    </div>
                @endcan
            </div>
            <div class="panel-body">
                <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
                    <thead class="thead-default">
                    <tr>
                        <th> #</th>
                        <th> {{ trans('model.rule_group.name') }}</th>
                        <th> {{ trans('model.rule_group.type') }}</th>
                        <th> {{trans('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($ruleGroups as $ruleGroup)
                        <tr>
                            <td> {{$ruleGroup->id}} </td>
                            <td> {{$ruleGroup->name}} </td>
                            <td> {!! $ruleGroup->type_label !!} </td>
                            <td>
                                @canany(['admin.rule.group.edit', 'admin.rule.group.destroy'])
                                    <div class="btn-group">
                                        @can('admin.rule.group.edit')
                                            <a href="{{route('admin.rule.group.edit', $ruleGroup)}}" class="btn btn-sm btn-outline-primary">
                                                <i class="icon wb-edit"></i> {{ trans('common.edit') }}
                                            </a>
                                        @endcan
                                        @can('admin.rule.group.destroy')
                                            <button onclick="delRuleGroup('{{route('admin.rule.group.destroy', $ruleGroup)}}', '{{$ruleGroup->name}}')"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="icon wb-trash"></i> {{ trans('common.delete') }}
                                            </button>
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
                        {!! trans('admin.rule.group.counts', ['num' => $ruleGroups->total()]) !!}
                    </div>
                    <div class="col-sm-8">
                        <nav class="Page navigation float-right">
                            {{$ruleGroups->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    @can('admin.rule.group.destroy')
        <script>
          // 删除规则分组
          function delRuleGroup(url, name) {
            swal.fire({
              title: '{{trans('common.warning')}}',
              text: '{{ trans('admin.confirm.delete.0', ['attribute' => trans('model.rule_group.attribute')]) }}' +
                  name + '{{ trans('admin.confirm.delete.1') }}',
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
                      swal.fire({
                        title: ret.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false,
                      }).then(() => window.location.reload());
                    } else {
                      swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                  },
                });
              }
            });
          }
        </script>
    @endcan
@endsection

